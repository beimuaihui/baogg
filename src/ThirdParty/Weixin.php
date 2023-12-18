<?php
/**
 * Created by PhpStorm.
 * User: bao
 * Date: 18-7-11
 * Time: 下午12:04
 */

namespace Baogg\ThirdParty;

class Weixin
{
    public static function isWeixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    public static function isMiniApp()
    {
        $a_strtolower = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($a_strtolower, "micromessenger")) {//公众号MicroMessenger
            if(strpos($a_strtolower, "miniprogram")) {//小程序
                return true;
            }

        }

        return false;

    }



    public static function getClientAccessToken($app_id = '', $app_secret = '')
    {
        $client_access_token = \Baogg\Redis\PhpRedis::getInstance()->get("wxToken:" . $app_id);
        if($client_access_token) {
            return $client_access_token;
        }


        $weapp_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$app_id}&secret={$app_secret}";
        $res_weapp = \Baogg\File\Html::getJsonByCurl($weapp_url);


        error_log(__FILE__ . __LINE__ . "\n res_weapp = " . var_export($res_weapp, true));

        if (!$res_weapp || !isset($res_weapp['access_token']) || !$res_weapp['access_token']) {
            return  '';
        }

        $client_access_token = $res_weapp['access_token'];
        \Baogg\Redis\PhpRedis::getInstance()->set("wxToken:" . $app_id, $res_weapp['access_token'], $res_weapp['expires_in'] - 60);

        return $client_access_token;
    }
}
