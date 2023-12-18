<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 240 2011-06-13 13:02:06Z beimuaihui $
 */

namespace Baogg\OS;

class Process
{
    static function existsProcess($pname)
    {
        if (file_exists("/tmp/{$pname}.pid")) {
            $pid = file_get_contents("/tmp/{$pname}.pid");
            if (file_exists("/proc/$pid")) {
                echo ("found a running instance, exiting.");
                return true;
            } else {
                echo ("previous process exited without cleaning pidfile, removing");
                unlink("/tmp/{$pname}.pid");
            }
        }
        $h = fopen("/tmp/{$pname}.pid", 'w');
        if ($h) fwrite($h, getmypid());
        fclose($h);
        return false;
    }

    /**
     * 只适用于cli模式下，如apache http模式下无效
     *
     * @param string $cmd
     * @return void
     */
    static function execInBackground($cmd)
    {

        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $cmd, "r"));
        } else {
            exec($cmd . " > /dev/null 2>&1 &");
        }
    }

    static function getAsyncHttp($url = '')
    {
        self::execInBackground("/usr/bin/curl {$url}");
    }
}
