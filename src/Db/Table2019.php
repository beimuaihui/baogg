<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Table.php 457 2012-01-12 07:44:47Z beimuaihui@gmail.com $
 */
/**
 * @author 
 * @version
 */



//class Permission extends Zend_Db_Table_Abstract {
namespace Baogg\Db;

use Aura\SqlQuery\QueryFactory;

class Table2019   {
	/**
	 * The default table name 
	 */
	protected $_name;
	protected $_db ;
	protected $_primary;
    protected $db_prefix;
    protected $_db_driver;
    protected $_enable_slave = null;
    const COLS = 'table_columns';
    public static $_columns = array();

	
	function __construct($db_key='master',$table_name='',$pk='') {	   
	    $this->_db=\Baogg\Db::getDb($db_key);
        $this->_slave_db = \Baogg\Db::getSlaveDb($db_key);
        $this->db_prefix=\Baogg\Db::getTablePrefix($db_key);
        $this->_db_driver=\Baogg\Db::getDbDriver($db_key);
        if(!$this->_db_driver){
            echo __FILE__.__LINE__.'<pre>';var_dump($this->_db_driver);print_r(get_defined_vars());exit;
        }
        if($table_name){
        	$this->_name=$this->db_prefix && strpos($table_name,$this->db_prefix)===0?$table_name:$this->db_prefix.$table_name;	
        }else{
        	$this->_name=$this->db_prefix && strpos($this->_name,$this->db_prefix)===0?$this->_name:$this->db_prefix.$this->_name;
        }
        /*
        $db= new Zend_Db_Adapter_Pdo_Mysql(array(
            'host'     => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname'   => 'baogg',
        	'driver_options' => array(
        			PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8'
        		)
        ));
        */
        
        //just for debug
        //$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        //$profiler->setEnabled(true);
        //$this->_db->setProfiler($profiler); 
        
        
        /*$writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);
        //global $logger;$logger->log('This is a log message!', Zend_Log::INFO);
        */
       

		//$this->_db=  Zend_Registry::get("db");

        if($pk){
        	$this->_primary=$pk;		
        }else{
			$this->_primary=$this->_primary;	
		}

		/*if(! BAOGG_DEBUG &&    ! BAOGG_READONLY){
			// First, set up the Cache
			$frontendOptions = array(
			    'automatic_serialization' => true
			    );
			$dir_cache =  BAOGG_UPLOAD_DIR.'cache/';
			is_dir($dir_cache) or Baogg_File::mkdir($dir_cache);
			$backendOptions  = array(
			    'cache_dir'                => $dir_cache
			    );
			 
			$cache = Zend_Cache::factory('Core',
			                             'File',
			                             $frontendOptions,
			                             $backendOptions);

			parent::__construct(array('metadataCache' => $cache));
		}else{
			parent::__construct();
		}*/
	}

    public function setEnableSlaveStatus($status=false){
        $this->_enable_slave = $status;
        return $this;
    }

	public function changeTableName($new_name){
		$this->setOptions(array(self::NAME=>$new_name));
		self::__construct();
	}
	
	/*
	 *   get  data list
	 *  @todo $where add __bind key ,for binding params
	 */
	function getList($where=array(),$order=array(),$limit=array(),$cols="*",$joins=array(),$group=array(),$having=array(),$is_distinct=false) {
        if(!$this->_db_driver){
            echo __FILE__.__LINE__.'<pre>';var_dump($this->_db_driver);print_r(get_defined_vars());exit;
        }
        try {
            $select = (new QueryFactory($this->_db_driver))->newSelect();
        }catch (Exception $e){
            echo __FILE__.__LINE__.'<pre>';var_dump($this->_db_driver);exit;
        }

        /*$select->cols([
            'user_id'
        ])->from($this->db_prefix.'users')
            ->where('user_id=:user_id',array("user_id"=> $id));


        $sql="SELECT user_id FROM tx_users where user_id=:user_id";
        $bind = array("user_id"=> $id);*/
        //return  $this->_db->fetchAll($select->getStatement(),$select->getBindValues());
        if(is_string($cols)){
            $cols=[$cols];
        }

		if($is_distinct){
			$select->distinct();
		}
		$select->cols($cols)->from($this->_name.' as '.$this->getAlias());

		foreach((array)$joins as $v){
			//echo '<pre>';print_r($v);
			if(!is_array($v['name'])){
				$v['name']=$this->db_prefix.$v['name'].' as '.$v['name'];
			}else{
				foreach($v['name'] as $alias=>$model){
					$v['name']=$this->db_prefix.$model.' as '.$alias;
				}
			}
			$join_type=isset($v['type'])?strtoupper($v['type']):'INNER';
			$select->join($join_type,$v['name'],$v['condition']);
			if($v['cols']){
			    if(is_array($v['cols'])){
			        $cols = array_merge($cols,$v['cols']);
                }else{
                    $cols .=','.$v['cols'];
                }
            }
		}

		$cols=$this->info(self::COLS);

		$arr_meta = $this->info();

		$arr_where = $this->buildWhere($where,$cols,'and','');
		$select->where($arr_where['where']);
		if($arr_where['bind'] && is_array($arr_where['bind'])){
			foreach($arr_where['bind'] as $k_where_bind=>$v_where_bind){
				$select->bindValue($k_where_bind,$v_where_bind);
			}
		}
		/*
		$arr_where_or=array();
        $arr_where_or_bind=array();

        $tmp_i_where = 0;
	    foreach((array)$where as $k=>$v){
            $tmp_i_where++;
	    	if(is_int($k)){
	    		//if $v is not ""
	    		if($v){
	    			$select->where($v);
	    		}
	    		continue;
	    	}

	    	$k=trim($k);
	    	$arr_k=explode(" ",$k);
	    	if(!in_array($arr_k[0],$cols) && $this->is_word($arr_k[0])){
	    		continue;
	    	}


			if(!$this->is_word($k)){ //such as col >= ':date',replace ? to  char(63)
				if($this->is_word($arr_k[0])){
					$select->where($this->getAlias() . '.' . $k . " :" . $arr_k[0].'__'.$tmp_i_where)->bindValue( $arr_k[0].'__'.$tmp_i_where , $v);
				}else{
					$select->where($k.' = :col__'.$tmp_i_where)->bindValue('col__'.$tmp_i_where,$v);
				}
            } else if (is_int($v) || is_float($v) || is_bool($v) ) {
                $select->where("{$this->getAlias()}.$k =  :" . $k)->bindValue($k, $v);
	        }else if(is_array($v)){
                $tmp_arr_bind_values = array();
                foreach($v as $sub_k=>$sub_v){
                    $tmp_arr_bind_values[$k.'__arr'.$sub_k] = $sub_v;
                }
                $select->where("{$this->getAlias()}.$k  IN (:" . implode(',:',array_keys($tmp_arr_bind_values)) . ")")->bindValues($tmp_arr_bind_values);
	        }else{
	           if($v==="%%"){
	           		continue;
	           }else if($this->is_query_value(trim($v,'%'))){
	           		$arr_where_or[]="{$this->getAlias()}.$k like :".$k;
	           		$arr_where_or_bind[]=array($k=>'%'.$this->get_query_value(trim($v,'%')).'%');
	           }else{
	           		if(isset($arr_meta[$k]) && in_array(strtolower($arr_meta[$k]['Type']),array('int','tinyint','bigint'))){
	           			$v = trim($v,'%');
	           		}
                    $select->where("{$this->getAlias()}.$k like  :" . $k)->bindValue($k , $v);
	           }
	        }
	    }

		if($arr_where_or){
	    	$select->where('('.implode(" or ",$arr_where_or).')',$arr_where_or_bind);
		}
		*/

	    if($order){
	    	$_arr_sort=array();
	    	foreach((array)$order as $k=>$v){
		    	if(is_int($k)){
		    		//$select->order($order);
		    		$_arr_sort[]=$v;
		    	}else if($k=='sort'){
		    		if(($json_order=json_decode($v,true))!==null){
		    			foreach((array)$json_order as $k=>$v){
		    				$v['property']=$this->filterColumn($v['property']);
		    				$_arr_sort[]="{$v['property']} {$v['direction']}";
		    			}
		    		}else{
				    	$sort=$this->filterColumn($order['sort']);
				    	if($sort){
					    	$dir=isset($order['dir']) && in_array(strtolower($order['dir']),array('asc','desc'))?$order['dir']:'asc';
					        $_arr_sort[]="$sort $dir" ;
				    	}
		    		}
		    	}else if($k=='dir'){

		    	}else{
		    		//just for 'array(col=>dir)'
		    		$sort=$this->filterColumn($k);
			    	if($sort){
				    	$dir= in_array(strtolower($v),array('asc','desc'))?$v:'asc';
				        $_arr_sort[]="$sort $dir" ;
			    	}
		    	}
	    	}
	    	$select->orderBy($_arr_sort) ;
	    }
		if($limit){
	        if(is_array($limit)){
                $select->limit((int)$limit['limit']) ;
                if(isset($limit['start']) && $limit['start']){
                    $select->offset((int)$limit['start']);
                }
            }else{
                $select->limit((int)$limit);
            }
        }

		if($group){
			$select->groupBy($group);
		}
		if($having){
			$select->having($having);
		}
		try{
//	        if($this->_name == 'baogg_menu' && $cols!=["count(*) as cnt"]){
//		        echo __FILE__.__LINE__.'<pre>';var_dump($select->getStatement());var_dump($select->getBindValues());
//            }
/*
$enable_slave = false;
	        if(\Baogg\Db::getTransLevel()!=0){
                $enable_slave = false;
            }else if($this->_enable_slave === true){
                $enable_slave = true;
            }else if(\Baogg\DbPlus::getEnableSlaveStatus() === true){
                $enable_slave = true;
            }

	        //error_log(__FILE__.__LINE__." enable_slave={$enable_slave}");
            //error_log(__FILE__.__LINE__." slave_db=".var_export($this->_slave_db,true));

	        if($enable_slave){
                $rs = $this->_slave_db->fetchAll($sql, $bind);
            }else{
                $rs = $this->_db->fetchAll($sql, $bind);
			}
			*/
            error_log(__FILE__.__LINE__." \n statment=".$select->getStatement().";bindvalues=".var_export($select->getBindValues(),true));
            $stm = $this->_db->prepare($select->getStatement());
            $stm->execute($select->getBindValues());
			$rs=$stm->fetchAll(\PDO::FETCH_ASSOC);
		}catch(PDOException $pe){
            echo __FILE__.__LINE__.'<pre>';var_dump($select->getStatement());var_dump($select->getBindValues());
            echo '<br /><pre>'.__FILE__.__LINE__;
            echo '<br />'.$select->getStatement();
            echo '<br />'.$e->getMessage();
            echo '<br />';
            print_r((array)$where );
            debug_print_backtrace();
            exit;
        }catch(Exception $e){
            echo __FILE__.__LINE__.'<pre>';var_dump($select->getStatement());var_dump($select->getBindValues());
			echo '<br /><pre>'.__FILE__.__LINE__;
			echo '<br />'.$select->getStatement();
			echo '<br />'.$e->getMessage();
			echo '<br />';
			print_r((array)$where );
			debug_print_backtrace();
			exit;
		} finally {
//            echo __FILE__.__LINE__.'<pre>';var_dump($select->getStatement());var_dump($select->getBindValues());
//            echo '<br /><pre>'.__FILE__.__LINE__;
//            echo '<br />'.$select->getStatement();
//            echo '<br />'.$e->getMessage();
//            echo '<br />';
//            print_r((array)$where );
//            debug_print_backtrace();

        }
		/* if($group){
			
		} */
	  // $rs[0]['sub_table']='gg';
	   
	   return $rs;	
	}


    /**
     * @param $where
     * @param array $cols
     * @param string $op 'and' or 'or'
     * @param string $col_suffix
     * @return array
     * @testing
     * $where = array('id' => 1,
     *           'col2' => 'val2',
     *          '3=3',
     *           "find_in_set(col4,'1,2,3,4') >0",
     *           'col5 >=' => 5,
     *           'or' => array(
     *               'col61' => 'val1',
     *               'col2' => 'val62',
     *               ' and '=>array('col7'=>'val7','col8'=>'val8'),
     *               '  and  '=>array('col9'=>'val9','col10'=>'val10'),
     *               )
     *           );
	 * $cols = array('id', 'col2', 'col1', 'col5', 'col61','col7','col8','col9','col10');
	 * if multi op,such as multi 'and',then use ' and ','  and  '
     */
    function buildWhere($where,$cols=array(),$op='and',$col_suffix = ''){

        $arr_where = array();
        $arr_where_bind = array();

        $tmp_i_where = 0;
        foreach ((array)$where as $k => $v) {
            $tmp_i_where++;
            $current_col_suffix = $col_suffix?$col_suffix.'__'.$tmp_i_where:$tmp_i_where;

            if (is_int($k)) {
                //if $v is not ""
                if ($v) {
                    $arr_where[]= $v;
                }
                if ($v && is_string($v)) {
                    $arr_where[]= $v;
                }else if($v && is_array($v)){
                    foreach($v as $k_sub_where=>$v_sub_bind){
                        //$select->where($k_sub_where);
                        $arr_where[]= $k_sub_where;
                        foreach((array)$v_sub_bind as $k_v_sub_bind=>$v_v_sub_bind){
                            $arr_where_bind[$k_v_sub_bind]=$v_v_sub_bind;
                        }
                    }
                }
                continue;
            }

            $k = trim($k);
            $arr_k = explode(" ", $k);
            if (!in_array($k,array('and','or')) && !in_array($arr_k[0], $cols) && $this->is_word($arr_k[0])) {
                continue;
            }


            if (!$this->is_word($k)) { //such as col >= ':date',replace ? to  char(63)
                if($this->is_word($arr_k[0])){
                    $arr_where[] =  $k . " :" . $arr_k[0].'__'.$current_col_suffix;
                    $arr_where_bind[$arr_k[0].'__'.$current_col_suffix] =   $v;
                    //$select->where(getAlias() . '.' . $k . " :" . $arr_k[0].'__'.$tmp_i_where)->bindValue( $arr_k[0].'__'.$tmp_i_where , $v);
                }else{
                    $arr_where[] =  $k.' = :col__'.$current_col_suffix;
                    $arr_where_bind['col__'.$current_col_suffix] =   $v;
                    //$select->where($k.' = :col__'.$tmp_i_where)->bindValue('col__'.$tmp_i_where,$v);
                }
            } else if (is_int($v) || is_float($v) || is_bool($v) ) {
                $arr_where[] = $this->getAlias().".$k =  :" . $k.'__'.$current_col_suffix;
                $arr_where_bind[$k.'__'.$current_col_suffix] =   $v;
            } else if (!in_array($k,array('and','or')) && is_array($v)) {
                $tmp_arr_bind_values = array();
                foreach($v as $sub_k=>$sub_v){
                    $tmp_arr_bind_values[$k.'__arr'.$sub_k.'__'.$current_col_suffix] = $sub_v;
                }
                $arr_where_bind[] = $tmp_arr_bind_values;
                $arr_where[] = $this->getAlias().".$k  IN (:" . implode(',:',array_keys($tmp_arr_bind_values)) . ")";
                //$select->where("{getAlias()}.$k  IN (:" . implode(',:',array_keys($tmp_arr_bind_values)) . ")")->bindValues($tmp_arr_bind_values);
            } else {
                /*if($k == 'user_group_id'){
                    echo __FILE__.__LINE__.'<pre>';
                    print_r($arr_meta);
                    var_dump(strtolower($arr_meta['metadata'][$k]['DATA_TYPE']));
                    exit;
                }*/
                //error_log(__FILE__.__LINE__." k= {$k}");
                if (in_array($k,array('and','or'))) {
                    //$params = array($v,$cols,$k,$current_col_suffix);
                    //error_log(__FILE__.__LINE__." sub=".var_export($params,true));
                    $arr_sub_where = $this->buildWhere($v,$cols,$k,$current_col_suffix);
                    //error_log(__FILE__.__LINE__." sub=".var_export($arr_sub_where,true));
                    $arr_where[] = $arr_sub_where['where'];
                    $arr_where_bind = array_merge($arr_where_bind,$arr_sub_where['bind']);
                    //$arr_where_or[] = "{getAlias()}.$k like :" . $k;
                    //$arr_where_or_bind[] = array($k => '%' . get_query_value(trim($v, '%')) . '%');
                } else {
                    $arr_where[] =$this->getAlias().".$k = :" . $k.'__'.$current_col_suffix;
                    $arr_where_bind[ $k.'__'.$current_col_suffix] =$v;
                    //$select->where("{getAlias()}.$k = :" . $k)->bindValue($k , $v);
                }
            }
        }
        return array('where'=>'('.implode(' '.$op.' ',$arr_where).')','bind'=>$arr_where_bind);
	}
	
	function info($filter=''){
	    if(!isset(self::$_columns[$this->_name]) || self::$_columns[$this->_name]){
            self::$_columns[$this->_name] = $this->_db->fetchAll(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($this->_name)));
        }

        //echo __FILE__.__LINE__.'<pre>';print_r(self::$_columns);exit;
        $rows = self::$_columns[$this->_name];
        $arr = array();
        foreach($rows as $row){
            $arr[$row['Field']] = $row;
        }
        if($filter == self::COLS){
            return array_keys($arr);
        }else{
            return $arr;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function quoteTableName($tableName)
    {
        return str_replace('.', '`.`', $this->quoteColumnName($tableName));
    }

    /**
     * {@inheritdoc}
     */
    public function quoteColumnName($columnName)
    {
        return '`' . str_replace('`', '``', $columnName) . '`';
    }

	/**
	 * 
	 * get list with tree style
	 * @param array $where
	 * @param array("sort"=>"","dir"=>"") $order
	 * @param array $limit
	 * @param cols array $cols
	 * @param array $joins
	 */
	function getListTree($where=array(),$order=array(),$limit=array(),$cols="*",$joins=array()){
		//$rs=$this->getList($where,$order,$limit,$cols,$joins);

		$rs=$this->getArray($where,$order,$limit,$cols,$joins);

		/*if($this->_primary=="position_id"){
            echo __FILE__.__LINE__.'<pre>';print_r($rs);print_r($where);print_r($cols);exit;
        }*/

		if(!$rs){
			return array();
		}
		$columns=@array_keys((array)current($rs));
		foreach((array)$rs as $k=>$v){
			if(isset($columns[2]) && !isset($rs[$v[$columns[2]]])){
				$rs[$k][$columns[2]]=0;
			}
		}
		$rs=array_values($rs);




		//echo '<br /><pre>'.__FILE__.__LINE__;print_r($rs);

		$dbTree=new Baogg_Db_Tree();
		$tr=$dbTree->rs2GridTree($rs);
		$rs=$dbTree->tr2GridStore($tr);

		//print_r($rs);exit;

		return $rs;
	}
	function getArray($where=array(),$order=array(),$limit=array(),$cols="*") {
		$rs=$this->getList($where,$order,$limit,$cols);
		$arr=array();
		foreach((array)$rs as $k=>$v){
			//except value field is not primary key,
			$key_col=$this->_primary;
			if(!array_key_exists($this->_primary, $v)){
				$arr_cols=array_keys($v);
				$key_col=$arr_cols[0];
			}
			$arr[$v[$key_col]]=$v;
		}
		return $arr;
	}
	function getCombo($where=array(),$order=array(),$limit=array(),$cols="*",$joins=array()) {
		$rs=$this->getList($where,$order,$limit,$cols,$joins);
		$arr=array();
		foreach((array)$rs as $k=>$v){
			if(!isset($arr_key)){
				$arr_key=array_keys($v);				
			}
			$arr[]=array($v[$arr_key[0]],$v[$arr_key[1]]);
		}
		return $arr;
	}
	
	function getTree($where=array(),$order=array(),$limit=array(),$cols="*") {
		$rs=$this->getArray($where,$order,$limit,$cols);
		
		
		foreach((array)$rs as $k=>$v){
			if(!isset($pid_column)){
				$arr_key=array_keys($v);
				$pid_column=$arr_key[2];
			}			
			if(!isset($rs[$v[$pid_column]])){
				$rs[$k][$pid_column]=0;
			}
		}
		$Tree=new Baogg_Db_Tree();
		$tr=$Tree->rs2GridTree(array_values($rs));
		$tr=$Tree->tr2GridStore($tr);
		return $tr;
	}
	function getExtTree($where=array(),$order=array(),$limit=array(),$cols="*",$joins=array(),$rs_op=array(),$arr_selected=array()) {
		$rs_tmp=$this->getList($where,$order,$limit,$cols,$joins);
		//echo '<pre>';print_r($rs_op);print_r($where);print_r($order);print_r($limit);print_r($cols);print_r($joins);		print_r($arr_selected);exit;
		//echo __FILE__.__LINE__.'<pre>';print_r($rs_tmp); print_r($rs_op);exit;
		$rs=array();
	  	foreach($rs_tmp as $k=>$v){	  		
	  		!empty($v['pid']) or $v['pid']=0;
	  		if(in_array($v['id'],(array)$arr_selected)){
	  			$v['checked']=true;
	  		}
	  		if(!isset($v['checked'])){
	  			$v['checked']=false;
	  		}
	  		//echo '<pre>';print_r($v);exit;
	  		$rs[$v['id']]=$v;
	  		foreach((array)$rs_op as $row_op){
	  			if(isset($row_op['id']) &&  in_array($row_op['id'],(array)$arr_selected)){
	  				$row_op['checked']=true;
	  			}
	  			$row_op['id']= isset($row_op['id'])?$row_op['id']: $v['id'].'_'.$row_op['id2'];
	  			$row_op['pid']=isset($row_op['pid'])?$row_op['pid']:$v['id'];
	  			$rs[$row_op['id']]=$row_op;
	  		}
	  	}
	  	
		foreach((array)$rs as $k=>$v){
			if(!isset($pid_column)){				
				$arr_key=array_keys($v);
				$pid_column=count($arr_key)>=3?$arr_key[2]:'pid';
			}
			//echo $pid_column;exit;
			if(!isset($v[$pid_column]) || !isset($rs[$v[$pid_column]])){
				$rs[$k][$pid_column]=0;
			}
		}
		
		$Tree=new Baogg_Db_Tree();
		$tr=$Tree->rs2GridTree(array_values($rs));
		$tr=$Tree->tr2ExtTreeStore($tr);
		//echo '<pre>';var_dump($tr);exit;
		return $tr;
		
	}
	function getComboTree($where=array(),$order=array(),$limit=array(),$cols="*") {
		$rs=$this->getArray($where,$order,$limit,$cols);
		
		
		foreach((array)$rs as $k=>$v){
			if(!isset($pid_column)){
				$arr_key=array_keys($v);
				$pid_column=$arr_key[2];
			}			
			if(!isset($rs[$v[$pid_column]])){
				$rs[$k][$pid_column]=0;
			}
		}
		$Tree=new Baogg_Db_Tree();
		$tr=$Tree->rs2ComboTree(array_values($rs));
		$tr=$Tree->tr2ComboStore($tr);
		return $tr;
	}
	/*
	 * Get All by state
	 */	
	function getAllByState() {
		$sql = 'SELECT * FROM ' . $this->_name . ' WHERE state = 1 ';
		$rs = $this->_db->query($sql)->fetchAll();
		return $rs; 	
	}

	/*
	 * Get language list unless a language . defaut is unless english
	 */
	function getAllUnlessLang($id=1) {
		$sql = "SELECT * FROM {$this->_name} WHERE state = 1  and {$this->_primary}<>{$id}";
		//echo $sql;
		//exit();
		$rs = $this->_db->query($sql)->fetchAll();
		return $rs; 	
	}
	
	function _update($arr, $id='') {

		$rows_affected  = 0;

		//echo __FILE__.__LINE__.'<pre>';		print_r($arr);
		//filter col
		$arr=$this->filterForm($arr);		
    		$id= ($id===''?$arr[$this->_primary]:$id);
    		
		$where = is_array($id)? "{$this->_primary} in (".$this->_db->quote(array_filter($id,'is_numeric')).")" : "{$this->_primary} = ".$this->_db->quote( (int)$id);
		$rs = $this->getList($where);
		//filter col val
		foreach((array)$rs as $v){
			//need to update data
			$arr_update  = array();
			foreach($arr as  $col=>$val){
				if($val  != $v[$col]){
					$arr_update[$col] = $val;
				}
			}
			if($arr_update){
				try{
                    $update = (new QueryFactory($this->_db_driver))->newUpdate();
                    $update->table($this->_name)->cols($arr_update)->where($where);
					$rows_affected = $this->_db->prepare($update->getStatement())->execute($update->getBindValues());
					/*f($rows_affected){
						$ModelHistory = new ModelHistory();
						$ModelHistory->create($this,__METHOD__,$arr_update);
					}*/
				}catch(Exception $e){
					echo __FILE__.__LINE__.'<pre>'.$e->getMessage();
					debug_print_backtrace();
					var_dump( $this->_primary);
					var_dump( $this->_name);
					var_dump($arr);
					var_dump($where);
					exit;
				}
			}
		}


		//echo  __FILE__.__LINE__.'<pre>';var_dump($this->_name);		print_r($arr);		echo $where;print_r($id);		exit;
		/*try{
			$rows_affected = $this->_db->update($this->_name, $arr, $where);

			//echo  __FILE__.__LINE__.'<pre>';var_dump($rows_affected);exit;
			if($rows_affected){
				$ModelHistory = new ModelHistory();
				foreach((array)$id as $sub_id){
					$rs = $this->getOne($sub_id);
					if($rs){
						$ModelHistory->create($this,__METHOD__,$rs[0] );
					}
				}
			}
			
			
			
		}catch(Exception $e){
			echo __FILE__.__LINE__.'<pre>'.$e->getMessage();
			debug_print_backtrace();
			var_dump( $this->_primary);
			var_dump( $this->_name);
			var_dump($arr);
			var_dump($where);
			exit;
		}*/
		
		
		return $rows_affected;
	}
	
	function _insert($arr) {
		unset($arr[$this->_primary]);
		$arr=$this->filterForm($arr);

        $insert = (new QueryFactory($this->_db_driver))->newInsert();
        $insert->into($this->_name)->cols($arr);
        try{
            $rows_affected = $this->_db->prepare($insert->getStatement())->execute($insert->getBindValues());
        }catch (\Exception $e){
            //echo __FILE__.__LINE__.'<pre>';var_dump($arr);exit;
            return false;
        }
		$last_insert_id = $this->_db->lastInsertId();

		/*$rs = $this->getOne($last_insert_id);
		if($rs){
			$ModelHistory = new ModelHistory();
			$ModelHistory->create($this,__METHOD__,$rs[0] );
		}*/
		
	
		return $last_insert_id;
	} 

	function _delete($id)
	{	

		/*$ModelHistory = new ModelHistory();
		foreach((array)$id as $sub_id){
			$rs = $this->getOne($sub_id);
			if($rs){
				$ModelHistory->create($this,__METHOD__,$rs[0] );
			}
		}*/

	    $arr_id=is_array($id)?array_filter($id,'is_numeric'):array_filter(explode(",",$id),'is_numeric');
		$where = "{$this->_primary} in (".$this->_db->quote($arr_id).")";
		//echo __FILE__.__LINE__.'<pre>';var_dump(array_filter(explode(",",$id),'is_numeric'));exit;

        $delete = (new QueryFactory($this->_db_driver))->newDelete();
        $delete->from($this->_name)->where($where);

		//echo $where;
		$rows_affected = $this->_db->prepare($delete->getStatement())->execute($delete->getBindValues());
		return $rows_affected;
	}


    function _inserts($arrs) {
        $insert = (new QueryFactory($this->_db_driver))->newInsert();
        $insert->into($this->_name);

        $flag_first = true;

	    foreach($arrs as $k=>$arr){
            //unset($arr[$this->_primary]);
            $arr=$this->filterForm($arr);
            if(!is_array($arr)){
                continue;
                //echo __FILE__.__LINE__.'<pre>';var_dump($arr);exit;
            }
            if(!$flag_first){
                $insert->addRow();
            }else{
                $flag_first = false;
            }
            $insert->cols($arr);
            //echo __FILE__.__LINE__.'<pre>';var_dump($arr);exit;

        }
        $rows_affected = $this->_db->prepare($insert->getStatement())->execute($insert->getBindValues());
        $last_insert_id = $this->_db->lastInsertId();
        /*$rs = $this->getOne($last_insert_id);
        if($rs){
            $ModelHistory = new ModelHistory();
            $ModelHistory->create($this,__METHOD__,$rs[0] );
        }*/


        return $last_insert_id;
    }

    function getOne($id) {
	    
		$sql = "SELECT * FROM {$this->_name} WHERE {$this->_primary} = ".$this->_db->quote( (int)$id);
		$rs = $this->_db->fetchAll($sql);
		return $rs; 	
	
	}
	

	/**
	 * return primary ids
	 * @param array,which build where $arr
	 */
	function getSame($arr) {	
		$rs = $this->getList($arr);
		foreach((array)$rs as $v)
		{
		    $ret[]=$v[$this->_primary];
		}
		return $ret; 	
	
	}
	function getSame2($arr,$op=" or "){
		$ret=array();
		
		if(!$arr){
			$arr['0']=1;
		}

		$where = array();
		foreach((array)$arr as $k=>$v)
		{
			$where[]=$this->_db->quoteInto(" $k = ?",$v);
		}
		$where=implode($op,$where);
	    $sql = "SELECT {$this->_primary} FROM {$this->_name} WHERE $where ";
		
		$rs = $this->_db->query($sql)->fetchAll();
		//echo '<pre>';print_r($sql);exit;
		foreach((array)$rs as $v)
		{
		    $ret[]=$v[$this->_primary];
		}
		
		return $ret; 	
	}
	
    function filterForm($form=array()){
        $arr_meta = $this->info();
        $cols = array_keys($arr_meta);



        //echo __FILE__.__LINE__.'<pre>';print_r($cols);print_r($form);
        //var_dump($cols);
        foreach((array)$form as $k=>$v){
            if(!in_array($k,$cols) || !$k){
                unset($form[$k]);
                continue;
            }
            // || is_null($v) || $v=== '' empty($v)
            if( is_null($v) || $v=== ''){
            	$form[$k] = $arr_meta[$k]['Default'];
            }

         /*   if($v === BAOGG_FIELD_NULL){
            	$form[$k] = NULL;
            }*/
            //added larter,change array value to string
            if(is_array($v)){
            	$form[$k]=implode(',',$v);
            }

        }
        //echo __FILE__.__LINE__.'<pre>';print_r($cols);print_r($form);exit;
        return $form;
        
    }
    
    function filterColumn($k)
    {
    	return trim(trim($k),"0123456789");
    }
    
	function is_word($str){
	    return preg_match("/^[a-zA-Z0-9_]*$/" , $str);
	}
	function is_query_value($v=''){
		return strpos($v,"#")===0 && strrpos($v,"#")===strlen($v)-1;
	}
	function get_query_value($v=''){
		return substr($v,1,-1);
	}
	function getName(){
		return $this->_name;
	}
	
	function getAlias($name=null){
		if($name===null){
			return str_replace($this->db_prefix, '', $this->_name);
		}else{
			return str_replace($this->db_prefix, '', $name);
		}
		return '';
	}
	
	public function getColCombo($where=array(),$order=array(),$limit=array(),$cols="*",$joins=array(),$group=array(),$having=array(),$is_distinct=true){
		$rs=$this->getList($where,$order,$limit,$cols,$joins,$group,$having,$is_distinct);
		$arr=array();
		foreach((array)$rs as $k=>$v){
			if(!isset($arr_key)){
				$arr_key=array_keys($v);				
			}
			$id_col=$arr_key[0];
			$text_col=isset($arr_key[1])?$arr_key[1]:$arr_key[0];
			
			$arr[]=array($v[$id_col],$v[$text_col]);
		}
		return $arr;
	}
	public function addComboAll($arr){
		array_unshift($arr,array('',Baogg_Language::get('please_select')));
		return $arr;
	}

	public static function getSubTableId($content_id=0){
        return (int)($content_id/500000);
    }



    /**
     * Quote values and place them into a piece of text with placeholders
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.
     *
     * Accepts unlimited number of parameters, one for every question mark.
     *
     * @param string $text Text containing replacements
     * @return string
     */
    public function quoteInto($text)
    {
        // get function arguments
        $args = func_get_args();
 
        // remove $text from the array
        array_shift($args);
 
        // check if the first parameter is an array and loop through that instead
       /* if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }*/
 
        // replace each question mark with the respective value
        foreach ($args as $arg) {
            $text = preg_replace('/\?{1}/', $this->_db->quote($arg), $text, 1);
        }
 
        // return processed text
        return $text;
    }

    public function _execute($sql='',$bind=array())
    {	
    	$rows_affected = $this->_db->prepare($sql)->execute($bind);
		return $rows_affected;
    }

    public function getAdapter(){
        return $this->_db;
    }
}