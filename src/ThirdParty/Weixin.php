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
    
    

    /**
     * 生成客户端access token
     *
     * @param string $app_id
     * @param string $app_secret
     * @return token
     * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getStableAccessToken.html
     *
     */
    public static function getClientStableAccessToken($app_id = '', $app_secret = '', $force_refresh = false)
    {


        $weapp_url = "https://api.weixin.qq.com/cgi-bin/stable_token";
        $res_weapp = \Baogg\File\Html::getByPostJsonCurl($weapp_url, array('grant_type' => 'client_credential','appid' => $app_id, 'secret' => $app_secret));


        error_log(__FILE__ . __LINE__ . "\n res_weapp = " . var_export($res_weapp, true));

        if (!$res_weapp || !isset($res_weapp['access_token']) || !$res_weapp['access_token']) {
            return  '';
        }

        $client_access_token = $res_weapp['access_token'];
        \Baogg\Redis\PhpRedis::getInstance()->set("wxToken:" . $app_id, $res_weapp['access_token'], $res_weapp['expires_in']);

        return $client_access_token;
    }


    /**
     * 生成小程序二维码
     *
     * @param string $client_access_token
     * @param array $params ,例
     * @return string 小程序二维码图片内容
     *
     * @see https://developers.weixin.qq.com/minigame/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
     */
    public static function getWXACodeUnlimited($client_access_token = '', $params = [])
    {

        return \Baogg\File\Html::getByPostJsonCurl("https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$client_access_token}", $params);
    }

    /**
     * 获取手机号
     *@link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
     * 
     * @param [type] $access_token
     * @param [type] $code
     * @param string $openid
     * @return void
     */
    public static function getUserPhoneNumber($access_token, $code, $openid = '')
    {
        $params = array('code' => $code);
        if($openid) {
            $params['openid'] = $openid;
        }

        return \Baogg\File\Html::getByPostJsonCurl("https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$access_token}", $params);
    }


    /**
     * 强制获取ClientToken并回调，主要是部分client token可能过期
     *
     * @param int $customer_id
     * @param callable Anonymous functions with one argument $client_token
     * @return mixed
     */
    public static function getAllClientToken($app_id,$app_secret,$callable)
    {
        // 暂时没有启动小程序二维码,开发版已经过期,正式版本还没有
        //error_log(__FILE__.__LINE__." \n arr_params = ".var_export($arr,true) );

        

        if($app_id && $app_secret) {
            $client_token = \Baogg\ThirdParty\Weixin::getClientAccessToken($app_id, $app_secret);
            $qr_file_content = $callable($client_token);
            //error_log(__FILE__.__LINE__." \n qr_file_content = ".var_export($qr_file_content,true) );

            // access token 错误
            if(strpos($qr_file_content, '"errcode":40001') !== false) {
                $client_token = \Baogg\ThirdParty\Weixin::getClientAccessToken($app_id, $app_secret, true); //不能刷新access_token ?
                $qr_file_content = $callable($client_token);
            }


            // access token 错误
            if(strpos($qr_file_content, '"errcode":40001') !== false) {
                $client_token = \Baogg\ThirdParty\Weixin::getClientStableAccessToken($app_id, $app_secret); //不能刷新access_token ?
                $qr_file_content = $callable($client_token);;
                //error_log(__FILE__.__LINE__." \n qr_file_content = ".var_export($qr_file_content,true) );
            }

            // 小程序未发布或者页面不存在
            if(strpos($qr_file_content, '"errcode":41030') !== false) {
                $qr_file_content = $callable($client_token);
            }


            return $qr_file_content;
        }

        return false;


    }
}
