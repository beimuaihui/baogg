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

class Logger
{
    public static function err($msg = '', $file_path = '', $line_num = 0)
    {
        if(\Baogg\Db\Table::isDev()) {
            //echo "\n\n\n file and line number =  {$file_path}:{$line_num};\n msg = ".var_export($msg, true);
            \error_log("\n\n\n file and line number =  {$file_path}:{$line_num};\n msg = ".var_export($msg, true));
            //\Baogg\ChromePhp::error($file_path, $line_num, $msg);
        }
    }
}