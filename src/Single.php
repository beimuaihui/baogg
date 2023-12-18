<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Single.php 197 2011-02-18 12:45:33Z beimuaihui $
 */
namespace Baogg;
class Single {
	protected static $_instance;
	//this can't be construct outside
	protected function __construct(){
		
	}
	public static function getInstance($c=__CLASS__){
		if (!isset(self::$_instance)) {         
            self::$_instance = new $c;
        }
        return self::$_instance;
	}
	public function  __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	
}
