<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * @desc convert joomla art layout config to array
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Single.php 197 2011-02-18 12:45:33Z beimuaihui $
 */

namespace Baogg\Xml;
class Layout {
	
	protected $_xml;
	
	public function __construct($xml=''){
		$this->_xml= new SimpleXMLElement($xml);
	}
	
	public function getCss(){
		$ret=array();
		$xml=@json_decode(@json_encode($this->_xml),1);
		
		if(isset($xml['stylesheets'])){
			if(is_array($xml['stylesheets']['file'])){
				foreach($xml['stylesheets']['file'] as $v){
					$ret[]=$v;
				}
			}else{
				$ret[]=$xml['stylesheets']['file'];
			}
		}
		
		return $ret;
	}
	
	public function getJs(){
		$ret=array();
		$xml=@json_decode(@json_encode($this->_xml),1);
		
		if(isset($xml['scripts'])){
			if(is_array($xml['scripts']['file'])){
				foreach($xml['scripts']['file'] as $v){
					$ret[]=$v;
				}
			}else{
				$ret[]=$xml['scripts']['file'];
			}
		}
		
		return $ret;
	}
	
	
	public function getBlocks(){
		//convert ot array
		$xml=@json_decode(@json_encode($this->_xml),1);
		
		$ret=$this->genBlock($xml);
		return $ret;
	}
	
	
	
	function genBlock($xml,$pid=''){
		$ret=array();
	
		$item=array();
	
		//if it is body  tag
		if(isset($xml['name'])){
	
			$item['id']=@$xml['name'];
			$item['title']=@$xml['description'];
			$item['type']='body';
			$item['attribute']=@$xml['@attributes'];
			$item['pid']=$pid;
			$ret[]=$item;
	
			$pid=@$xml['name'];
		}else{
			//if it is a div tag
	
			$item['id']=@$xml['@attributes']['name'];
			$item['title']=@$xml['@attributes']['description'];
			$item['type']='blocks';
			$item['attribute']=@$xml['@attributes'];
			$item['pid']=$pid;
			$ret[]=$item;
	
			$pid=@$xml['@attributes']['name'];
		}
	
	
		if(isset($xml['blocks'])){
			//many blocks
			if($xml['blocks'] ==array_values($xml['blocks'])){
				foreach($xml['blocks'] as $blocks){
					$ret=array_merge($ret,$this->genBlock($blocks,$pid));
				}
			}else{
				$ret=array_merge($ret,$this->genBlock($xml['blocks'],$pid));
			}
		}
	
	
		if(isset($xml['block'])){
			if($xml['block'] ==array_values($xml['block'])){
				foreach($xml['block'] as $block){
					$item['id']=@$block['@attributes']['name'];
					$item['title']=@$block['@attributes']['description'];
					$item['type']='block';
					$item['attribute']=@$block['@attributes'];
					$item['pid']=$pid;
					$ret[]=$item;
				}
			}else{
	
				$item['id']=@$xml['block']['@attributes']['name'];
				$item['title']=@$xml['block']['@attributes']['description'];
				$item['type']='block';
				$item['attribute']=@$xml['block']['@attributes'];
				$item['pid']=$pid;
				$ret[]=$item;
	
			}
		}
	
		return $ret;
	
	
	}
}