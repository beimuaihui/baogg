<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Db.php 262 2011-07-23 15:51:14Z beimuaihui $
 */
namespace Baogg;
abstract class Feed
{
    protected $url='';
    protected $params=array();
    protected $result=array();
    public function __construct($url,$params){
        $this->url=$url;
        $this->params=$params;
    }
    public function beforeHandler(){
        
    }
    public function handler(){
        
    }
    public function afterHandler(){
        
    }
}