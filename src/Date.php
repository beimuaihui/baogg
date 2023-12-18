<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Date.php 239 2011-06-13 04:08:13Z beimuaihui $
 */ 
namespace Baogg;
class Date {
	static function month2quarter($month='')
	{
		$month=(int)$month;
		return floor(($month-1)/3);
	}
	static function quarter2month($quarter='')
	{
		$ret=array();
		$quarter=(int)$quarter;
		$pre_month=($quarter-1)*3;
		for($i=1;$i<=3;$i++){
			$ret[]=$pre_month+$i;
		}
		return $ret;
	}
}

?>