<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Controller.php 197 2011-02-18 12:45:33Z beimuaihui $
 */
namespace Baogg;

class Utils
{
    public static function get($key)
    {
        $ret = "";
        if (! $key || ! is_string($key)) {
            return null;
        }
        $arr = explode(".", $key);
        if (count($arr) < 2) {
            return $ret;
        }
        $type = $arr[0];
        unset($arr[0]);
        switch ($type) {
            case 'l': //multi language
                $ret = \Baogg\Language::get($arr[1]);
                break;
            case 's': //session
                $ret = $_SESSION;
                foreach ($arr as $v) {
                    if (isset($ret[$v])) {
                        $ret = $ret[$v];
                    } else {
                        $ret = "";
                        break;
                    }
                }
                break;
            case 'c': //cookie
                $ret = $_COOKIE;
                foreach ($arr as $v) {
                    if (isset($ret[$v])) {
                        $ret = $ret[$v];
                    } else {
                        $ret = "";
                        break;
                    }
                }
                break;
           case 'm': //cookie
                global $MY;
                $ret = $MY;
                foreach ($arr as $v) {
                    if (isset($ret[$v])) {
                        $ret = $ret[$v];
                    } else {
                        $ret = "";
                        break;
                    }
                }
                break;
        }
        return $ret;
    }
    public static function set($key, $value)
    {
        $res = false;
        if (! $key || ! is_string($key)) {
            return $res;
        }
        $arr = explode(".", $key);
        if (count($arr) < 2) {
            return $res;
        }
        $type = $arr[0];
        unset($arr[0]);
        switch ($type) {
            case 's': //session
                $ret = & $_SESSION;
                foreach ($arr as $k => $v) {
                    //not array ,then init array,else if not exist data add key=$v
                    if (! is_array($ret)) {
                        $ret = array($v => array());
                    } elseif (!isset($ret[$v])) {
                        $ret[$v] = array();
                    }
                    $ret = & $ret[$v];
                }
                $ret = $value;
                $res = true;
                //echo __FILE__.__LINE__.'<pre>';print_r($_SESSION);echo '<br />';
                break;
            case 'c': //cookie
                $ret = & $_COOKIE;
                foreach ($arr as $k => $v) {
                    //echo '<pre>';print_r($arr);print_r($ret);debug_zval_dump($_SESSION);echo '<br />';
                    //not array ,then init array,else if not exist data add key=$v
                    if (! is_array($ret)) {
                        $ret = array($v => array());
                    } elseif (!isset($ret[$v])) {
                        $ret[$v] = array();
                    }
                    $ret = & $ret[$v];
                }
                $ret = $value;
                $res = true;
                break;
             case 'm': //cookie
                global $MY;
                $ret = & $MY;
                foreach ($arr as $k => $v) {
                    //echo '<pre>';print_r($arr);print_r($ret);debug_zval_dump($_SESSION);echo '<br />';
                    //not array ,then init array,else if not exist data add key=$v
                    if (! is_array($ret)) {
                        $ret = array($v => array());
                    } elseif (!isset($ret[$v])) {
                        $ret[$v] = array();
                    }
                    $ret = & $ret[$v];
                }
                $ret = $value;
                $res = true;
                break;
        }
        return $res;
    }

    /*＊
     * getRandomWeightedElement()
     * Utility function for getting random values with weighting.
     * Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
     * An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
     * The return value is the array key, A, B, or C in this case.  Note that the values assigned
     * do not have to be percentages.  The values are simply relative to each other.  If one value
     * weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
     * chance of being selected.  Also note that weights should be integers.
     *
     * @param array $weightedValues
     * return string key
     */
    public static function getRandomWeightedElement(array $weightedValues)
    {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        return '';
    }


    public static function uuidV4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }


    public static function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:'';
        $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'';
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}
