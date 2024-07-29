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
    static function upload($des_path){
        $ret = array ('error' => 0, 'msg' => '');
        //如果开启了oss
        if(SHANMING_OSS_ON===true && SHANMING_OSS_ISUPLOAD){  //not dev server
            include_once ROOT_DIR."shanming/util/aliyun-oss-php-sdk/autoload.php";
            $ossClient = new \OSS\OssClient(SHANMING_OSS_KEY, SHANMING_OSS_SECRET, SHANMING_OSS_URL);
            try {

                $target_path = str_replace ( ROOT_DIR, '', ($des_path) );//uploadToOSS
                $rs_oss = $ossClient->multiuploadFile(SHANMING_OSS_BUCKET, $target_path, $des_path);
            } catch (\OSS\Core\OssException $e) {
                return array ('error' => - 1, 'msg' => $e->getMessage() );
            }
        }
        return $ret;
    }

    static function show($url,$opt=array('webp'=>true,'base'=>SHANMING_OSS_PATH)){
        if(!$url){
            return $url;
        }
        if(!isset($opt['webp'])){
            $opt['webp']=true;
        }
        if(!isset($opt['base'])){
            $opt['base']=SHANMING_OSS_PATH;
        }
        return SHANMING_OSS_ON?\Baogg\File::fixUrlName($url , $opt['base'],$opt):$url;
    }

    static function show2($url,$base='',$opt=array('webp'=>false)){
        if(!$url){
            return $url;
        }
        if(!isset($opt['webp'])){
            $opt['webp']=true;
        }
        if(!$base){
            $base=self::getBaseUrl();
        }

        return \Baogg\File::fixUrlName($url , $base,$opt);
    }

    static  function showSiteUrl($url,$opt=array('webp'=>false)){
        return self::show2($url,Protocol.HostUrl,$opt);
    }

    static function getBaseUrl(){
        return SHANMING_OSS_ON?SHANMING_OSS_PATH:Protocol.HostUrl;
    }

    static function gmt_iso8601($time)
    {
        return str_replace('+00:00', '.000Z', gmdate('c', $time));
    }
}
