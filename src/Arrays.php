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


/**
 * App
 *
 * This is the primary class with which you instantiate,
 * configure, and run a Slim Framework application.
 * The \Baogg\App class also accepts Slim Framework middleware.
 */
class Arrays
{
    /**
     * Replaces the keys in the given array with an array of in-order
     * replacement keys.
     * @param array &$rs ,sql query result set
     * @param array $map_key ,key convert map.such as array('a'=>'b','c'=>'d')
     * @param int $handle ,such as 1:only use new key;2:replace old key to new key;3:keep old key and new key
     * @return array
     **/
    public static function convertKey($rs, $map_key=array(),$handle=2)
    {
        foreach($rs as $k=>$v){
            $rs[$k]=self::convertRowKey($v,$map_key,$handle);
        }
        return $rs;
    }
    /**
     *convert row,add new key column,value as old key's value
     **/
    public static function convertRowKey($row,$map_key=array(),$handle=2){
        foreach((array)$map_key as $new=>$old){
            if($old==$new || $new=='' || $old==''){
                continue;
            }
            $row[$new]=$row[$old];
        }

        $arr_keys=array_keys($map_key);
        foreach((array)$row as $k=>$v){
            //if replace key or only use new key
            if(($handle==2 && in_array($k,$map_key) && !in_array($k,$arr_keys)) || ($handle==1 && !in_array($k,$arr_keys))){
                unset($row[$k]);
            }
        }

        return $row;
    }

    ////such  as ,msort($rs,array('field1' => array(SORT_DESC, SORT_NUMERIC),'field3' => array(SORT_DESC, SORT_NUMERIC) ),true )
    public static function msort(&$rs, $arr_sort, $caseInSensitive = true)
    {
        if( !is_array($rs) || !is_array($arr_sort)){
            return $rs;
        }
        $args         = array();
        $arr_col_val  = array();
        $arr_args_dir = array();
        $i            = 0;
        foreach($arr_sort as $col => $arr_sort_dir){
            $arr_sort_dir = is_array($arr_sort_dir)?$arr_sort_dir:array($arr_sort_dir);
            $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $arr_sort_dir) || in_array(SORT_REGULAR, $arr_sort_dir));
            foreach ($rs as $key => $row)
            {
                $arr_col_val[$col][$key] = $convertToLower ? strtolower($row[$col]) : $row[$col];
            }
            $args[] = &$arr_col_val[$col];
            foreach($arr_sort_dir as $sortAttribute)
            {
                $arr_args_dir[$i] = $sortAttribute;
                $args[] = &$arr_args_dir[$i];
                $i++;
            }
        }
        $args[] = &$rs;
        //echo __FILE__.__LINE__.'<pre>';print_r($args);
        call_user_func_array('array_multisort', $args);
        return end($args);
    }
    public static function genFile($filename='',$arr=array()){
        file_put_contents($filename,  "<?php" . PHP_EOL . "return\t" . var_export($arr, true) . ";");
        return true;

    }
    /**
     * @param array $arr
     * @param $obj_xml SimpleXMLElement object
     * @return void
     */
    public static function toXMLChild($arr = array(), &$obj_xml) {

        foreach((array)$arr as $key => $value) {
            if(is_array($value)) {
                $key = is_numeric($key) ? "item$key" : $key;
                $subNode = $obj_xml->addChild("$key");
                self::toXMLChild($value, $subNode);
            }
            else {
                $key = is_numeric($key) ? "item$key" : $key;
                $obj_xml->addChild("$key","$value");
            }
        }
    }
    /**
     * convert array to xml with root node
     * @param  array  $arr  source array
     * @param  string $root root string value
     * @return string       such as <row><key>value</key></row>
     */
    public static function toXML($arr = array(),$root = '<row/>'){
        $xml = new SimpleXMLElement($root);
        self::toXMLChild($arr,$xml);
        return $xml->asXML();
    }
}