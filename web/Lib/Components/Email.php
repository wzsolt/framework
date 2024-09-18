<?php

namespace Framework\Components;

use Framework\Components\Enums\RecipientType;
use Framework\Helpers\Str;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;
use Framework\View;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    const MAIL_NEW_PASSWORD = 'new-password';
    const MAIL_REQUEST_NEW_PASSWORD = 'request-new-password';
    const MAIL_THANK_YOU = 'thank-you';
    const MAIL_NEW_REGISTRATION = 'new-registration';

    const STATE_OK = 1;
    const STATE_NOT_SENT = 2;
    const STATE_MISSING_SENDER = 3;
    const STATE_MISSING_RECIPIENT = 4;
    const STATE_MISSING_SUBJECT = 5;
    const STATE_MISSING_BODY = 6;
    const STATE_SMTP_ERROR = 100;

    const EMAIL_STATES = [
        self::STATE_OK => 'LBL_EMAIL_STATE_OK',
        self::STATE_NOT_SENT => 'LBL_EMAIL_STATE_NOT_SENT',
        self::STATE_MISSING_SENDER => 'LBL_EMAIL_STATE_MISSING_SENDER',
        self::STATE_MISSING_RECIPIENT => 'LBL_EMAIL_STATE_MISSING_RECIPIENT',
        self::STATE_MISSING_SUBJECT => 'LBL_EMAIL_STATE_MISSING_SUBJECT',
        self::STATE_MISSING_BODY => 'LBL_EMAIL_STATE_MISSING_BODY',
        self::STATE_SMTP_ERROR => 'LBL_EMAIL_STATE_SMTP_ERROR'
    ];

    private static Email $instance;

    private PHPMailer $mail;

    private int $id;
    private string $language;
    private string $tag;
    private string $from;
    private string $fromName;
    private string $replyTo;
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private string $subject;
    private string $body;
    private string $mailId;
    private array $attachments = [];
    private array $data = [];
    private string $baseTemplate = 'mail';
    private string $template;
    private string $host;

    public static function create():Email
    {
        self::$instance = new Email();

        self::$instance->init();

        return self::$instance;
    }

    public function init():self
    {
        $hostConfig = HostConfig::create();
        $smtpConfig = $hostConfig->getSmtpConfig();

        $this->mail = new PHPMailer(true);
        if (!empty($smtpConfig)) {
            $this->mail->IsSMTP();
            $this->mail->SMTPDebug = 0;
            $this->mail->SMTPAuth = true;
            $this->mail->SMTPAutoTLS = false;
            $this->mail->SMTPSecure = strtolower($smtpConfig['ssl']);
            $this->mail->Port = $smtpConfig['port'];
            $this->mail->Host = $smtpConfig['host'];
            $this->mail->Username = $smtpConfig['user'];
            $this->mail->Password = $smtpConfig['password'];
        }

        $this->mail->IsHTML();
        $this->mail->CharSet = 'utf-8';

        $this->id = 0;
        $this->tag = '';
        $this->host = $hostConfig->getHost();

        $this->language = $hostConfig->getLanguage();
        $this->from = '';
        $this->fromName = '';
        $this->replyTo = '';

        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->subject = '';
        $this->body = '';
        $this->mailId = Uuid::v4();
        $this->attachments = [];

        return $this;
    }

    public function setFrom(string $email = '', string $name = ''):self
    {
        if(Empty($email)){
            $siteSettings = SiteSettings::create();

            $email = ($siteSettings->get('outgoingEmail') ?: EMAIL_SENDER_EMAIL);
            $name = ($siteSettings->get('emailSenderName') ?: EMAIL_SENDER_NAME);
        }

        if (Utils::checkEmail($email)) {
            $this->from = $email;
            $this->fromName = $name;
        }

        return $this;
    }

    public function setReplyTo(string $email):self
    {
        if (Utils::checkEmail($email)) {
            $this->replyTo = $email;
        }

        return $this;
    }

    public function setTemplate(string $template, array $data = []):self
    {
        $this->template = $template;

        $this->setTemplateData($data);

        return $this;
    }

    public function setTemplateData(array $data):self
    {
        $this->data = $data;

        return $this;
    }

    public function addRecipient(string $email, RecipientType $type = RecipientType::To):self
    {
        if (!Utils::checkEmail($email)) {
            return $this;
        }

        switch($type){
            case RecipientType::To:
            default:
                if(!in_array($email, $this->to)){
                    $this->to[] = $email;
                }

                break;

            case RecipientType::Cc:
                if(!in_array($email, $this->cc)){
                    $this->cc[] = $email;
                }
                break;

            case RecipientType::Bcc:
                if(!in_array($email, $this->bcc)){
                    $this->bcc[] = $email;
                }
                break;
        }

        return $this;
    }

    public function addUser(int $userId, RecipientType $type = RecipientType::To):self
    {
        $user = User::getUserProfile($userId);

        if($user && !Empty($user['email'])){
            $this->addRecipient($user['email'], $type);
        }

        return $this;
    }

    public function setSubject(string $subject):self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setBody(string $body):self
    {
        $this->body = $body;

        return $this;
    }

    public function addAttachment(string $file, string $fileName = ''):self
    {
        $savePath = DIR_CACHE . 'emails/' . $this->mailId . '/';
        if (!is_dir($savePath)) {
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
        }
        if (!$fileName) {
            $fileName = basename($file);
        }
        $key = sha1_file($file);
        if (copy($file, $savePath . $key)) {
            $this->attachments[$key] = $fileName;
        }

        return $this;
    }

    public function send():int
    {
        $status = 0;

        if(Empty($this->from)){
            $this->setFrom();
        }

        if(!Empty($this->template)){
            $this->loadTemplate($this->template);
        }

        $this->prepareMailBody($this->data);

        $this->save();

        if (defined('EMAIL_INSTANT_SEND') && EMAIL_INSTANT_SEND) {
            $status = $this->sendMail();
        }

        return $status;
    }

    private function save():void
    {
        if (empty($this->id)) {
            $values = [
                'em_userid' => User::create()->getId(),
                'em_language' => $this->language,
                'em_tag' => $this->tag,
                'em_from' => $this->from,
                'em_replyto' => $this->replyTo,
                'em_to' => $this->to,
                'em_cc' => $this->cc,
                'em_bcc' => $this->bcc,
                'em_subject' => $this->subject,
                'em_body' => $this->body,
                'em_mailid' => $this->mailId,
                'em_attachments' => $this->attachments,
                'em_status' => 0,
                'em_domain' => $this->host
            ];

            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    if(!Empty($val)) {
                        $values[$key] = json_encode($val);
                    }else{
                        $values[$key] = '';
                    }
                }
            }

            $db = Db::create();
            $db->sqlQuery(
                Db::insert(
                    'emails',
                    $values
                )
            );

            $this->id = $db->getInsertRecordId();
        }
    }

    private function sendMail():int
    {
        $debugInfo = '';

        if ($check = $this->checkMail()) {
            $status = $check;
        } else {
            try {
                $this->mail->setFrom($this->from, $this->fromName);

                if(!Empty($this->replyTo)){
                    $this->mail->addReplyTo($this->replyTo);
                }

                foreach($this->to AS $email){
                    $this->mail->addAddress($email);
                }

                if(!Empty($this->cc)) {
                    foreach ($this->cc as $email) {
                        $this->mail->addCC($email);
                    }
                }

                if(!Empty($this->bcc)) {
                    foreach ($this->bcc as $email) {
                        $this->mail->addBCC($email);
                    }
                }

                $this->mail->Subject = $this->subject;

                $this->mail->msgHTML($this->body, DOC_ROOT);

                if (!empty($this->attachments)) {
                    foreach ($this->attachments as $key => $filename) {
                        $this->mail->addStringAttachment($this->getAttachment($key), $filename);
                    }
                }

                if (!$this->mail->send()) {
                    $status = self::STATE_NOT_SENT;
                } else {
                    $status = self::STATE_OK;
                    $this->removeAttachments();
                }

            } catch (Exception $e) {
                $status = self::STATE_SMTP_ERROR;
                $debugInfo = $this->mail->ErrorInfo;
            }
        }

        if (!empty($this->id)) {
            Db::create()->sqlQuery(
                Db::update(
                    'emails',
                    [
                        'em_status' => $status,
                        'em_sent' => date("Y-m-d H:i:s"),
                        'em_debug' => $debugInfo
                    ],
                    [
                        'em_id' => $this->id
                    ]
                )
            );
        }

        return $status;
    }

    private function checkMail():int
    {
        if (empty($this->from)) return self::STATE_MISSING_SENDER;

        if (empty($this->to)) return self::STATE_MISSING_RECIPIENT;

        if (empty($this->subject)) return self::STATE_MISSING_SUBJECT;

        if (empty($this->body)) return self::STATE_MISSING_BODY;

        return 0;
    }

    private function loadTemplate(string $templateName): void
    {
        $res = Db::create()->getFirstRow(
            Db::select(
                'templates',
                [],
                [
                    'mt_key' => $templateName,
                    'mt_language' => HostConfig::create()->getLanguage(),
                    'mt_client_id' => HostConfig::create()->getClientId()
                ]
            )
        );

        if (!empty($res)) {
            $this->tag = $res['mt_key'];

            if(Empty($this->subject)) {
                $this->subject = $res['mt_subject'];
            }

            if(Empty($this->body)){
                $this->body = $res['mt_body'];
            }

            if(!Empty($res['mt_template'])){
                $this->baseTemplate = $res['mt_template'];
            }
        }
    }

    private function prepareMailBody(array $data):void
    {
        $this->subject = Str::replaceValues($this->subject, $data);
        //$this->body = Str::replaceValues($this->body, $data);
        $this->body = View::renderFromString($this->body, $data);

        $this->body = View::renderContent($this->baseTemplate, ['contentString' => $this->body], true);
    }

    private function getAttachment($file):string|false
    {
        $filePath = DIR_CACHE . 'emails/' . $this->mailId . '/' . $file;
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        } else {
            return false;
        }
    }

    private function removeAttachments():void
    {
        if ($this->attachments) {
            $filePath = DIR_CACHE . 'emails/' . $this->mailId . '/';
            foreach ($this->attachments as $file => $fileName) {
                if (file_exists($filePath . $file)) {
                    @unlink($filePath . $file);
                }
            }
            @rmdir($filePath);
        }
    }
}
