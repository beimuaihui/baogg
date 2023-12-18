<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 292 2011-08-12 08:37:34Z beimuaihui@gmail.com $
 */

namespace Baogg;

class File
{
    public static function ReadDirFile($dir)
    {
        $files = array();

        if (!is_dir($dir)) {
            return $files;
        }

        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (is_file($dir . $filename)) {
                $files[] = $filename;
            }
        }
        return $files;
    }

    public static function genPdfByUrl($url, $pdfName = '', $dir = '')
    {
        if (!$pdfName) {
            $pdfName = md5($url);
        } else {
            $pdfName = self::fixFileName($pdfName);
        }

        $dir = $dir ? $dir : BAOGG_UPLOAD_DIR . 'pdf/';
        $flag = self::mkdir($dir);
        $flag = $flag && self::genHtmlByUrl($url, $pdfName);

        $bin = BAOGG_UPLOAD_DIR . 'bin/wkhtmltopdf';
        if ($flag) {
            exec($bin . ' ' . BAOGG_UPLOAD_DIR . 'html/' . $pdfName . '.html ' . BAOGG_UPLOAD_DIR . 'pdf/' . $pdfName . '.pdf');
        }
    }
    public static function genHtmlByUrl($url, $htmlName = '')
    {
        $htmlName = $htmlName ? self::fixFileName($htmlName) : md5($url);

        $content = file_get_contents($url);
        $dir = BAOGG_UPLOAD_DIR . 'html/';
        if (self::mkdir($dir)) {
            return file_put_contents($dir . $htmlName . '.html', $content) !== false;
        } else {
            return false;
        }
    }

    public static function genPdfByContent($content, $pdfName = '')
    {
        if (!$pdfName) {
            $pdfName = md5($content);
        } else {
            $pdfName = self::fixFileName($pdfName);
        }

        $dir = BAOGG_UPLOAD_DIR . 'pdf/';
        $flag = self::mkdir($dir);
        $flag = $flag && self::genHtmlByContent($content, $pdfName);

        $bin = BAOGG_UPLOAD_DIR . 'bin/wkhtmltopdf/wkhtmltopdf.exe';
        if ($flag) {
            //echo ($bin.' '.BAOGG_UPLOAD_DIR.'html/'.$pdfName.'html '.BAOGG_UPLOAD_DIR.'pdf/'.$pdfName.'pdf' );exit;
            exec($bin . ' ' . BAOGG_UPLOAD_DIR . 'html/' . $pdfName . '.html ' . BAOGG_UPLOAD_DIR . 'pdf/' . $pdfName . '.pdf');
        }
    }
    public static function genHtmlByContent($content, $htmlName = '')
    {
        $htmlName = $htmlName ? self::fixFileName($htmlName) : md5($content);

        $dir = BAOGG_UPLOAD_DIR . 'html/';
        if (self::mkdir($dir)) {
            return file_put_contents($dir . $htmlName . '.html', $content) !== false;
        } else {
            return false;
        }
    }

    public static function download($name, $dest = 'D')
    {
        $path_parts = pathinfo($name);
        $fileName = $path_parts['basename'];

        if (!file_exists($name)) {
            return;
        }

        switch ($dest) {
            case 'I':
                //Send to standard output
                if (isset($HTTP_SERVER_VARS['SERVER_NAME'])) {
                    //We send to a browser
                    if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT']) and strpos($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE')) {
                        Header('Content-Type: application/force-download');
                    } else {
                        Header('Content-Type: application/octet-stream');
                    }
                    if (headers_sent()) {
                        //self::Error('Some data has already been output to browser, can\'t send PDF file');
                        Header('Content-Length: ' . filesize($name));
                    }
                    Header('Content-disposition: inline; filename=' . $fileName);
                }
                ob_clean();
                flush();
                $flag = @readfile($name);
                break;
            case 'D':
                //Download file
                if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT']) and strpos($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE')) {
                    Header('Content-Type: application/force-download');
                } else {
                    Header('Content-Type: application/octet-stream');
                }
                if (headers_sent()) {
                    echo('Some data has already been output to browser, can\'t send PDF file');
                }

                Header('Content-Length: ' . filesize($name));
                Header('Content-disposition: attachment; filename=' . $fileName);
                ob_clean();
                flush();
                $flag = @readfile($name);
                break;
        }
        return $flag !== false;
    }

    public static function mkdir($dir)
    {
        if (is_dir($dir)) {
            try {
                //@chmod($dir, 0777);
            } catch (\Exception $e) {
            }
            return true;
        }
        $flag = mkdir($dir, 0777, true);
        return $flag;
    }

    //windows maxium filename length is 255,and not including following 9 chars, '/','?','\\','*','|','"','<','>',':'
    public static function fixFileName($name = '')
    {
        return $name ? substr(str_replace(array(" ", '/', '?', '\\', '*', '|', '"', '<', '>', ':'), "_", trim($name)), 0, 255) : date("YmdHis") . mt_rand();
    }

    /**
     *
     * Transfrom relative path into absolute URL using PHP
     * @param relative path $rel
     * @param hostname $base
     * @return unknown|string
     */
    public static function fixUrlName($rel, $base)
    {


        /* return if already absolute URL */

        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?') {
            return $base . $rel;
        }

        /* parse base URL and convert to local variables:
        $scheme, $host, $path */
        extract(parse_url($base));

        if (strpos($rel, '//' . $host) === 0) {
            $rel = $scheme . ':' . $rel;
            if (parse_url($rel, PHP_URL_SCHEME) != '') {
                return $rel;
            }
        }

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/') {
            $path = '';
        }

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }


    public static function getMime($file_name)
    {
        if (function_exists("finfo_file")) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $mime = finfo_file($finfo, $file_name);
            finfo_close($finfo);
            return $mime;
        } elseif (function_exists("mime_content_type")) {
            return mime_content_type($file_name);
        } elseif (!stristr(ini_get("disable_functions"), "shell_exec")) {
            // http://stackoverflow.com/a/134930/1593459
            $file = escapeshellarg($file_name);
            $mime = shell_exec("file -bi " . $file);
            return $mime;
        } else {
            return '';
        }
    }

    public static function getSetting($key = '')
    {
        if(!defined('BAOGG_ROOT')) {
            throw new \Exception("Please define BAOGG_ROOT path to your app directory path");
        }
        $path = \Baogg\App::isDev() ? realpath(BAOGG_ROOT. 'config/settings_local.php') : realpath(BAOGG_ROOT. 'config/settings.php');
        //error_log(__FILE__ . __LINE__ . " path = {$path}");
        $arr_setting = include $path;

        $ret = $arr_setting;
        if (!$key) {
            return $ret;
        }

        $arr_key = explode('.', $key);
        foreach ($arr_key as $v_key) {
            if (is_array($ret) && isset($ret[$v_key])) {
                $ret = $ret[$v_key];
            } else {
                return '';
            }
        }

        return $ret;
    }
}
