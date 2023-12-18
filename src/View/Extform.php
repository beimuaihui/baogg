<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Extform.php 491 2012-04-23 13:18:50Z beimuaihui@gmail.com $
 */
namespace Baogg\View;
class Extform {
	protected $form_config;
	protected $win_config;
	protected $tabs;
	protected $columns=array();	
	protected $buttons;
	protected $hidden_col;
	protected $column_stores=array();
	/**
	 * 
	 * @param array ,must have url $config
	 */
	function __construct($config=array(),$tabs=array(),$win_config=array())
	{
		// 'height'=> 120,'contentEl'=> "form",'region'=> 'north','bodyStyle'=> 'padding:15px 15px 0 15px;',
		$default=array(
                    'frame'=> true,
		    		'bodyBorder' =>false, 
					//'baseCls'=>'x-plain',
					"bodyStyle"=>"padding:5px;",
					'xtype'=>'form',					
					'column_num'=>1,
					'width'=>600,
					'id'=>"ext_form",
					'defaultType'=>'container', //added
		            'fieldDefaults'=>array('msgTarget'=>'side'),					
					'url'=>Zend_Controller_Front::getInstance()->getRequest()->getParams()
		);		
		$this->form_config=array_merge($default,$config);
		
		$default_win=array(
			"id"=>"win_form",
			"xtype"=>"window",
			'autoScroll'=>true,
			"bodyStyle"=>"padding:5px;",
			"closeAction"=>"hide",
			"collapsible"=>true,
			"maximizable"=>true,
			"hidden"=>true,
			"shadow"=>true,
			"modal"=>true
		);
		$this->win_config=array_merge($default_win,$win_config);
		
		//echo '<pre>';print_r($this->form_config);exit;
		if(!$tabs)
		{			
			$column_num=$this->form_config['column_num'];
			if($column_num>1){
				for($i=0;$i<$column_num;$i++)
				{
					$columnWidth=1.0/$column_num;
					$this->columns[$i]=array("columnWidth"=>(float)sprintf("%0.1f",$columnWidth),"defaultType"=>"textfield","layout"=>"anchor","items"=>array());


				}

				if(!isset($this->form_config['layout'])){
					$this->form_config['layout'] = 'column';
				}
			}

			//echo __FILE__.__LINE__.'<pre>';var_dump($this->form_config);exit;
			if(isset($this->form_config['height'])){
				$this->columns[0]['minHeight'] = $this->form_config['height'] - 60;
			}
		}else{
			//remove form panel 's padding width,use tabpanel's padding width
			unset($this->form_config['bodyStyle']);
			
			$default_tabs=array('xtype'=>'tabpanel',
								'activeTab'=>0,
								'plain'=>true,
								'deferredRender'=>false,
								'defaults'=>array('bodyStyle'=>'padding:10px'),
								'height'=>$this->form_config['height'],											
								'items'=>array()
								);
			$this->tabs=array_merge($default_tabs,$tabs);
			foreach($this->tabs['items'] as $k=>$v){
				$this->handleTab($k,$v);
			}
		}
		$this->buttons=array();
		//$this->addItem(0,array('name'=>'task',"xtype"=>"hidden","value"=>"add","hideLabel"=>true));
	}
	
	function getColumns(){
		return $this->columns;
	}
	/**
	 * 
	 * @param 3 columns,which one $i
	 * @param array,must have name,xtype,source $item
	 */
	public function addItem($i,$item=array())
	{
		if($this->form_config['column_num'] ==1){
			$i=0;
		}
		$item=$this->handleItem($item);
		
		//is search with range, between
		if(@$item['is_search_range']){
			$item['is_search_range']=false;			
			$item2=array("xtype"=>"compositefield",
						"items"=>array(										
										$item,
										array("xtype"=>"displayfield","value"=>\Baogg\Language::get("search_to")))
						);
			$this->addItem($i,$item2);					
			//$item3['hideLabel']=true;
			$item3['name']=$item['name'].'_2';
			$item3['fieldLabel']='';
			$this->addItem($i,$item3);
		}else{
			
			$this->columns[$i]['items'][]=$item;
		}
	}
	
	public function addItems($items=array()){
		foreach((array)$items as $name=>$params){
			$params['name']=isset($params['name'])?$params['name']:$name;
			$col_id=isset($params['col_id'])?$params['col_id']:0;
			$this->addItem($col_id,$params);
		}
	}
	
	/**
	 * must have name,url,handler(or form's fn),confirm(msg)
	 * @param array $config
	 */
	public function addButton($config)
	{
		
		$default=array("text"=>\Baogg\Language::get(@$config['name']),
						"iconCls"=>trim(@$config['name'],"0123456789").'-icon',
						"id"=>'btn'.ucwords(@$config['name']));
		
		if(!isset($config['handler'])){					
			if(isset($config['confirm']))
				$config['handler']= new Zend_Json_Expr('function(b,e){'.$this->getConfirmFn($config).'}');
			else 
				$config['handler']=new Zend_Json_Expr('function(b,e){'.$this->getButtonFn($config).'}');
			
		}else{
			$config['handler']=new Zend_Json_Expr('function(b,e){'.$config['handler'].'}');
		}
		unset($config['url']);
		//$this->buttons[]='-';
		$this->buttons[]=array_merge($default,$config);	
		
	}
	
	/**
	 * 
	 * must have properties:{layout:column,title:'',column_num:3}
	 * @param array $tab
	 */
	
	public function addTab($tab)
	{
		$default_tab=array("layout"=>"anchor",
							"title"=>Baogg_Language::get("details"),
							"column_num"=>3);
		if(count($this->tabs)){
			$default_tab["title"]=Baogg_Language::get("more");
		}
		$tab=array_merge($default_tab,$tab);
		
		$this->tabs['items'][]=$tab;
		
	}
	public function handleTab($pos,$tab)
	{
		$default_tab=array("layout"=>"anchor",
							"title"=>Baogg_Language::get("details"),
							"column_num"=>3);
		if($pos){
			$default_tab["title"]=Baogg_Language::get("more");
		}
		$tab=array_merge($default_tab,$tab);
		
		
		$this->tabs['items'][$pos]=$tab;
		
	}
	
	public function handleItem($config=array())
	{
		$config['xtype']=isset($config['xtype'])?$config['xtype']:'textfield';		
		if(!@$config['hideLabel'] && (!array_key_exists('fieldLabel',$config) || !$config['fieldLabel'])  ){
			$item['fieldLabel']=\Baogg\Language::get(@$config['name']);
		}		
		$item["anchor"]='95%';
		$item["id"]=@$config["name"];
		switch(@$config['xtype'])
		{
			case 'combo':	
				//echo '<pre>'.__FILE__.__LINE__;print_r($item);print_r($config);
				 $item=array_merge(array(					                          
                         //"submitValue"=>false,
                         "name"=>$config['name'],
                         "displayField"=>'text',
						  "queryMode"=>'local',
                         "valueField"=>'id',
				  		 "triggerAction"=>'all',
				 		 "hideTrigger"=>false,
				 		 "autoSelect"=>true,
						 "forceSelection"=>true,
				 		 "allowBlank"=>false
				 		// "setValue"=>new Zend_Json_Expr("function(val,doSelect){ if(val==null ){ val=''; }   this.callParent([val,doSelect]); }")
				),$item);
				
				// echo '<pre>'.__FILE__.__LINE__;print_r($item);exit;
				//$item["inputId"]=$item['id'];
				unset($item["id"]);
				//$item["name"]=$config['name'].'2';
				
				break;
			case 'gridcombo':	
				//
				 $item=array_merge(array(					                          
                         //"submitValue"=>false,
                         "name"=>$config['name'],
                         'multiSelect'=>false,
                         'gridCfg'=>array('height'=>300,'paging'=>false),
                         "displayField"=>'text',						 
                         "valueField"=>'id',
				  		 "triggerAction"=>'all',
				 		 "hideTrigger"=>false,
				 		 'typeAhead'=>true,
						 "forceSelection"=>true,
				 		 "allowBlank"=>false
				),$item);				
				break;	
				
			case 'radiogroup':
				//$item['columns']=2;
				$item['defaults']=array('name'=>$config['name'],'flex'=>1);
				//echo __FILE__.__LINE__.'<pre>';print_r($config);exit;
				foreach((array)@$config['source'] as $opt){
					$item['items'][]=array(
						 //'xtype'=> 'radio',
                         "inputValue"=> $opt[0],
                         //"name"=> $config['name'],
                         "boxLabel"=> $opt[1],						
					);
				}
				break;
			case 'checkboxgroup':
				$item['columns']=2;
				//$item['defaults']=array('name'=>$config['name'].'[]');
				foreach((array)$config['source'] as $opt){
					$item['items'][]=array(
						 'xtype'=> 'checkbox',
                         "inputValue"=> $opt[0],
                         //"name"=>$config['name'],
                         "boxLabel"=> $opt[1],
					);
				}
				break;
			case 'htmleditor':				
				if(!isset($config['shrinkWrap']) || in_array($config['shrinkWrap'],array(false,0,1))){
					$item['height']='300';
				}
				if(!isset($config['shrinkWrap']) || in_array($config['shrinkWrap'],array(false,0,2))){
					$item['width']='600';
				}
				
				break;
			case 'tinymce_textarea':
				$item['fieldStyle']= 'font-family: Courier New; font-size: 12px;';
				$item['noWysiwyg']=false;
				$item['tinyMCEConfig']=new Zend_Json_Expr('tinyCfg1');
				break;
			case 'datefield':
				$item['format']='Y-m-d';
				break;
			case 'datefield2':
				$item['format']='Y-m-d H:i:s';			
				break;		
			case 'timefield':
				$item['format']='H:i:s';	
				break;
			case 'hidden':
				$this->hidden_col=$config['name'];
				break;
			case 'fileuploadfield':
				$this->form_config["fileUpload"]=true;
				$item['buttonText']='';	
				$item['buttonCfg']=array("iconCls"=>"upload-icon");	
				break;
			case 'label':
				$item['text']=$config['init_value'];
				break;
			case 'itemselector':
				$item['height']=200;				
				//$item['autoScroll']=true;
				break;
		}
		
		foreach($item as $k=>$v){
			if(!isset($config[$k])){				
				$config[$k]=$v;				
			}else if(is_array($item[$k])){				
				//if single property is array,then merge
				$config[$k]=array_merge_recursive($item[$k],$config[$k]);
			}
		}

		//$config=array_merge_recursive($item,$config);
		/*if($config['xtype']=='radiogroup'){
			echo '<pre>'.__FILE__.__LINE__;print_r($item);print_r($config);exit;
		}*/

		if(@$config['xtype']=='combo' && is_array(@$config['store'])){
			foreach($config['store'] as $k=>$v){
				$config['store'][$k]=array((string)$v[0],(string)$v[1]);
			}
			$config["store"]=new Zend_Json_Expr("Ext.create('Ext.data.ArrayStore', { model:'ComboModel',data:".Zend_Json::encode($config['store'])." } )");
		}else if(@$config['xtype']=='radiogroup'){
			//unset($config['name']);
			$config['name']  = '_'.$config['name'].'_group';
			$config['xtype'] = 'fieldcontainer';
			$config['defaultType'] = 'radiofield';
			$config['layout'] = 'hbox';
			//echo __FILE__.__LINE__.'<pre>';print_r($config);exit;
			//unset($config['id']);
			
		}else if(@$config['xtype']=='checkboxgroup'){
			//unset($config['name']);
			//$config['xtype'] = 'fieldcontainer';
			$config['defaultType'] = 'checkboxfield';
			$config['layout'] = 'hbox';
			//unset($config['id']);
			
		}else if(@$config['xtype']=='gridcombo'){
			//echo '<pre>'.__FILE__.__LINE__;print_r($item);print_r($config);exit;

			if(!isset($config['gridCfg']['height'])){
				$config['gridCfg']['height']=300;
			}
			if(isset($config['store'])){
				$this->column_stores[$config["name"]]=$config['store'];
			}else if( isset($config['gridCfg']['store'])){
				$this->column_stores[$config["name"]]=$config['gridCfg']['store'];
			}
			if(!isset($config['gridCfg']['fields']) && !isset($config['gridCfg']['columns'])){
				$config['gridCfg']['fields']=array('id','text');
			}

			if(isset($config['gridCfg']['fields']) && !isset($config['gridCfg']['columns'])){
				$config['gridCfg']['columns']=array();
				foreach($config['gridCfg']['fields'] as $field_name){
					$config['gridCfg']['columns'][]=array('header'=>Baogg_Language::get($field_name),'dataIndex'=>$field_name);
				}
			}

			//if data store is array,then convert it to arraystore	
			if(is_array($this->column_stores[$config["name"]])){
				foreach($this->column_stores[$config["name"]] as $k=>$v){
					$this->column_stores[$config["name"]][$k]=array((string)$v[0],(string)$v[1]);
				}
				$this->column_stores[$config["name"]]=new Zend_Json_Expr("Ext.create('Ext.data.ArrayStore', { model:'ComboModel',data:".Zend_Json::encode($this->column_stores[$config["name"]])." } )");
			}
			$config['gridCfg']['store']=$config['store']=new Zend_Json_Expr("ext_form_{$config['name']}_store");

			//if page size
			if($config['gridCfg']['paging'] || isset($config['gridCfg']['pageSize'])){
				if(!isset(	$config['gridCfg']['dockedItems'])){
					$config['gridCfg']['dockedItems']=  array( );
				}
				$config['gridCfg']['dockedItems'][]=  array( 'xtype'=> 'pagingtoolbar',                      
                    'store' =>  new Zend_Json_Expr("ext_form_{$config['name']}_store"), 
                    'dock' => 'bottom',   
                    'displayInfo'=> true
                );
			}

			if(@$config['multiSelect']){
				unset($config['typeAhead']);
				$config['gridCfg']['selModel']=new Zend_Json_Expr("Ext.create('Ext.selection.CheckboxModel')");
			}
			//echo __FILE__.__LINE__.'<pre>';print_r($item);print_r($config);exit;
			
		}
		return $config;
	}
	
	public function __toString()
	{
		
		
		if(!$this->tabs){
			//echo '</script><pre>'.__FILE__.__LINE__;print_r($this->columns);exit;
			//index_p as flag of form tree style.
			$Tree=new Baogg_Db_Tree();
			foreach((array)$this->columns as $k=>$v){				
				if(!isset($flag_is_tree)){
					$flag_is_tree=array_key_exists('index_p', @$v['items'][0]);
					if(!$flag_is_tree){
						break;
					}
				}
				$tr_v=$Tree->rs2GridTree($v['items']);
				$Tree->tr2Fieldset($tr_v);
				$this->columns[$k]['items']=$tr_v[0];
			}
			
			

			//echo '</script><pre>'.__FILE__.__LINE__;print_r($this->columns);
			$column_num=$this->form_config['column_num'];
			for($i=0;$i<$column_num;$i++)
			{
				$columnWidth=1.0/$column_num;
				$this->columns[$i]=array_merge(array("columnWidth"=>(float)sprintf("%0.1f",$columnWidth),"defaultType"=>"textfield","layout"=>"anchor"),(array)$this->columns[$i]);



				if( isset($this->form_config['height']) ){
					//$this->columns[$i]["height"]=$this->form_config['height'] - 40;
				}
			}
			//echo '</script><pre>'.__FILE__.__LINE__;print_r($this->columns);var_dump($column_num);exit;
			//$this->form_config['items']=array("xtype"=>"fieldset",'title'=>\Baogg\Language::get('details'),'autoHeight'=>true,"items"=>array("layout"=>"column",									 		"items"=>$this->columns));
			$this->form_config['items']=$this->columns;
			
		}else{
			$i_column=0;
			
			foreach($this->tabs['items'] as $k=>$tab)
			{	
				for($i=0;$i<$tab['column_num'];$i++)
				{	
					$columnWidth=1.0/$tab['column_num'];
					$this->columns[$i_column]=array_merge((array)(@$this->columns[$i_column]),array("columnWidth"=>(float)sprintf("%0.1f",$columnWidth),
															"layout"=>"anchor",
															"height"=>$this->form_config['height'],
															"items"=>array()));				
					$this->tabs['items'][$k]['items'][]=$this->columns[$i_column];
					$i_column++;
				}
				
			}
			$this->form_config['items']=$this->tabs;
		}
		
		if(isset($this->form_config['region'])){
			$this->win_config['region']=$this->form_config['region'];
			unset($this->form_config['region']);
		}
		$this->win_config['tbar']=$this->buttons;
		
		
		if(isset($this->form_config['width'])){
			$this->win_config['width']=$this->form_config['width']+25;
			unset($this->form_config['width']);	
		}
		if(isset($this->form_config['height'])){
			$this->win_config['height']=$this->form_config['height']+25;
			unset($this->form_config['height']);
		}		
		
		
		if(isset($this->form_config['url'])){
			$this->form_config['url']=Baogg_Controller_Url::generate($this->form_config['url']);
		}
		unset($this->form_config['column_num']);		
			
		$this->win_config['items']=$this->form_config;
		
		//unset($this->win_config['items'][0]['id']);

		//legacy comptible,
		$ret="{});\n";
		foreach($this->column_stores as $field=>$store){
			$ret.="var ext_form_{$field}_store=".Zend_Json::encode($store, false, array('enableJsonExprFinder' => true)).";\n";
		}
		$ret.='var win_form = Ext.create("Ext.window.Window",'.Zend_Json::encode($this->win_config, false, array('enableJsonExprFinder' => true));

		return $ret;
	}
	
	protected function getButtonFn($config)
	{		
		if(isset($config['params'])){
			$config['params']=Zend_Json::encode($config['params'],false, array('enableJsonExprFinder' => true));
		}else{
			$config['params']="{}";
		}
		if(isset($config['url'])){			
			$config['url']=is_a($config['url'],'Zend_Json_Expr')?	$config['url']:'"'.$config['url'].'"';
			$config['url']=' url:'.$config['url'].',';
		}else{
			$config['url']='';
		}
		return 'Ext.getCmp("ext_form").getForm().submit({
							    clientValidation: true,	
							    method:"POST",			  
							   '.$config['url'].'
							  	params:'.$config['params'].',
							    success: function(form, action) {					
							       '.$config['fn'].'				
							    },
							    failure: function(form, action) {
									switch (action.failureType) {
									    case Ext.form.Action.CLIENT_INVALID:
											top.Ext.ux.ShowError("'.\Baogg\Language::get("msg_form_invalid_value").'");
											break;
									    case Ext.form.Action.CONNECT_FAILURE:
											top.Ext.ux.ShowError("'.\Baogg\Language::get("msg_ajax_failed").'" );
											break;
									    case Ext.form.Action.SERVER_INVALID:
									       top.Ext.ux.ShowError( action.result.msg);
									       break;
									    default:
									      top.Ext.ux.ShowError(action.result.msg);
									      break;
								       }
								    }
							});';
		
	}
	function getConfirmFn($config)
	{
		if(isset($config['url'])){			
			$config['url']=is_a($config['url'],'Zend_Json_Expr')?	$config['url']:'"'.$config['url'].'"';
			$config['url']=' url:'.$config['url'].',';
		}else{
			$config['url']='';
		}
		
		if(isset($config['params'])){
			if(is_array($config['params'])){
				$config['params']=Zend_Json::encode($config['params'],false, array('enableJsonExprFinder' => true));
				$config['url']=' url:'.$config['url'].',';
			}
		}else{
			$config['params']="{}";
		}
		return 'Ext.ux.confirm( "'.$config['confirm'].'", function(buttonId, text, opt){
	                         if (buttonId == "yes") {
	                          
								form.getForm().submit({
								    clientValidation: true,				  
								  	'.$config['url'].'
								  	params:'.$config['params'].',						  	
								    success: function(form, action) {					
								       '.$config['fn'].'				
								    },
								     failure: function(form, action) {								       
										switch (action.failureType) {
										    case Ext.form.Action.CLIENT_INVALID:
												top.Ext.ux.ShowError("'.\Baogg\Language::get("msg_form_invalid_value").'");
												break;
										    case Ext.form.Action.CONNECT_FAILURE:
												top.Ext.ux.ShowError("'.\Baogg\Language::get("msg_ajax_failed").'" );
												break;
										    case Ext.form.Action.SERVER_INVALID:
										       top.Ext.ux.ShowError( action.result.msg);
										       break;
										    default:
										      top.Ext.ux.ShowError( action.result.msg);
										      break;
										}
									 }
								});
	                         }
	                   	 });';
		
	}
	
	/**
	 * 
	 * @param array button name $btns
	 * @param array row button,enabled when have primary key  $row_btns
	 */
	public function addExistButtons($btns=array(),$row_btns=array())
	{
		foreach((array)$btns as $btn){				
			$this->addExistButton($btn,in_array($btn,$row_btns));
		}
	}
	public function addExistButton($btn='',$is_row_btn)
	{
		if(in_array($btn,array(" ","-","->"))){			
				$this->buttons[]=$btn;
				return;	
		}
			
		$permission=new Permission();
		$params=Zend_Controller_Front::getInstance()->getRequest()->getParams();
		$params['action']=$btn;
		if(in_array($btn,array('search','add','edit','delete','remove')) && !$permission->checkButton($btn)){	
			return ;
		}
		
		$cls=$is_row_btn?'baogg-btn-row-cls baogg-btn-row-'.$btn.'-cls':'baogg-btn-cls baogg-btn-'.$btn.'-cls';
		$uncls=$is_row_btn?'baogg-btn-row-disabled-cls baogg-btn-row-disabled-'.$btn.'-cls':'baogg-btn-disabled-cls baogg-btn-'.$btn.'-disabled-cls';
		$disabled=$is_row_btn;
		$default_btn=array("name"=>$btn,'disabled'=>$disabled,'cls'=>$cls,'disabledClass'=>$uncls);
			
		switch($btn)
		{
			case 'search':											
				$btn_config=array('handler'=>'grid.getStore().getProxy().extraParams  = Ext.apply(form.getForm().getFieldValues(),{query:"",start:0,limit:'.Baogg_View_Extgrid::$PAGE_SIZE.'});grid.getStore().reload();');				
				break;
			case 'search1':
				/* var form_values=Ext.getCmp("ext_form").getForm().getFieldValues();
				
				
				grid.getStore().proxy.extraParams=form_values;grid.getStore().load({
					params:Ext.apply(form_values,{
						query:"",start:0,limit:'.Baogg_View_Extgrid::$PAGE_SIZE.'})
						, callback: function(records, operation, success) {
							//grid.suspendEvents(true);
							Ext.Array.each(grid.columns, function(name, index, countriesItSelf){
								if(name.dataIndex &&  name.dataIndex!=""){
									name.setVisible(form_values[name.dataIndex] && form_values[name.dataIndex] != "");
									//name.doLayout( );
				
								}else{
									name.setVisible(false);
									Ext.Array.each(name.getGridColumns(), function(sub_name, sub_index, sub_countriesItSelf){
										//var matches; matches=/rule\_id\_(\d+)/gi.exec(sub_name.dataIndex)
										if (sub_name.dataIndex.indexOf("rule_id_")===0) {
											 
											var rule_id=sub_name.dataIndex.substring(8); //rule_id_  length is 8
											var arr_brand_rule=json_rule[rule_id];
											var flag_brand=!form_values.qa_brand_id || form_values.qa_brand_id == "" || form_values.qa_brand_id == "#ALL#" || form_values.qa_brand_id==arr_brand_rule.qa_brand_id;
											var flag_qa_rule=!form_values.qa_rule_id || form_values.qa_rule_id == "" || form_values.qa_rule_id == "#ALL#"  || form_values.qa_rule_id==arr_brand_rule.qa_rule_id;
											if (flag_brand && flag_qa_rule) {
												sub_name.setVisible(true);
												sub_name.ownerCt.setVisible(true);
				
												//sub_name.doLayout();
												//sub_name.ownerCt.doLayout();
											}else{
												sub_name.setVisible(false);
											}
										}else{
											if (!form_values[sub_name.dataIndex] || form_values[sub_name.dataIndex] == "") {
												sub_name.setVisible(false);
											}else{
												sub_name.setVisible(true);
												//sub_name.doLayout();
											}
										}
									})
								}
							});//grid.resumeEvents();grid.doLayout();grid.getView().refresh()
						}
				}); */
				$btn_config=array('handler'=>'Ext.getCmp("ext_form").getEl().dom.target="report_content";form.getForm().standardSubmit=true;form.getForm().submit({method:"POST",target:"report_content" });if(form.getForm().isValid()){win_form.hide();}');
				break;
				 
			case 'add':				
				$btn_config=array("text"=>\Baogg\Language::get('create'),'fn'=>'top.Ext.ux.ShowResult(action.result);grid.getStore().load();');
				break;
			case 'edit':
				$btn_config=array("text"=>\Baogg\Language::get('update'),'fn'=>'top.Ext.ux.ShowResult(action.result);grid.getStore().load();');
				break;
			case 'delete':
				//echo '<pre>';var_dump($this->form_config['url']);exit;		
				$btn_config=array("url"=>Baogg_Controller_Url::generate(array_merge($this->form_config['url'],array('action'=>'delete'))),'params'=>array("ids"=>new Zend_Json_Expr('Ext.getCmp("'.$this->hidden_col.'").getValue()')),"confirm"=>\Baogg\Language::get('msg_confrim_delete'),'fn'=>'top.Ext.ux.ShowResult(action.result);grid.getStore().load();');
				break;
			case 'remove':
				$url=$this->form_config['url'];
				$url['action']=$btn;
				$url=Baogg_Controller_Url::generate($url);
				$btn_config=array("text"=>\Baogg\Language::get('delete_selected'),'handler'=>'grid.deleteSelected("'.$url.'"));');
				break;
			case 'reset':
				$btn_config=array('handler'=>'form.getForm().reset();Ext.select(".baogg-btn-row-cls").each(function(el,c,idx){ Ext.getCmp(el.id).setDisabled(true);})');
				break;
			case 'model_field':			
				$Menu=new Menu();
				$arr_menu=$Menu->getByKey("model_field");
				if(!$permission->checkUrl($arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/")){
					return ;
				}
				$arr_menu["url"]=new Zend_Json_Expr('"'.$arr_menu['url'].'relation_id/"+Ext.getCmp("relation_id").getValue()+"/model_id/"+Ext.getCmp("model_id").getValue()');				
				$btn_config=array('handler'=>'top.addTab('.Zend_Json::encode($arr_menu, false, array('enableJsonExprFinder' => true)).')');
				break;
			case 'user_group_role':				
				$Menu=new Menu();
				$arr_menu=$Menu->getByKey('user_group_role');
				if(!$permission->checkUrl($arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/")){
					return ;
				}				
				$btn_config=array("handler"=>'(new Ext.ux.Dialog({id:"user_group_role_"+Ext.getCmp("user_id").getValue(),url:"'.$arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/".'user_id/"+grid.getStore().getRow(rowIndex).id,width:880,height:500,modal:true})).show();');
				break;
			case 'resource_op':
				$Menu=new Menu();
				$arr_menu=$Menu->getByKey("resource_op");
				if(!$permission->checkUrl($arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/")){
					return ;
				}
				$arr_menu["url"]=new Zend_Json_Expr('"'.$arr_menu['url']."menu_id/{$arr_menu['menu_id']}/".'resource_id/"+Ext.getCmp("resource_id").getValue()');
				$btn_config=array("handler"=>'top.addTab('.Zend_Json::encode($arr_menu, false, array('enableJsonExprFinder' => true)).')');
				break;
			case 'role_resource':
				
				$Menu=new Menu();
				$arr_menu=$Menu->getByKey('role_resource');
				if(!$permission->checkUrl($arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/")){				
					return ;
				}
				$btn_config=array("handler"=>'(new Ext.ux.Dialog({id:"resource_role_win",url:"'.$arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/".'/resource_id/"+Ext.getCmp("resource_id").getValue(),width:800,height:500,modal:true})).show();');
				break;
			case 'save_grid':
				$btn_config=array('fn'=>'top.Ext.ux.ShowSuccess(action.result.msg);grid.getStore().load({callback:function(){ if(action.result.success){setTimeout(function(){top.Ext.ux.HideMsg();},100); }}});Ext.getCmp("win_form").hide();');
				break;
			case 'save_new':
				$btn_config=array('fn'=>'top.Ext.ux.ShowSuccess(action.result.msg);grid.getStore().load({callback:function(){ if(action.result.success){setTimeout(function(){top.Ext.ux.HideMsg();},100); }}});Ext.getCmp("ext_form").getForm().reset();');
				break;
			case 'save':
				$btn_config=array('fn'=>'top.Ext.ux.ShowSuccess(action.result.msg);grid.getStore().load({callback:function(){ if(action.result.success){setTimeout(function(){top.Ext.ux.HideMsg();},100); }}});');
				break;
			case 'cancel':
				$btn_config=array('handler'=>'win_form.hide();');
				break;					
			default:
				throw new Exception("Your button type is not supported yet!");
				break;
		}
		$btn_config=array_merge($default_btn,$btn_config);
		$this->addButton($btn_config);
	}
	
	public function getConfig()
	{
		return $this->form_config;
	}
	
}