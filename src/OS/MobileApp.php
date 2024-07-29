<?php


namespace Baogg\OS;


class MobileApp
{
    const ANDROID_DEVICE='android';
    const IPHONE_DEVICE='iphhone';
    const MINI_APP_DEVICE = 'mini_app';
    const WECHAT_DEVICE = 'wechat';


    public static $arr_pkg = [
        41 => 'com.ds2319.wx',
        67 => 'com.changshengyi.wx',
    ]; // 默认包名

    public static $arr_app_pkg_type = [
        'com.ds2319.wx' => 2,
        'com.changshengyi.wx' => 2,
    ]; // 包名对应的类型


    public static function isApp(){
        return strpos($_SERVER['HTTP_USER_AGENT'], 'sr_wsy_xy_user') !== false ;
    }

    public static function isIphone(){
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== FALSE);
    }

    public static function isAndroid(){
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false) ;
    }

    public static function isIphoneApp(){
        return self::isIphone() && self::isApp();
    }

    public static function isAndroidApp(){
        return self::isAndroid() && self::isApp();
    }

    public static function isMiniApp(){
        $a_strtolower = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($a_strtolower, "micromessenger") || strpos($a_strtolower, "miniprogram"))//公众号MicroMessenger
        {
           return true;
        }

        return false;

    }

    /**
     * 微信浏览器，包括公众号和小程序
     *
     * @return boolean
     */
    public static function isWeixin(){
        $a_strtolower = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($a_strtolower, "micromessenger"))//公众号MicroMessenger
        {
            return true;
            if(strpos($a_strtolower, "miniprogram"))//小程序
            {
                return false;
            }
            else {
                return true;
            }

        }

        return false;

    }


    public static function isSimpleMode(){
        $flag =  self::isApp() && self::isIphone() && strpos($_SERVER['HTTP_USER_AGENT'], 'sr_wsy_xy_user/2018091111')!==false;
        if(!$flag){
            $flag = self::isApp() && self::isAndroid() && strpos($_SERVER['HTTP_USER_AGENT'], 'sr_wsy_xy_user/1.3')!==false;
        }
        if(!$flag){
            $flag = !self::isApp();
        }
        return $flag;
    }

    public static function enableIOSLogin(){
        return \Baogg\File::getSetting('shanming.app.enable_ios_login');
    }
}
