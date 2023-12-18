<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Form.php 197 2011-02-18 12:45:33Z beimuaihui $
 */
namespace Baogg\View;
class Form {
	/**
	 *  form data structure ,change result set to tree type
	 */
	private $_elements=array();
	
	/**
	 * must input the datastructure like reseultset
	 */
	public function __construct($rs=array()){
		foreach((array)$rs as $k=>$v){
			$rs[$k]['id']=$k;
			if(isset($v['plugin'])){
				$pid=explode(":",$v['plugin']);
				$rs[$pid[0]]['plugin'][$pid[1]][]=$rs[$k];
				unset($rs[$k]);
			}
		}
		foreach((array)$rs as $v){			
			if(!isset($v['pid'])){
				$this->_elements[0]=$v;
			}else{
				if(!isset($this->_elements[$v['pid']])){
					$this->_elements[$v['pid']]=array();
				}
				$this->_elements[$v['pid']][$v['id']]=$v;
			}
		}
		//echo "<pre>";print_r($this->_elements);
	}
	/**
	 * render form tag
	 */
	public function renderForm($element){
		 $content="<form ";
		 if(!isset($element['action'])){
		 	$element['action']='post';
		 }
		 foreach((array)$element as $k=>$v){
		 	if($k!="elements"){
		 		$content.=" $k='$v' ";
		 	}
		 }
		 $content.=">";
		 foreach((array)$this->_elements[$element['id']] as $v){
		 	$content.=$this->renderElement($v);
		 }
		 $content.="</form>";
		 return $content;
	}
	/**
	 * render field set tag
	 */
	public function renderFieldset($element){
		 $content="<fieldset ";
		 foreach((array)$elements as $k=>$v){
		 	if($k!="elements"){
		 		$content.=" $k='$v' ";
		 	}
		 }
		 $content.=" ><legend>{$v['label']}</legend>";
		 foreach((array)$this->_elements[$element['id']] as $v){
		 	$content.=$this->renderElement($v);
		 }
		 $content.="</fieldset>";
		 return $content;
	}
	/**
	 * render form element
	 */
	public function renderComboElement($element){
		//show tag
		if(!isset($element['tag'])){
			$element['tag']=isset($element['pid'])?"fieldset":"form";
		}
		
		$content="<{$element['tag']}";
		$element['class']=isset($element['class'])?$element['class']." form{$element['tag']}":"form{$element['tag']}";
		//show properties
		foreach((array)$element as $k=>$v){
			if(!in_array($k,array('tag','label'))){
		 		$content.=" $k='$v' ";
			}
		 }
		 
		//end tag
		$content.=">";
		
		//show legend
		if(isset($element['legend'])){
			$content.="<legend>{$element['legend']}</legend>";
		}
		
		//show content
		if(isset($element['content'])){
			$content.=$element['content'];	
		}else{
			//print_r( $this->_elements[$element['id']]);
			 foreach((array)$this->_elements[$element['id']] as $v){
			 	$content.=$this->renderElement($v);
			 }
		}
		
		//untag
		$content.="</{$element['tag']}>";
		
		return $content;
	}
	/**
	 * render form element
	 */
	public function renderElement($v){
		$content='';
		if(isset($this->_elements[$v['id']])){
			return $this->renderComboElement($v);
		}		
		
		//plugin explain
		$plugContent=array(0=>array(),1=>array(),2=>array(),3=>array(),4=>array());
		if(isset($v['plugin'])){
				foreach((array)$v['plugin'] as $pos=>$arrPlug){
					foreach((array)$arrPlug as $k=>$plug){
						$plugContent[$pos][$k]=$this->renderFormElement($plug);
					}
				}
		}
			
		if(isset($v['content'])){
			$content.=implode("",$plugContent[0]);
			$content.=$v['content'];	
			$content.=implode("",$plugContent[1]);
		}else{	
			$content.=implode("",$plugContent[0]);		
			$content.="<div id='div{$v['id']}' class='divFormRow'>";
			$content.=implode("",$plugContent[1]);
			$content.=$this->renderLabel($v);
			$content.=implode("",$plugContent[2]);
			$content.=$this->renderFormElement($v);
			$content.=implode("",$plugContent[3]);
			$content.="</div>";
			$content.=implode("",$plugContent[4]);
		}
		return $content;
	}
	
	/**
	 * render form element's label
	 */
	public function renderLabel($v){
		if(!isset($v['label'])){
			$v['label']=Translate::$$v['id'];
		}
		if($v['label']!==false){
			return "<label for='{$v['id']}' id='lbl{$v['id']}' class='formLabel'>{$v['label']}</label>";
		}
	}
	/**
	 * render form element's input control
	 */
	public function renderFormElement($v){
		$content='';
		if(!isset($v['tag'])){
			$v['tag']='input'; //show tag
		}
		
		if(in_array($v['tag'],array('checkbox','radio','select'))){
			 $fn='renderForm'.ucwords($v['tag']);
			return $this->$fn($v);
			// call_user_func( array(__CLASS__,$fn),$v);
		}
		
		
		 $content.="<{$v['tag']} ";
		 $v['class']=isset($v['class'])?$v['class']." form{$v['tag']}":"form{$v['tag']}";
		 foreach((array)$v as $k=>$prop){
		 	$content.=" $k='$prop'  ";
		 }
		 $content.=" name='{$v['id']}'  ";
		 if(isset($v['type']) && in_array($v['type'],array('submit','reset'))){
		 	$multi=Translate::$$v['type'];
		 	$content.=" value='{$multi}' ";
		 }
		 if($v['tag']=='textarea')
			 $content.="></{$v['tag']}>";
		 else 
		   $content.=" />";
		 return $content;
	}
	/**
	 * redner form element's textbox
	 */
	public function renderFormRadio($v){
		 $content="<fieldset id='fld{$v['id']}' class='rowfieldset'>";
		 if(isset($v['legend'])){
		 	$content.= "<legend>{$v['legend']}</legend>";
		 }
		 foreach((array)$v['data'] as $k=>$l){
		 	$v['class']=isset($v['class'])?$v['class']." form{$v['tag']}":"form{$v['tag']}";
		 	if(isset($v['selected']) && $v['selected']==$k){
		 		$content.= "<input class='{$v['class']}' type='radio' id='{$v['id']}_{$k}' name='{$v['id']}' value='{$k}' checked='true' />";
		 	}else{
		 		$content.= "<input class='{$v['class']}' type='radio' id='{$v['id']}_{$k}' name='{$v['id']}' value='{$k}'  />";
		 	}
		 	$content.= "<label for='{$v['id']}_{$k}'>{$l}</label>";
		 }
		 $content.="</fieldset>";
		 //echo $content;exit;
		 return $content;
	}
	
	public function renderFormSelect($v){
		$v['class']=isset($v['class'])?$v['class']." form{$v['tag']}":"form{$v['tag']}";
		 $content="<select id='{$v['id']}' name='{$v['id']}'  class='{$v['class']}'>";
		 foreach((array)$v['data'] as $k=>$l){
		 	
		 	if(isset($v['selected']) && $v['selected']==$k){
		 		$content.= "<option value='{$k}'  selected='true'>$l</option>";
		 	}else{
		 		$content.= "<option value='{$k}' >$l</option>";
		 	}
		 }
		 $content.="</select>";
		 //echo $content;exit;
		 return $content;
	}
	/**
	 * default to string
	 */
	public function __toString() {
		return $this->renderElement($this->_elements[0]);
	}
	

	/**
	 * set elements
	 */
	public function getElements(){
		return $this->_elements;
	}
	/**
	 * get elements
	 */
	public function setElements($rs=array()){
		$this->__construct($rs);
	}
}
?>