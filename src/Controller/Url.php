<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Url.php 202 2011-02-28 02:22:44Z beimuaihui $
 */
namespace Baogg\Controller;
class Url {
	public static function generate($params=array()){
		if(!is_array($params)){
			return $params;
		}
		$arr_sys=array("module","controller","action");
		$url="";
		
		foreach((array)$arr_sys as $v){
			if(isset($params[$v])){
				$url.=$params[$v].'/';
				unset($params[$v]);
			}
		}
		
		foreach((array)$params as $k=>$v){
			//if(is_array($k) || is_array($v)){echo __FILE__.__LINE__.'<pre>';print_r($k);print_r($v);print_r($params);exit;}
			$url.=$k.'/'.$v.'/';
		}
		
		return BAOGG_BASE_URL.$url;
	}
	
	public static function reverse($url=''){
		if(BAOGG_BASE_URL && strpos($url,BAOGG_BASE_URL)===0){
			$url=substr($url,strlen(BAOGG_BASE_URL) );
		}
		$arr_url=explode("/",$url);
		if(count($arr_url)<3){
			echo __FILE__.__LINE__.'<pre>';print_r($arr_url);var_dump($url);exit;
		}
		$params['module']=$arr_url[0];
		$params['controller']=$arr_url[1];
		$params['action']=$arr_url[2];
		for($i=3;isset($arr_url[$i]) && $arr_url[$i]!="";$i+=2){
			$params[$arr_url[$i]]=@$arr_url[$i+1];
		}
		return $params;		
	}

	public static function getRealPost($key='') {
		$pairs = explode("&", file_get_contents("php://input"));
		$vars = array();
		foreach ($pairs as $pair) {

			$nv = explode("=", $pair);
			$name = urldecode($nv[0]);
			$value = urldecode($nv[1]);

			if(isset($vars[$name])){
				$vars[$name] .= ','.$value;
			}else{
				$vars[$name] = $value;
			}
			
		}	
		//echo __FILE__.__LINE__.'<pre>';print_r($vars);var_dump(file_get_contents("php://input"));exit;    
		if($key){
			if(!array_key_exists($key, $vars)){
				return '';
			}else{
				return $vars[$key];
			}
		}else{
			return $vars;
		}
	}


	public static function getIp() {
		if (isset($_SERVER)) {

			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
				return $_SERVER["HTTP_X_FORWARDED_FOR"];

			if (isset($_SERVER["HTTP_CLIENT_IP"]))
				return $_SERVER["HTTP_CLIENT_IP"];

			return $_SERVER["REMOTE_ADDR"];
		}

		if (getenv('HTTP_X_FORWARDED_FOR'))
			return getenv('HTTP_X_FORWARDED_FOR');

		if (getenv('HTTP_CLIENT_IP'))
			return getenv('HTTP_CLIENT_IP');

		return getenv('REMOTE_ADDR');
	}



	/**
	 * generate serial product num
	 *
	 * @param varchar $str1,such as A01B99A
	 * @param varchar  $str2,such as A89B99A
	 * @param int $step,such as 1
	 * @return array,serial product num 
	 */
	public static function genSerialNum($str1, $str2, $step = 1) {
		
		$arr_matches_start = array ();
		$arr_matches_end = array ();
		$reg_num = '/(\D*+)(\d+)(\D*+)/m';
		$flag=1;
		$step=abs($step);
		
		preg_match_all ( $reg_num, $str1, $arr_matches_start );	
		preg_match_all ( $reg_num, $str2, $arr_matches_end );	
		//echo '<pre>';print_r($arr_matches_start);print_r($arr_matches_end);	

		$ret  = array();
		foreach ( $arr_matches_start [0] as $k => $v ) {		
			if ($arr_matches_start [1] [$k] != $arr_matches_end [1] [$k]) { //\D uneuqal ,return parse error,
				$flag = 0;
				//echo "parse error";
				break;
			}
			
			
			if (strlen ( $arr_matches_start [2] [$k] ) == strlen ( $arr_matches_end [2] [$k] )) { //compare digit,equal length, then add 0 prefix
				$format = "%0" . strlen ( $arr_matches_start [2] [$k] ) . "d";			
			} else{
				$format = "%d";		
			}
			$begin = ( int ) $arr_matches_start [2] [$k];
			$end = ( int ) $arr_matches_end [2] [$k];					
			
			if($begin>$end){
				$i=$end;
				$end=$begin;
				$begin=$i;
			}
			//echo ' euqla';var_dump($begin);var_dump($end);var_dump($step);	
			$ret2=array();
			if($k==0){
				for($i = $begin ; $i <= $end; $i =$i+$step) { //+ $step
					$ret2 [] = $arr_matches_start [1] [$k] . sprintf ( $format, $i ) . $arr_matches_start[3][$k];					
				}
			}else{
				foreach((array)$ret as $line){
					for($i = $begin ; $i <= $end; $i =$i+$step) { //+ $step
						$ret2 [] = $line.$arr_matches_start [1] [$k] . sprintf ( $format, $i ) . $arr_matches_start[3][$k];					
					}
				}
			}
			$ret=$ret2;
			unset($ret2);
		}
		//$ret  = array();
		return $flag?$ret:array();
	}
	
	
	public static function getCurrentUrl() {
	     return (!empty($_SERVER['HTTPS'])) ? rtrim("https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],'/').'/' : rtrim("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],'/').'/';

	}


}