<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Tree.php 463 2012-02-09 02:55:04Z beimuaihui $
 */
namespace Baogg\Db;
class Tree {
    function Rs2Tree (& $rs = array())
    {
        foreach ((array) $rs as $v) {
            $tr[$v['pid']][$v['id']] = $v;
        }
        return $tr;
    }
    function genMenu (& $tr = array(), $parentId = 0)
    {
    	$ret=array();
        foreach ((array) $tr[$parentId] as $k => $v) {
        	
            if (isset($tr[$v['id']]) && is_array($tr[$v['id']])) {
                $v['menu']['items'] = $this->genMenu( $tr, $v['id']);
            }else{
            	$v['handler']=new Zend_Json_Expr("addTab");
            }
            
            $ret[]=$v;
        }
        
        return $ret;
    }
    
    /**
     * 
     *change db result to combobox store
     * @param $rs db query result,rs columns is id,text,pid
     * @param $pid
     */
    function rs2ComboTree($rs){
    	if(!$rs || !is_array($rs)){
    		return array();
    	}
    	$columns=array_keys($rs[0]);
    	foreach ((array) $rs as $v) {   		    		    	
            $tr[$v[$columns[2]]][$v[$columns[0]]] = $v[$columns[1]];
        }
        
        return $tr;
    }
    function tr2ComboStore(& $tr,$pid=0,$level=0)
    {
    	$ret=array();    	
        
        if($pid === 0 && !isset($tr[$pid])){
            $pid = '';
        }

        if(!isset($tr[$pid])){
        	return array();
        }
        
        foreach((array)$tr[$pid] as $k=>$v){
        	if($level){
        		$v=str_repeat("--", 3*$level).'|--'.$v;
        	}        	
        	$ret[]=array(''.$k,$v);        	
        	$ret=array_merge($ret,$this->tr2ComboStore($tr,$k,$level+1));        	
        }        
        return $ret;
    }
    
	/**
	 * 
	 * change rs to grid store,
	 * @param $rs
	 * @param $col_id
	 * @param $col_pid
	 */
    function rs2GridTree($rs){
        
        $tr=array();
    	if(!$rs || !is_array($rs)){
    		return array();
    	}
    	$columns=array_keys($rs[0]);  
        
        /*if($columns[2] == 'hideTrigger'){          
            echo __FILE__.__LINE__.'<pre>';print_r($columns);print_r($rs); exit;//debug_print_backtrace();
        }*/ 

    	foreach ((array) $rs as $v) { 
            if(!isset($tr[$v[$columns[2]]])){
                $tr[$v[$columns[2]]]=array();
            }  		    		    	
            /*if(!isset($tr[$v[$columns[2]]][$v[$columns[0]]])){
                 echo '</script>'.__FILE__.__LINE__.'<pre>';print_r($columns);print_r($v);print_r($rs);debug_print_backtrace(); exit;//
            } */          
            
            try{
                $tr[$v[$columns[2]]][$v[$columns[0]]] = $v;
            }catch(Exception $e){
               // echo __FILE__.__LINE__.'<pre>';print_r($rs);exit;
            }
        }
        
        return $tr;
    }
    function tr2GridStore(& $tr,$pid=0,$level=0)
    {
    	//echo __FILE__.__LINE__.'<pre>';print_r($tr);exit;
    	$ret=array();    	
        
        if($pid === 0 && !isset($tr[$pid])){
            $pid = '';
        }
        if(!isset($tr[$pid])){
        	return array();
        }
        
        //, $col_name='text'
    	$key1=key($tr);
    	$key2=key($tr[$key1]);
    	$arr_keys=array_keys($tr[$key1][$key2]);
    	$col_name=$arr_keys[1];
    	
    	//echo __FILE__.__LINE__.'<pre>';print_r($tr);var_dump($level);exit;
        foreach((array)$tr[$pid] as $k=>$v){ 
        	if($level){       	
        		$v[$col_name]=str_repeat("--", 3*$level).'|--'.$v[$col_name];
        	}        	
            $v['level_alias'] = $level;
        	$ret[]=$v; 
        	//if $k=0 will cause bad crashed
        	if($k){       	
        		$ret=array_merge($ret,$this->tr2GridStore($tr,$k,$level+1,$col_name));
        	}        	
        }        
        return $ret;
    }
	function tr2ExtTreeStore(& $tr,$pid=0,$level=0){
		$ret=array();
		
		if($pid === 0 && !isset($tr[$pid])){
            $pid = '';
        }

		if(!isset($tr[$pid])){
			return array();
		}
		
		//, $col_name='text'
		$key1=key($tr);
		$key2=key($tr[$key1]);
		$arr_keys=array_keys($tr[$key1][$key2]);
		
		foreach((array)$tr[$pid] as $k=>$v){
		    $rs_child = $this->tr2ExtTreeStore($tr,$k,$level+1);
		    if($rs_child){
			    $v['children'] = $rs_child;
            }
			$ret[]=$v;
		}
		return $ret;
	}
	/**
	*add child items to parent's items value
	**/
	function tr2Fieldset(& $tr,$pid=0)
	{
		// echo '</script><pre>'.__FILE__.__LINE__;print_r($tr);echo $pid;
		
        
		if($pid === 0 && !isset($tr[$pid])){
            $pid = '';
        }
        if(!isset($tr[$pid])){
            return false;
        }
        /*if(!isset($tr[$pid])){
            echo '</script><pre>'.__FILE__.__LINE__;print_r($tr);
            echo $pid;
            debug_print_backtrace();
            exit;
        } */

		foreach((array)$tr[$pid] as $k=>$v){
			if(!isset($arr_keys)){
				$arr_keys=array_keys($v);
				$id_col=$arr_keys[0];
				$name_col=$arr_keys[1];
				$pid_col=$arr_keys[2];
			}
            try{
                $tr[$pid][$k]['title'] = isset($tr[$pid][$k]['title'])?$tr[$pid][$k]['title']:$v[$name_col];
                if(!isset($v[$name_col])){
                   // throw new Exception('UNDEFINED');
                }
            }catch(Exception $e){
               /* echo '</script>'.__FILE__.__LINE__.'<pre>';print_r($tr);var_dump($name_col);var_dump($v);
                 debug_print_backtrace();
                echo '<br />'.$e->getMessage();exit;*/
            }
            if(!isset($tr[$pid][$k]['id']) && !isset($tr[$pid][$k]['name']) && (!isset($tr[$pid][$k]['xtype']) || $tr[$pid][$k]['xtype'] == 'fieldset') ){
                $tr[$pid][$k]['id']    = 'category_'.@$v[$id_col];    
            }
            
            $tr[$pid][$k]['xtype'] = isset($tr[$pid][$k]['xtype'])?$tr[$pid][$k]['xtype']:'fieldset';

           /* if(!isset($v[$id_col])){
                echo __FILE__.__LINE__.'<pre>';print_r($tr);exit;
            }*/

			if(isset($tr[$v[$id_col]])){				
				$this->tr2Fieldset($tr,$v[$id_col]);				
				$tr[$pid][$k]['items']=array_values($tr[$k]);
				unset($tr[$k]);
			}
		}
		$tr[$pid]=array_values($tr[$pid]);
	}
	
	
	
	function arr2combo($arr=array())
	{
		
	}
}
