<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 240 2011-06-13 13:02:06Z beimuaihui $
 */

namespace Baogg\File\Oss;

class AliyunOss extends \Baogg\File
{
    public static function upload($des_path)
    {
        $ret = array('error' => 0, 'msg' => '');

        $ossClient = new \OSS\OssClient(SHANMING_OSS_KEY, SHANMING_OSS_SECRET, SHANMING_OSS_URL);
        try {

            $target_path = str_replace(ROOT_DIR, '', ($des_path));//uploadToOSS
            $rs_oss = $ossClient->multiuploadFile(SHANMING_OSS_BUCKET, $target_path, $des_path);
        } catch (\OSS\Core\OssException $e) {
            return array('error' => - 1, 'msg' => $e->getMessage() );
        }
        return $ret;
    }

    public static function show($url, $opt = array('webp' => true,'base' => URL_CDN))
    {
        if(!$url) {
            return $url;
        }
        if(!isset($opt['webp'])) {
            $opt['webp'] = true;
        }
        if(!isset($opt['base'])) {
            $opt['base'] = URL_CDN;
        }
        return  \Baogg\File::fixUrlName($url, $opt['base'], $opt);
    }

    public static function show2($url, $base = '', $opt = array('webp' => false))
    {
        if(!$url) {
            return $url;
        }
        if(!isset($opt['webp'])) {
            $opt['webp'] = true;
        }
        if(!$base) {
            $base = self::getBaseUrl();
        }

        return \Baogg\File::fixUrlName($url, $base, $opt);
    }

    public static function showSiteUrl($url, $opt = array('webp' => false))
    {
        return self::show2($url, Protocol.HostUrl, $opt);
    }

    public static function getBaseUrl()
    {
        return URL_CDN;
    }

    public static function gmt_iso8601($time)
    {
        return str_replace('+00:00', '.000Z', gmdate('c', $time));
    }
}
