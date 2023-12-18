<?php
namespace Baogg;
class Cache {
	private static $instance;

	private function __construct()
	{
	}

	public static function singleton()
	{
		if (!isset(self::$instance)) {			
			$frontendOptions = array(
			   'lifetime' => 7200, // cache lifetime of 10 minute
			   'automatic_serialization' => true
			);

			$cache_dir=BAOGG_UPLOAD_DIR.'cache/';
			$backendOptions = array(
			    'cache_dir' => $cache_dir // Directory where to put the cache files
			);
			if(!is_dir($cache_dir)){
				mkdir($cache_dir,0777,true);
			}
			// getting a Zend_Cache_Core object
			$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
			self::$instance = $cache;
		}
		return self::$instance;
	}

	public static function get($key='')
	{
		 return self::singleton()->load($key);
	}
	
	public static function set($key='',$value='')
	{
		self::singleton()->save($value,$key);
	}
	
	public static function flush()
	{
		self::singleton()->clean(Zend_Cache::CLEANING_MODE_ALL);
	}
	
	
	
	
	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	public function __wakeup()
	{
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
}


?>