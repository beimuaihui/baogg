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


class TableDoctrine
{
    /**
     * The default table name
     */
    protected $_name;
    protected $_db;
    protected $_db_key;
    protected $_primary;
    protected $db_prefix;
    protected $_db_driver;
    protected $_enable_slave = null;
    const COLS = 'table_columns';
    public static $_columns = array();

    protected $TABLE_NAME;
    protected $TABLE_SCHEMA;
    protected $is_debug = false;


    function __construct($db_key = '', $table_name = '', $pk = '')
    {
        $db_key = $db_key?$db_key:$this->TABLE_SCHEMA;
        $this->_db_key = $db_key;

        $table_name = $table_name?$table_name:$this->TABLE_NAME;

        $this->_db = \Baogg\Db::getDb($db_key);
        $this->_slave_db = \Baogg\Db::getSlaveDb($db_key);

        $this->db_prefix = \Baogg\Db::getTablePrefix($db_key);
        $this->_db_driver = \Baogg\Db::getDbDriver($db_key);
        if (!$this->_db_driver) {
            echo __FILE__ . __LINE__ . '<pre>';
            var_dump($this->_db_driver);
            print_r(get_defined_vars());
            exit;
        }
        if ($table_name) {
            $this->_name = $this->db_prefix && strpos($table_name, $this->db_prefix) === 0 ? $table_name : $this->db_prefix . $table_name;
        } else {
            $this->_name = $this->db_prefix && strpos($this->_name, $this->db_prefix) === 0 ? $this->_name : $this->db_prefix . $this->_name;
        }


        if ($pk) {
            $this->_primary = $this->_primary = $pk;
        } else {
            $this->_primary = $this->_primary;
        }

    }

    public function setEnableSlaveStatus($status=false){
        $this->_enable_slave = $status;
        return $this;
    }

    public function changeTableName($new_name)
    {
        $this->setOptions(array(self::NAME => $new_name));
        self::__construct();
    }


    public function newSelect(){
        if (!$this->_db_driver) {
            echo __FILE__ . __LINE__ . '<pre>';
            var_dump($this->_db_driver);
            print_r(get_defined_vars());
            exit;
        }
        try {
            //$select = (new QueryFactory($this->_db_driver))->newSelect();
            $select = $this->_db->createQueryBuilder();

        } catch (Exception $e) {
            echo __FILE__ . __LINE__ . '<pre>';
            var_dump($this->_db_driver);
            exit;
        }
        return $select;
    }

    /*
     *   get  data list
     *  @todo $where add __bind key ,for binding params
     */
    function getList($where = array(), $order = array(), $limit = array(), $cols = "*", $joins = array(), $group = array(), $having = array(), $is_distinct = false)
    {

        $select = $this->newSelect();

        /*$select->cols([
            'user_id'
        ])->from($this->db_prefix.'users')
            ->where('user_id=:user_id',array("user_id"=> $id));


        $sql="SELECT user_id FROM tx_users where user_id=:user_id";
        $bind = array("user_id"=> $id);*/
        //return  $this->_db->fetchAll($select->getStatement(),$select->getParameters());
        if (is_string($cols)) {
            $cols = [$cols];
        }

        if ($is_distinct) {
            $select->distinct();
        }

        foreach ((array)$joins as $v) {
            //error_log(__FILE__.__LINE__." \n join = ".var_export($v,true));
            $join_alias = $v['name'];
            if (!is_array($v['name'])) {
                $v['name'] = $this->db_prefix . $v['name'] ;
            } else {
                foreach ($v['name'] as $join_alias => $model) {
                    $v['name'] = $this->db_prefix . $model ;
                }
            }
            $join_type = isset($v['type']) ? strtoupper($v['type']).'Join' : 'join';
            if(!in_array($join_type,array('join','innerJoin', 'leftJoin','rightJoin'))){
                $join_type = 'join';
            }
            //error_log(__FILE__.__LINE__." \n v_name  = {$v['name']}");
            $select->join($this->getAlias(),$v['name'],$join_alias, $v['condition']);
            if ($v['cols']) {
                if (is_array($v['cols'])) {
                    $cols = array_merge($cols, $v['cols']);
                } else {
                    $cols[] = $v['cols'];
                }
            }
        }

        $select->select($cols)->from($this->_name , $this->getAlias());


        $cols_filter = array_merge($cols,$this->info(self::COLS)); //show all columns

        $arr_meta = $this->info();


        $arr_sub_where = $this->buildWhere($where,$cols_filter,'and',0); //if multi op,such as multi 'and',then use ' and ','  and  '
        //error_log(__FILE__.__LINE__." sub=".var_export($arr_sub_where,true));
        //$arr_where[] = $arr_sub_where['where'];
        //$arr_where_bind = array_merge($arr_where_bind,$arr_sub_where['bind']);
        $select->where($arr_sub_where['where']);

        if(is_array($arr_sub_where['bind']) && $arr_sub_where['bind']){
            foreach($arr_sub_where['bind'] as $arr_sub_where_bind_k=>$arr_sub_where_bind_v){
                $select->setParameter(':'.$arr_sub_where_bind_k,$arr_sub_where_bind_v);
            }
        }

        if ($order) {
            $_arr_sort = array();
            foreach ((array)$order as $k => $v) {
                if (is_int($k)) {
                    //$select->order($order);
                    $_arr_sort[] = $v;
                } else if ($k == 'sort') {
                    if (($json_order = json_decode($v, true)) !== null) {
                        foreach ((array)$json_order as $k => $v) {
                            $v['property'] = $this->filterColumn($v['property']);
                            $_arr_sort[] = "{$v['property']} {$v['direction']}";
                        }
                    } else {
                        $sort = $this->filterColumn($order['sort']);
                        if ($sort) {
                            $dir = isset($order['dir']) && in_array(strtolower($order['dir']), array('asc', 'desc')) ? $order['dir'] : 'asc';
                            $_arr_sort[] = "$sort $dir";
                        }
                    }
                } else if ($k == 'dir') {

                } else {
                    //just for 'array(col=>dir)'
                    $sort = $this->filterColumn($k);
                    if ($sort) {
                        $dir = in_array(strtolower($v), array('asc', 'desc')) ? $v : 'asc';
                        $_arr_sort[] = "$sort $dir";
                    }
                }
            }
            $select->add('orderBy',$_arr_sort);
        }
        if ($limit) {
            if(false && $this->TABLE_NAME == 'weixin_commonshop_products' && $limit && $limit!=1){
                error_log(__FILE__.__LINE__."\n limit = ".var_export($limit,true));
            }
            if (is_array($limit)) {
                $select->setMaxResults((int)$limit['limit']);
                if (isset($limit['start']) && $limit['start']) {
                    $select->setFirstResult((int)$limit['start']);
                }
            } else {
                $select->setMaxResults((int)$limit);
            }
        }

        if ($group) {
            $select->groupBy($group);
        }
        if ($having) {
            $select->having($having);
        }
        if($this->is_debug && self::isDev() ){
            error_log(__FILE__.__LINE__.var_export($select->getSQL(),true));
            error_log(__FILE__.__LINE__.var_export($select->getParameters(),true));
            $arr_tmp =  $select->getParameters();
            $str_tmp = $select->getSQL();
            foreach($arr_tmp as $k_tmp=>$v_tmp){
                $str_tmp = str_replace($k_tmp,is_bool($v_tmp)?$v_tmp:"'{$v_tmp}'",$str_tmp);
            }
            error_log(__FILE__.__LINE__."\n".$str_tmp);
        }
        return $this->fetch_all_array($select->getSQL(),$select->getParameters());

    }



    /**
     * @param $where
     * @param array $cols
     * @param string $op 'and' or 'or'
     * @param string $col_suffix
     * @param int $tmp_i_where where start index
     * @return array
     * @testing
     * $where = array('id'=>1,'col2'=>'val2','3=3',"find_in_set(col4,'1,2,3,4') >"=>0,'col5 >='=>5 ,'or'=>array('col61'=>'val1','col2'=>'val62'));
     * $cols = array('id','col2','col1','col5','col61');
     */
    function buildWhere($where,$cols=array(),$op='and',$col_suffix = '',$tmp_i_where = 0){
        if(!$cols){
            $cols = $this->info(self::COLS);
        }
        $arr_where = array();
        $arr_where_bind = array();

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
                    if(count($arr_k) == 1){
                        $arr_where[] =  $k.' = :col__'.$current_col_suffix;
                        $arr_where_bind['col__'.$current_col_suffix] =   $v;

                    }else{
                        $arr_where[] =  $k.' :col__'.$current_col_suffix;
                        $arr_where_bind['col__'.$current_col_suffix] =   $v;
                    }
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
                $arr_where_bind = array_merge($arr_where_bind,$tmp_arr_bind_values);
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


    function info($filter = '')
    {
        if (!isset(self::$_columns[$this->_name]) || self::$_columns[$this->_name]) {
            self::$_columns[$this->_name] = $this->_db->fetchAll(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($this->_name)));
        }

        //echo __FILE__.__LINE__.'<pre>';print_r(self::$_columns);exit;
        $rows = self::$_columns[$this->_name];
        $arr = array();
        foreach ($rows as $row) {
            $arr[$row['Field']] = $row;
        }
        if ($filter == self::COLS) {
            return array_keys($arr);
        } else {
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


    function getArray($where = array(), $order = array(), $limit = array(), $cols = "*")
    {
        $rs = $this->getList($where, $order, $limit, $cols);
        $arr = array();
        foreach ((array)$rs as $k => $v) {
            //except value field is not primary key,
            $key_col = $this->_primary;
            if (!array_key_exists($this->_primary, $v)) {
                $arr_cols = array_keys($v);
                $key_col = $arr_cols[0];
            }
            $arr[$v[$key_col]] = $v;
        }
        return $arr;
    }




    public function fetch_all_array($sql, $bind=array())
    {
        try {
	        /*if($this->_name == 'weixin_commonshop_orders' ){
		        echo __FILE__.__LINE__.'<pre>';var_dump($sql);var_dump($bind);
            }*/
	        $enable_slave = false;
	        if(\Baogg\Db::getTransLevel($this->_db_key)!=0){
                $enable_slave = false;
            }else if($this->_enable_slave === true){
                $enable_slave = true;
            }else if(\Baogg\Db::getEnableSlaveStatus() === true){
                $enable_slave = true;
            }

	        //error_log(__FILE__.__LINE__." enable_slave={$enable_slave}");
            //error_log(__FILE__.__LINE__." slave_db=".var_export($this->_slave_db,true));

	        if($enable_slave){
                $rs = $this->_slave_db->fetchAll($sql, $bind);
            }else{
                $rs = $this->_db->fetchAll($sql, $bind);
            }
        } catch (\PDOException $pe) {
            echo __FILE__ . __LINE__ . '<pre>'.$sql;
            echo '<br /><pre>' . __FILE__ . __LINE__;
            echo '<br />' . $pe->getMessage();
            echo '<br />';
            debug_print_backtrace();
            exit;
        } catch (\Exception $e) {
            echo __FILE__ . __LINE__ . '<pre>';
            echo '<br /><pre>' . __FILE__ . __LINE__;
            echo '<br />' . $e->getMessage();
            echo '<br />';
            debug_print_backtrace();
            exit;
        } finally {
//            echo __FILE__.__LINE__.'<pre>';var_dump($select->getStatement());var_dump($select->getParameters());
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

    public function fetch_row_array($sql, $assoc = true)
    {
        $arr = $this->fetch_all_array($sql,$assoc);
        return $arr?$arr[0]:array();
    }



    function insert($tbl = '', $arr = array())
    {
        if($this->_primary){
            unset($arr[$this->_primary]);
        }

        //error_log(__FILE__.__LINE__." \n arr_params = ".var_export($arr,true));
        $arr=$this->filterForm($arr);
        //error_log(__FILE__.__LINE__." \n arr_params = ".var_export($arr,true));

        //$insert = $this->_db->createQueryBuilder();
        //$insert->insert($tbl?$tbl:$this->_name)->values($arr);
        try{
           //error_log(__FILE__.__LINE__.var_export($insert->getStatement(),true));
           //error_log(__FILE__.__LINE__.var_export($insert->getParameters(),true));
            $rows_affected = $this->_db->insert($tbl?$tbl:$this->_name,$arr);
        }catch (\Exception $e){
            error_log(__FILE__.__LINE__." \n err msg=".var_export($e->getMessage(),true));
            return 0;
        }
        $last_insert_id = $this->_db->lastInsertId();

        /*$rs = $this->getOne($last_insert_id);
        if($rs){
            $ModelHistory = new ModelHistory();
            $ModelHistory->create($this,__METHOD__,$rs[0] );
        }*/


        return $last_insert_id;


    }

    /**
     * @param $tbl
     * @param $where
     * @return mixed ;sql error then return false,execute none then return true;exec >=1 lines,then return affected rows
     */
    function delete($tbl, $where)
    {

        //$delete = (new QueryFactory($this->_db_driver))->newDelete();
        //$delete->from($tbl?$tbl:$this->_name);
        $tbl = $tbl?$tbl:$this->_name;
        $tbl_alias = $this->getAlias($tbl);

        $arr_where_bind = $this->buildWhere($where);


        //echo $where;
        if(false && $tbl_alias == 'menu_type'){
            error_log(__FILE__.__LINE__." \n sql=delete {$tbl_alias} from {$tbl} as {$tbl_alias} where ".($arr_where_bind['where']?$arr_where_bind['where']:"1=1"));
            error_log(__FILE__.__LINE__." \n bind =".var_export($arr_where_bind['bind'],true));
            error_log(__FILE__.__LINE__." \n where =".var_export($where,true));
        }
        $stmt = $this->_db->prepare("delete {$tbl_alias} from {$tbl} as {$tbl_alias} where ".($arr_where_bind['where']?$arr_where_bind['where']:"1=1"));
        $rows_affected = $stmt->execute($arr_where_bind['bind']);
        if($rows_affected){
            $rows_affected = $stmt->rowCount();
            if(!$rows_affected){
                $rows_affected = true;
            }
        }
        //$rows_affected = $this->_db->prepare($delete->getStatement())->execute($delete->getParameters());
        //error_log(__FILE__.__LINE__." \n rows_affected = {$rows_affected}");
        return $rows_affected;
    }

    function deleteById($id){
        $arr_id=is_array($id)?array_filter($id,'is_numeric'):array_filter(explode(",",$id),'is_numeric');
        $where = "{$this->_primary} in (".$this->_db->quote($arr_id).")";
        return $this->delete($this->_name,$where);
    }

    /**
     * @param $tbl
     * @param $arr
     * @param $where
     * @param bool $flag_value_is_field
     * @return bool ;sql error then return false,execute none then return true;exec >=1 lines,then return affected rows
     */
    function update(string $tbl, array $arr, $where,$flag_value_is_field=false)
    {
        $tbl = $tbl?$tbl:$this->_name;
        $arr_where_bind = $this->buildWhere($where);
        $arr_str_set = [];
        foreach($arr as $col=>$val){
            if($val instanceof Table\Expr){
                $arr_str_set[] = "$col = ".$val->__toString();
            }else{
                $arr_str_set[] = "$col = :$col";
                $arr_where_bind['bind'][$col] = $val;
            }
        }

        $sql = 'UPDATE ' . $tbl.' as '.$this->getAlias($tbl)
            . ' SET ' . implode(', ', $arr_str_set)
            . ($arr_where_bind['where']  ? ' WHERE ' . ((string) $arr_where_bind['where'] ) : '');



        try{
            $stmt = $this->_db->prepare($sql);
            $rows_affected = $stmt->execute($arr_where_bind['bind']);

            if(false && $tbl == 'baogg_menu_type'){
                error_log(__FILE__.__LINE__."\n".var_export($sql,true));
                error_log(__FILE__.__LINE__."\n".var_export($arr_where_bind['bind'],true));
                error_log(__FILE__.__LINE__." \n ".var_export($rows_affected,true));
            }

            if($rows_affected){
                $rows_affected = $stmt->rowCount();
                if(!$rows_affected){
                    $rows_affected = true;
                }
            }
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
        return $rows_affected;

    }




    function updateById($arr, $id='') {

        $rows_affected  = true;

        //echo __FILE__.__LINE__.'<pre>';		print_r($arr);
        //filter col
        $arr=$this->filterForm($arr);
        $id= ($id===''?$arr[$this->_primary]:$id);

        $where = is_array($id)? "{$this->_primary} in (".$this->_db->quote(array_filter($id,'is_numeric')).")" : "{$this->_primary} = ".$this->_db->quote( (int)$id);
        $rs = $this->getList($where);
        //filter col val
        //error_log(__FILE__.__LINE__." \n rs = ".var_export($rs,true));
        foreach((array)$rs as $v){
            //need to update data
            $arr_update  = array();
            foreach($arr as  $col=>$val){
                if($val  != $v[$col]){
                    $arr_update[$col] = $val;
                }
            }

            //error_log(__FILE__.__LINE__." \n arr_update = ".var_export($arr_update,true));

            if($arr_update){
                $rows_affected = $this->update($this->_name,$arr_update,$where);
            }
        }

        return $rows_affected;
    }

    /*public function buildWhere($where= array()){

        if(!$where){
            $where[] = "1=1";
        }
        if(is_string($where)){
            return $where;
        }
        $str_where = array();
        foreach((array)$where as $k=>$v){
            if(is_numeric($k)){
                $str_where[]=$v;
            }else if(is_array($v) ){
                $str_where[] = "{$k} in ('".implode("','",$v)."')";
            }else if(is_int($v) || is_float($v) || is_bool($v)){
                $str_where[] = "{$k}={$v}";
            }else{
                $str_where[] = "{$k}='{$v}'";
            }
        }
        return $str_where;
    }*/


    public function getList2($where=array(),$order_by='',$cols='*'){
        $str_where = $this->buildWhere($where);
        $query = "select {$cols} from ".($this->TABLE_SCHEMA?$this->TABLE_SCHEMA.'.':'').$this->TABLE_NAME." where ".implode(' and ',$str_where).' '.$order_by;
        //error_log(__FILE__.__LINE__.$query);
        return $this->fetch_all_array($query);
    }

    public function insertTo($arr){
        return $this->insert($this->_name,$arr);
    }

    public function updateTo($arr=array(),$where=array(),$flag_value_is_field=false){
        if($where && is_numeric($where)){
            $where = array($this->_primary=>$where);
        }
        try {
            return $this->update($this->_name,$arr,$where,$flag_value_is_field);
        }catch(\Exception $e){
            return false;
        }
    }

    public function deleteTo($where){
        return $this->delete($this->_name,$where);
    }
    public function floor_decimals($data,$decimals)
    {
        $data = bcadd($data,0,$decimals);
        return $data;
    }

    public function splash_new($search) {
        if(empty($search) and $search!=0){
            return "";
        }
        $search = addslashes($search);
        //$search = str_replace("_","\_",$search);
        //$search = str_replace("'","",$search);
        //$search = nl2br($search); // 回车转换
        /*----------------修改html标记转换-----------------
        $search = htmlspecialchars($search); // html标记转换
        $check  = preg_match('/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i',$search);
        if ($check) {
            echo '<script language="JavaScript">alert("系统警告：\n\n请不要尝试在参数中包含非法字符！");</script>';
            exit();
        }else{
            return $search;
        }
        ----------------修改html标记转换-----------------*/
        return $search;
    }

    public function splash_new_array($a){
        if(is_array($a)){
            foreach($a as $n=>$v){
                $b[$n]=$this->splash_new_array($v);
            }
            return $b;
        }else{
            return $this->splash_new($a);
        }
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
            if( is_null($v) ){ //|| $v=== '' ,$arr_meta[$k]['Default'] is null will throw an exception
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
    function getAlias($name=null){
        if($name===null){
            return str_replace($this->db_prefix, '', $this->_name);
        }else{
            return str_replace($this->db_prefix, '', $name);
        }
        return '';
    }

    function getRow($where = array(), $order = array(), $limit = array(), $cols = "*", $joins = array(), $group = array(), $having = array(), $is_distinct = false){
        if(!$limit){
            $limit = 1;
        }
        $rs = $this->getList($where, $order, $limit, $cols,$joins,$group,$having);
        $row = $rs && $rs[0]?$rs[0]:array();
        foreach($row as $k=>$v){
            if($v === null){
                $row[$k] = '';
            }
        }
        return $row;
    }


    function getByID($id=0,$cols='*'){
        return $this->getRow(array($this->_primary=>$id),array(),array(),$cols);
    }

    /**
     * @param array $rs
     * @param array $arr_col
     * @return array filter result list by fixed column name array
     */
    public function filterListByColumn($rs=array(),$arr_col=array()){
        if(!$rs || !is_array($rs)){
            return $rs;
        }

        $ret = array();
        foreach($rs as $k=>$row){
            if(!$row || !is_array($row)){
                continue;
            }

            $ret[]=$this->filterRowByColumn($row,$arr_col);
        }

        return $ret;
    }


    public function filterRowByColumn($row = array(),$arr_col = array()){
        $new_row = array();

        foreach($arr_col as $v_col_name=>$v_col_alias){
            if(is_numeric($v_col_name)){  //no column name,so column alias is the same as column name
                $v_col_name = $v_col_alias;
            }
            if(!isset($row[$v_col_name])){
                continue;
            }
            $new_row[$v_col_alias] = $row[$v_col_name];
        }

        return $new_row;
    }

    public function execute($sql='',$arr_bind=array()){
        $row_effected = 0;
        try{
            $row_effected = $this->_db->prepare($sql)->execute($arr_bind);
        }catch(Exception $e){
            $str_tmp = $sql;
            foreach((array)$arr_bind as $k_tmp=>$v_tmp){
                $str_tmp = str_replace(':'.$k_tmp,is_bool($v_tmp)?$v_tmp:"'{$v_tmp}'",$str_tmp);
            }
            error_log(__FILE__.__LINE__." \n sql error sql={$str_tmp}");
        }
        return $row_effected;
    }

    public static function isValidBatchcode($batchcode=''){
        return is_numeric($batchcode);
    }

    public static function isDev(){
        return \Baogg\File::getSetting('settings.is_dev');
    }

    public function setDebug($is_debug = false){
        $this->is_debug = $is_debug;
    }
}
