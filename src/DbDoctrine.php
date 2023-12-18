<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Db.php 438 2011-12-20 13:04:07Z beimuaihui@gmail.com $
 */
namespace Baogg;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\Profiler\Profiler;

class DbDoctrine {

	protected static $arr_db=array();
	protected static $key_map=array('master'=>'master','slaver'=>'slaver','qa'=>'qa','cdnlog'=>'cdnlog');

    protected static $arr_slave_db=array();
    protected static $index_slave_db=-1;
    protected static $enable_slave = false;
    protected static $trans_level = 0;
	//this can't be construct outside,single pattern
	protected function __construct() {
	
	}
	
	public static function getDb($key,$debug=false):\Doctrine\DBAL\Connection {
		$key=isset(self::$key_map[$key])?self::$key_map[$key]:strtolower($key);
		if (! array_key_exists ( $key,self::$arr_db )) {


            $c  = \Baogg\APP::getSettings();
            $db = $c['settings']['db'][$key];


			self::$arr_db[$key] = \Doctrine\DBAL\DriverManager::getConnection($db); //

			if(false && $debug){
			    //$logger = \Baogg\App::getInstance()->getContainer()->logger;
			    //echo __FILE__.__LINE__.'<pre>';var_dump($logger);exit;
                //self::$arr_db[$key]->setProfiler(new Profiler(\Baogg\App::getInstance()->getContainer()['logger']));
                //self::$arr_db[$key]->getProfiler()->setActive(true);
            }
		
		}
		//self::$_slaveDb->query("set names ".BAOGG_{$key}_CHARSET);
		return self::$arr_db[$key];
		
		
	}

    public static function getSlaveDb($key):\Doctrine\DBAL\Connection{
        $key=isset(self::$key_map[$key])?self::$key_map[$key]:strtolower($key);

        self::getDb($key);

        if(! array_key_exists ( $key,self::$arr_slave_db )){
            $c  = \Baogg\File::getSetting();
            $db = $c['settings']['db'][$key];

            try{
                $arr_slaves = \Baogg\File::getSetting('settings.db.SLAVES');
                if(!$arr_slaves){
                    throw new \Exception("mysql settings slave no config yet!");
                }
                if(self::$index_slave_db<0){
                    $index_slave_db = array_rand($arr_slaves);
                }
                $db['host'] =  $arr_slaves[$index_slave_db];

                self::$arr_slave_db[$key] =  new ExtendedPdo(
                    $db);

                $row_status = self::$arr_slave_db[$key]->fetchAssoc("SHOW SLAVE STATUS");

                //error_log(__FILE__.__LINE__." mysql slave  status=".var_export($row_status,true));

                if(!$row_status || $row_status['Slave_IO_Running'] == 'No' || $row_status['Slave_SQL_Running'] == 'No') {
                    unset(self::$arr_slave_db[$key]);
                }

            }catch (\PDOException $e) {
                error_log(__FILE__.__LINE__." {$db['driver']}.':host='.{$row_slave}.';dbname='. {$db['database']}  mysql slave pdo error!".$e->getMessage());
                self::$arr_slave_db[$key] = & self::$arr_db[$key];
            } catch (\Exception $e) {
                //error_log(__FILE__.__LINE__." mysql slave  error!");
                self::$arr_slave_db[$key] = & self::$arr_db[$key];
            } finally {
                if(! self::$arr_slave_db[$key] ){
                    error_log(__FILE__.__LINE__." mysql slave  empty!");

                    self::$arr_slave_db[$key] = & self::$arr_db[$key];
                }
            }


        }
        //self::$_slaveDb->query("set names ".BAOGG_{$key}_CHARSET);
        return self::$arr_slave_db[$key];


    }

    public static function enableSlave($status=false){
        self::$enable_slave = $status;
        return self;
    }
    public static function getEnableSlaveStatus(){
        return self::$enable_slave;
    }

    public static function getTransLevel(){
        return self::$trans_level;
    }
    public static function increTransLevel(){
        self::$trans_level ++;
    }
    public static function DecreTransLevel(){
        self::$trans_level --;
    }
	public static function getTablePrefix($key){		
		$key=isset(self::$key_map[$key])?self::$key_map[$key]:strtolower($key);

        $c  = \Baogg\APP::getSettings();

		return $c['settings']['db'][$key]['prefix'];
	}
	public static function getDbDriver($key){
        $key=isset(self::$key_map[$key])?self::$key_map[$key]:strtolower($key);

        $c  = \Baogg\APP::getSettings();
       /* if($key == 'baogg' || !$key){
            echo __FILE__.__LINE__.'<pre>';var_dump($c['settings']['db'][$key]['driver']);exit;
        }*/

        return $c['settings']['db'][$key]['driver'];
    }
	public function __clone() {
		trigger_error ( 'Clone is not allowed.', E_USER_ERROR );
	}
	/**
	 * @author :bob
	 * @abstract : get the sql ,use as echo debugDb();
	 *
	 * @return the sql string.
	 
	public  static function debugDb( $db,$type = '') {
		if ($type == 'log') {
			$spacer = "";
		} else {
			$ret = "<pre>";
			$spacer = "<br />-- -------------------------------------------------------<br />";
		}
		if (! $db) {
			return "No Database Connect!";
		}
		
		$ret .= $spacer;

		

		foreach ( ( array ) $db->getProfiler ()->getQueryProfiles ( Zend_Db_Profiler::SELECT | Zend_Db_Profiler::INSERT | Zend_Db_Profiler::UPDATE | Zend_Db_Profiler::DELETE | Zend_Db_Profiler::QUERY, true ) as $query ) {
			if(!$query){
				continue;
			}
			

			if ($query->getQueryType () == Zend_Db_Profiler::INSERT || $query->getQueryType () == Zend_Db_Profiler::UPDATE) {
				$sql = str_replace ( "?", "'%s'", $query->getQuery () );
				
				$params = array_map ( "mysql_escape_string", $query->getQueryParams () );
				
				$ret .= $params ? (vsprintf ( $sql, $params )) : $sql;
				$ret .= ";" . $spacer;
			} else {
				$ret .= $query->getQuery () . ";" . $spacer;
			}
		}

		return $ret;
	}
	*/

	public static function filterColumn(& $tbl,$post,& $db){
		//$arrColumn=$db->describeTable($tbl);
		$sm = $db->getSchemaManager();
    	$arrColumn = (array)$sm->listTableColumns($tbl);
		//echo "<pre>";print_r($arrColumn);
		foreach((array)$post as $k=>$v){
			if(!array_key_exists($k,$arrColumn)){
				unset($post[$k]);
			}
		}
		return $post;
	}
	
	
	public static function syncTable($table,$master_field, $slaver_field=NULL) {
		$ret=array();
		
		if (! $master_field || ! is_array ( $master_field )) {
	
			return array();
		}
		
		if (! $slaver_field || ! is_array ( $slaver_field )) {
			//table does not exists, create
			$q = "CREATE TABLE  IF NOT EXISTS  `$table`(";
			
			foreach ( $master_field as $field ) {
				if ($field['Key'] == 'PRI')
					$primary = " PRIMARY KEY ";
				else
					$primary = '';
				
				//$primary=($field['key'] == 'PRI' ? ", ADD PRIMARY KEY (`{$field['Field']}`)" : '')	
				
			$column_def = "";
			$column_def .=($field['Null']=='NO' ? ' NOT NULL ' : ' NULL ') ;			
			$column_def .= (strlen($field['Default']) > 0 ? " default '{$field['default']}' " : '');
			$column_def .= ($field['Extra'] == 'auto_increment' ? ' auto_increment ' : '');
			$column_def .= 	$field ['Key'] == 'PRI'?' PRIMARY KEY ':'';
			$q .= "`{$field['Field']}` {$field['Type']} " .$column_def;
			
			if ($field != end ( $master_field ))
				$q .= ", ";
			}
			
			$q .= ") DEFAULT CHARSET=utf8mb4";
			return array($q);
		}
		
		
		//table exists, check fields
		foreach ( $master_field as $f => $field ) {
			$dfield = @$slaver_field [$f];
			$ffound = isset ( $dfield );
			
			if ($ffound) {
				
				//|| $field ['Null'] != $dfield ['Null'] || $field ['Default'] != $dfield ['Default'] || $field ['Extra'] != $dfield ['Extra']
				if ($field ['Type'] != $dfield ['Type']  || $field ['Key'] != $dfield ['Key'] ) 
				{
					$column_def = "";
					if ($field ['Null'] != $dfield ['Null']) {
						$column_def .= $field ['Null'] == 'NO' ? " NOT NULL " : ' NULL ';
					}
					
					if ($field ['Default'] != $dfield ['Default'] && $field ['Default']) {
						$column_def .= " default '{$field['Default']}' ";
					}
					
					if ($field ['Extra'] != $dfield ['Extra'] && ($field['Extra']=='auto_increment' || $field['Extra']='')) {
						$column_def .= " {$field['Extra']} ";
					}
					
					if ($field ['Key'] != $dfield ['Key'] && $field ['Key'] == 'PRI') {
						$column_def .= " PRIMARY KEY ";
					}
					
					//ALTER TABLE  `mail_log` CHANGE  `to_name`  `to_name` VARCHAR( 62 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL
					$q = "ALTER TABLE `$table` CHANGE  `{$field['Field']}` `{$field['Field']}` {$field['Type']} $column_def";
					//$db->q($q);
					$ret [] = $q;
				
				}
			} else {
				//alter table add field
				if ($field ['Key'] == 'PRI')
					$primary = " PRIMARY KEY ";
				else
					$primary = '';
				
				if ($field ['Null'] == 'NO'){
					$isNull = " NOT NULL ";
				}else{
					$isNull = ' NULL ';
				}
				
				if ($field ['Default']) {
					$default = " default '{$field['Default']}' ";
				}
				
				
					
				//ALTER TABLE  `mail_log` CHANGE  `to_name`  `to_name` VARCHAR( 62 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL
				$q = "ALTER TABLE `$table` ADD `{$field['Field']}` {$field['Type']}  $isNull $default {$field['Extra']} $primary";//
				//$db->q($q);
				$ret [] = $q;
			}
			
		}
		return $ret;
	
	}



	/*
	function AddTableField($table, $field, $field_before = 0) {
		$sql = "ALTER TABLE `{$table}` ADD `{$field['name']}` {$field['type']} " . ($field['null'] ? '' : 'NOT') . ' NULL' . (strlen($field['default']) > 0 ? " default '{$field['default']}'" : '') . ($field['extra'] == 'auto_increment' ? ' auto_increment' : '') . (!is_string($field_before) ? ' FIRST' : " AFTER `{$field_before}`") . ($field['key'] == 'PRI' ? ", ADD PRIMARY KEY (`{$field['name']}`)" : '');
          return mysql_query($sql, $this->dbp);
    }
   function ChangeTableField($table, $field, $new_field) {
   	$sql = "ALTER TABLE `{$table}` CHANGE `{$field}` `{$new_field['name']}` {$new_field['type']} " . ($new_field['null'] ? '' : 'NOT') . ' NULL' . (strlen($new_field['default']) > 0 ? " default '{$new_field['default']}'" : '') . ($field['extra'] == 'auto_increment' ? ' auto_increment' : '') . ($field['key'] == 'PRI' ? ", ADD PRIMARY KEY (`{$field['name']}`)" : '');
     return mysql_query($sql, $this->dbp);
  }*/
}

?>