<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Language.php 339 2011-10-07 10:40:45Z beimuaihui $
 */

namespace Baogg;

class Language
{
    protected static $loaded_files = array();

    public static function loadFile($file)
    {
        global $LANG;
        $LANG = $LANG ? $LANG : array();

        $path = BAOGG_ROOT . 'app/View/lang/' . self::getBrowserLang() . '/Global.php';
        if (is_file($path) && !isset(self::$loaded_files[$path])) {
            $_lang = include $path;
            $LANG = array_merge($LANG, $_lang);
            self::$loaded_files[$path] = 1;
        }

        $path = BAOGG_ROOT . 'app/View/lang/' . self::getBrowserLang() . '/' . $file . ".php";
        if (is_file($path) &&  !isset(self::$loaded_files[$path])) {
            //var_dump($path);exit;
            $_lang = include $path;
            // \Baogg\App::getLogger()->debug(" path = {$path}; lang =" . var_export($_lang, true));

            $LANG = $LANG ? array_merge($LANG, $_lang) : $_lang; //防止$LANG=null的情况
            self::$loaded_files[$path] = 1;
        }
    }

    public static function get($key)
    {
        global $LANG;

        if (!$LANG) {
            return $key;
        }

        // var_dump($key, $LANG);
        //\Baogg\App::getLogger()->debug("LANG = " . var_export($LANG, true));

        $key = is_string($key) ? trim($key) : $key;
        $matches = array();

        if (isset($LANG[$key])) {
            return $LANG[$key];
        } else if (is_int($key) && isset($LANG['CODE__' . $key])) {
            return $LANG['CODE__' . $key];
        } elseif (preg_match('/^([\-\|]+)(.+)$/', trim($key), $matches)) {
            //such as ---------|-Role,tree combo
            return $matches[1] . self::get($matches[2]);
        } elseif (preg_match('/^(.+?)(\d+)$/', trim($key), $matches)) {
            //such as role2; will change to role
            return self::get($matches[1]);
        } else {
            return $key;
        }
    }
    public static function outputResult($ret)
    {
        if (isset($ret['msg'])) {
            $ret['msg'] = \Baogg\Language::get($ret['msg']);
        }
        return json_encode($ret);
    }
    //translate array("1"=>"a","2"=>"b") to array(array(1,a),array(2,b)),for form combobox store
    public static function array2store($key)
    {
        global $LANG;

        $ret = array();

        if (is_array($key)) {
            foreach ((array)$key as $k => $v) {
                $ret[] = array('' . $k, $v);
            }
        } elseif (isset($LANG[$key])) {
            $rs = $LANG[$key];
            foreach ((array)$rs as $k => $v) {
                $ret[] = array('' . $k, $v);
            }
        }

        return $ret;
    }

    /**
     * 获取浏览器的语言
     *
     * @return string
     */
    public static function getBrowserLang(): string
    {
        $supportedLangs = array('zh-CN', 'en');

        $languages = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) : array('zh-CN');

        foreach ($languages as $lang) {
            if (in_array($lang, $supportedLangs)) {
                // Set the page locale to the first supported language found
                return $lang;
            }
        }
        return 'en';
    }
}
