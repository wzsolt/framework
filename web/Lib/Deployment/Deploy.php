<?php

namespace Framework\Deployment;

use DirectoryIterator;
use Framework\Components\HostConfig;
use Framework\Helpers\Str;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Deploy
{
    const GIT_PULL          = 'pull';
    const GIT_ADD           = 'add';
    const GIT_REMOVE        = 'rm';
    const GIT_COMMIT        = 'commit -a -m';
    const GIT_PUSH          = 'push origin';
    const GIT_STATUS        = 'status';

    const ACTION_DEPLOY     = 'deploy';
    const ACTION_UPDATE_DB  = 'db';
    const ACTION_COMPOSER   = 'composer';
    const ACTION_PATCH      = 'patch';

    const COMMIT_MESSAGE    = '"* SQL and/or script file(s) are moved to the processed folder."';

    private string $userName;

    private string $repositoryPath;

    private array $options = [];

    private string $payload;

    private mixed $gitResponse;

    private array $log = [];

    private string $logFileName;

    private bool $needCommit = false;

    private int $startTime = 0;

    private HostConfig $hostConfig;

    public function __construct(string $userName, string $repositoryPath, string $payload = '')
    {
        $this->hostConfig = HostConfig::create()->load($_SERVER['HTTP_HOST'] ?: '');

        $this->userName = $userName;

        $this->repositoryPath = rtrim($repositoryPath, '/') . '/';

        $this->payload = $payload;

        $this->gitResponse = json_decode(urldecode($this->payload), true);

        if($this->gitResponse) {
            preg_match("/\[(.*?)\]/is", $this->gitResponse['head_commit']['message'], $matches);

            if (!Empty($matches[1])) {
                $this->options = explode(',', $matches[1]);
            }
        }

        $this->logFileName = date('Ymd') . '.txt';
    }

    public function start():self
    {
        $this->startTime = microtime(true);

        $this->addLog('Deployment process started at ' . date('Y-m-d H:i:s') . ' with the following options: <b>' . ($this->options ? implode(', ', $this->options) : 'NONE' ) . '</b>');

        return $this;
    }

    public function finish():self
    {
        if($this->needCommit) {
            $this->commit(self::COMMIT_MESSAGE);
        }

        $finishTime = microtime(true);

        $this->addLog('Deployment process finished at ' . date('Y-m-d H:i:s'));
        $this->addLog('Total time: ' . ($finishTime - $this->startTime) . ' sec');

        $this->saveLog('', "\n\n-------------------------------------------------------------------------------\n\n");

        return $this;
    }

    public function verifySignature($secret):bool
    {
        if(GIT_SKIP_VERIFICATION) return true;

        $headers = $this->getAllHeaders();

        if($headers) {
            return hash_equals('sha256=' . hash_hmac('sha256', $this->payload, $secret), ($headers['X-Hub-Signature-256'] ?? ''));
        }else{
            return false;
        }
    }

    public function isOnBranch(string $branch):bool
    {
        if(Empty($this->gitResponse['ref'])) return false;

        return ($this->gitResponse['ref'] == $branch);
    }

    public function isDeploymentRequest():bool
    {
        return !Empty($this->options);
    }

    public function setOption(string $option):self
    {
        if(!Empty($option)) {
            $this->options[] = $option;
        }

        return $this;
    }

    public function updateRepository():self
    {
        if(in_array(self::ACTION_DEPLOY, $this->options)){
            $this->addLog('Updating the repository');

            $this->gitCommand(self::GIT_PULL);

            $this->clearCache('twig');
        }

        $this->updateDependencies();

        return $this;
    }

    public function clearCache(string $path):self
    {
        $path = rtrim($path, '/') . '/';

        if(file_exists($this->repositoryPath . DIR_CACHE . $path)) {
            exec("rm -rf " . $this->repositoryPath . DIR_CACHE . $path, $output);

            $this->addLog('Twig cache cleared.', $output, true);
        }

        return $this;
    }

    public function updateDatabase():self
    {

        if(in_array(self::ACTION_UPDATE_DB, $this->options) && is_dir($this->repositoryPath . DIR_SQL)){
            $fileList = $this->getFileList($this->repositoryPath . DIR_SQL, 'sql');

            $this->addLog('Updating database (<b>' . count($fileList) . ' file(s) found</b> found):');

            if(!Empty($fileList)){
                $i = 1;

                foreach($fileList AS $file){
                    $this->addLog(' ' . $i . '. file: ' . $file);

                    if($this->processSQlFile($this->repositoryPath . DIR_SQL . $file)) {
                        copy($this->repositoryPath . DIR_SQL . $file, $this->repositoryPath . DIR_SQL_PROCESSED . $file);

                        $this->gitCommand(self::GIT_REMOVE, DIR_SQL . $file);

                        $this->gitCommand(self::GIT_ADD, DIR_SQL_PROCESSED . $file);

                        $this->needCommit = true;
                    }

                    $i++;
                }
            }
        }

        return $this;
    }

    public function runScripts():self
    {
        if(in_array(self::ACTION_PATCH, $this->options) && file_exists($this->repositoryPath . DIR_SCRIPTS)){
            $fileList = $this->getFileList($this->repositoryPath . DIR_SCRIPTS, 'php');

            $this->addLog('Running script(s) (<b>' . count($fileList) . ' file(s) found</b> found):');

            if(!Empty($fileList)){
                $i = 1;

                foreach($fileList AS $file){
                    $this->addLog(' ' . $i . '. file: ' . $file);

                    if($this->processScriptFile($file)) {
                        copy($this->repositoryPath . DIR_SCRIPTS . $file, $this->repositoryPath . DIR_SCRIPTS_PROCESSED . $file);

                        $this->gitCommand(self::GIT_REMOVE, DIR_SCRIPTS . $file);

                        $this->gitCommand(self::GIT_ADD, DIR_SCRIPTS_PROCESSED . $file);

                        $this->needCommit = true;
                    }

                    $i++;
                }
            }
        }

        return $this;
    }

    private function updateDependencies():void
    {
        if(
            in_array('composer.json', $this->gitResponse['head_commit']['modified']) ||
            in_array('composer.json', $this->gitResponse['head_commit']['added']) ||
            in_array(self::ACTION_COMPOSER, $this->options)
        ){
            exec("cd " . $this->repositoryPath . " && " . PHP_BIN . " " . COMPOSER_PATH . " install 2>&1", $output);

            $this->addLog('Dependency update result:', $output, true);
        }
    }

    public function sendReport(string $email):void
    {
        $smtpConfig = $this->hostConfig->getSmtpConfig();

        if(!Empty($smtpConfig) && REPORT_SENDER && !Empty($this->log)) {
            $mail = new PHPMailer(true);

            try {
                $mail->IsSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = true;
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = strtolower($smtpConfig['ssl']);
                $mail->Port = $smtpConfig['port'];
                $mail->Host = $smtpConfig['host'];
                $mail->Username = $smtpConfig['user'];
                $mail->Password = $smtpConfig['password'];
                $mail->CharSet = 'utf-8';
                $mail->isHTML();

                $mail->setFrom(REPORT_SENDER);
                $mail->addAddress($email);

                $mail->Subject = 'Deployment report - ' . date('Y-m-d H:i:s');
                $mail->msgHTML(implode('', $this->log));

                $mail->send();
            } catch (Exception $e) {
                $this->saveLog('Email report error:', $mail->ErrorInfo);
            }
        }
    }

    private function gitCommand(string $command, string $params = ''):array
    {
        $output = [];

        if(file_exists($this->repositoryPath) && $this->userName) {
            exec("cd " . $this->repositoryPath . " && sudo -u " . $this->userName . " " . DIR_GIT_PATH . " " . $command . ($params ? ' ' . $params : '') . " 2>&1", $output);

            $this->addLog('Git "' . $command . '" command result:', $output, true);
        }

        return $output;
    }

    private function commit(string $message):void
    {
        $this->gitCommand(self::GIT_COMMIT, $message);

        $this->gitCommand(self::GIT_PUSH);
    }

    private function processSQlFile(string $file):bool
    {
        exec("mysql -u " . DB_USER . " -p" . DB_PASSWORD . " -f " . DB_NAME . " < " . $file . " 2>&1", $output);

        $this->addLog('Sql dump import result:', $output);

        return true;
    }

    private function processScriptFile(string $fileName):bool
    {
        $result = false;

        $className = '\\Runners\\' . Str::dashesToCamelCase(strstr($fileName, '.php', true));

        if(class_exists($className)){
            $runner = new $className();
            if($runner instanceof Runner){
                $result = $runner->run();
            }
        }

        return $result;
    }

    public function addLog(string $title, $data = false, $isCode = false):void
    {
        $logLine = '';

        $this->saveLog($title, $data);

        if($title){
            $logLine .= $title . "<br>\n";
        }

        if(!Empty($data)) {
            if (is_array($data)) {
                $data = implode("<br>\n", $data);
            }

            if ($isCode) {
                $logLine = '<code>' . $data . '<code>';
            } else {
                $logLine .= $data;
            }

            $logLine .= "<br>\n";
        }

        $this->log[] = $logLine;
    }

    public function saveLog($title, $data = false, $fileName = false, $append = true):void
    {
        if(!$fileName) $fileName = $this->logFileName;

        $folderName = rtrim(DIR_LOG, '/') . '/deployment/';

        if(!is_dir($folderName)){
            @mkdir($folderName, 0777, true);
            @chmod($folderName, 0777);
        }

        $out = '[' . date('Y-m-d H:i:s') . '] ' . (!Empty($title) ? strip_tags($title) : '')  . "\n";

        if(!Empty($data)) {
            if (is_array($data)) {
                $out .= print_r($data, true);
            } else {
                $out .= strip_tags($data);
            }
        }

        @file_put_contents($folderName . $fileName, $out, ($append ? FILE_APPEND : false));
    }

    private function getAllHeaders():array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    private function getFileList(string $directory, string $extension = ''):array
    {
        $fileList = [];

        $dir = new DirectoryIterator($directory);

        foreach ($dir as $fileInfo) {
            if (!$fileInfo->isDot() && !$fileInfo->isDir() && $fileInfo->getFilename()[0] !== '.' && ($fileInfo->getExtension() == $extension || Empty($extension))) {
                $fileList[] = $fileInfo->getFilename();
            }
        }

        sort($fileList);

        return $fileList;
    }
}