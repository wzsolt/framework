<?php

namespace Framework\Helpers;

class File
{
    /**
     * Deletes a directory's content recursively
     *
     * @param $p_del string (directory path)
     * @param $b bool (at the end remove the directory itself or not)
     * @return void
     */
    public static function delTree(string $p_del, bool $b = false ):void
    {
        if (empty($p_del)) return;
        $elv = '/';
        if ( $handle = opendir($p_del . $elv) ) {
            while ( $file = readdir($handle) ) {
                if ( $file == '..' || $file == '.' ) continue;
                if ( is_file($p_del . $elv . $file) ) unlink( $p_del . $elv . $file );
                if ( is_dir($p_del . $elv . $file) ) self::delTree( $p_del . $elv . $file, true );
            }
            closedir( $handle );
        }
        if ( $b ) @rmdir($p_del);
    }

    public static function deleteDir($dirPath):void
    {
        if(is_dir($dirPath)) {
            if (!str_ends_with($dirPath, '/')) {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    self::deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dirPath);
        }
    }


    /**
     * Check remote file whether is it exists
     *
     * @param string $url
     * @return bool
     */
    public static function checkRemoteFile(string $url):bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(curl_exec($ch)!==FALSE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Download file form URL
     *
     * @param string $url
     * @param string $path
     * @return string|false
     */
    public static function downloadFile(string $url, string $path):string|false
    {
        $out = false;
        $newFile = false;

        $newFileName = $path;
        $file = fopen ($url, 'rb');
        if ($file) {
            $newFile = fopen ($newFileName, 'wb');
            if ($newFile) {
                while(!feof($file)) {
                    fwrite($newFile, fread($file, 1024 * 8), 1024 * 8);
                }
                $out = $path;
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newFile) {
            fclose($newFile);
        }

        return $out;
    }

    public static function readFileChunked(string $filename, bool $retBytes = true): int|bool
    {
        $cnt =0;
        $handle = fopen($filename, "rb");
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, CHUNK_SIZE);
            echo $buffer;
            ob_flush();
            flush();
            if ($retBytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retBytes && $status) {
            return $cnt; // return num. bytes delivered like readFile() does.
        }

        return $status;
    }

    // Function to remove folders and files
    public static function rrmdir($dir):void
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rrmdir("$dir/$file");
                }
            }
            rmdir($dir);
        } else if(file_exists($dir)) {
            unlink($dir);
        }
    }

    // Function to Copy folders and files
    public static function rcopy($src, $dst):void
    {
        if (file_exists ( $dst )) {
            self::rrmdir($dst);
        }
        if (is_dir ( $src )) {
            mkdir($dst, 0777, true);
            chmod($dst, 0777);

            $files = scandir ( $src );
            foreach ( $files as $file ) {
                if ($file != "." && $file != "..") {
                    self::rcopy("$src/$file", "$dst/$file");
                }
            }
        } else if (file_exists ( $src )) {
            copy($src, $dst);
        }
    }

    public static function getFileUploadMaxSize():int
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    public static function parseSize($size):int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    public static function getFileContentType($file):string
    {
        $mimeType = mime_content_type($file);

        return strstr($mimeType, '/', true);
    }

    /**
     * @param string|array $data data to save
     * @param string $title title line
     * @param string $fileName
     * @param bool $append
     */
    public static function logData(string|array $data, string $title = '', string $fileName = 'log.txt', bool $append = true):void
    {
        $folderName = rtrim(DIR_LOG, '/') . '/';

        if(!is_dir($folderName)){
            @mkdir($folderName, 0777, true);
            @chmod($folderName, 0777);
        }

        $out = '[' . date('Y-m-d H:i:s') . (!Empty($title) ? '] ' . $title : ']')  . "\n";

        if(is_array($data)){
            $out .= print_r($data, true);
        }else{
            $out .= $data;
        }

        if($append){
            $out .= "\n\n-------------------------------------------------------------------------------\n\n";
        }

        @file_put_contents($folderName . $fileName, $out, ($append ? FILE_APPEND : false));
    }

}