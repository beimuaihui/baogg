<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Grid.php 465 2012-02-12 05:33:23Z beimuaihui $
 */
namespace Baogg\View;
class Extgrid {

    private $_cm = array();

    private $_tbar = array();

    private $_actionColumn = array();

    private $_isSelectionModel = false;

    private $_isRowNumberer = false;

    private $_isRowEditing = false;

    private $_expander = false;

    private $_jsonUrl = "";

    private $_storePk = "";

    private $_id = "grid";

    private $_url = array();

    private $_groupField = '';

    private $_groupFieldTpl = '';

    private $_auto_load_store = true;

    private $_disabled = false;

    const CMTYPE_PK = 1;

    const CMTYPE_SUBTABLE = 2;

    public static $PAGE_SIZE = 25;
    //public static $AUTO_LOAD_STORE=true;

    private $_store_params = array();

    public function __construct ($url, $cm)
    {
        $this->_url = $url;
        
        $url2 = Baogg_Controller_Url::generate($url);
        $this->_id = preg_replace("/\W/", "", $url2);
        $this->_jsonUrl = $url2;
        $this->convertCm($cm);
        $this->_store_params = array('limit' => self::$PAGE_SIZE);
    }

    public function registerActionColumn ($name, $fn, $params = array(), $checked_permission = false)
    {
        $permission = new Permission();
        /*if($name =="report_xls"){
            echo __FILE__.__LINE__.'<pre>';var_dump(! $checked_permission && ! $permission->checkButton($name));exit;
        }*/
        if (! $checked_permission && ! $permission->checkButton($name)) {
            return;
        }
        $fn = "function(grid,rowIndex,colIndex) {{$fn}}";
        // echo $fn;exit;
        if (! isset($this->_actionColumn['items'])) {
            $this->_actionColumn['items'] = array();
        }
        $item = array("iconCls" => $name . "-icon","id" => "btn_" . $name,"tooltip" => \Baogg\Language::get($name),"altText" => \Baogg\Language::get($name),
                "handler" => new Zend_Json_Expr($fn));
        if (isset($params) && $params) {
            $item = array_merge($item, (array) $params);
        }
        $this->_actionColumn['items'][] = $item;
    }

    public function unregisterActionColumn ($name)
    {
        if (! isset($this->_actionColumn['items'])) {
            return;
        }
        unset($this->_actionColumn['items'][$name]);
    }

    public function registerButton ($text, $fn, $withSplit = true)
    {
        $permission = new Permission();
        if (! $permission->checkButton($text)) {
            return;
        }
        if ($withSplit) {
            $this->registerSplit();
        }
        @$this->_tbar[$text] = "{iconCls:'{$text}-icon',text:'" . \Baogg\Language::get($text) . "',handler:function(btn,ev){{$fn}}}";
    }

    public function registerControl ($js, $label = '', $withSplit = true)
    {
        if ($withSplit) {
            $this->registerSplit();
        }
        if ($label) {
            $this->registerHtml($label);
        }
        if (is_array($js)) {
            $js = Zend_Json::encode($js, false, array('enableJsonExprFinder' => true));
        }
        $this->_tbar[] = $js;
    }
    
    // Ext.getCmp("grid").getTopToolbar().addText("<select><option>1</option></select>");
    public function registerHtml ($html, $k = '')
    {
        if ($k) {
            $this->_tbar[$k] = '"' . addcslashes($html, '"') . '"';
        } else {
            $this->_tbar[] = '"' . addcslashes($html, '"') . '"';
        }
    }

    public function registerSplit ()
    {
        $this->registerHtml('-');
    }

    public function registerForm($form){
        if(!$form || !$form instanceof Baogg_VIew_Extform  || !$form->getColumns() || !is_array($form->getColumns()) ) {
            return false;
        }
        
        $columns = array_values($form->getColumns());

        foreach($column as $col=>$v){
            foreach($v['items'] as $sub_k=>$form_item){
                $col = isset($form_item['name'])?$form_item['name']:(isset($form_item['id'])?$form_item['id']:'');
                if($col){
                    continue;
                }
                if(isset($this->_cm[$col])){
                    $this->_cm[$col]['editor'] = $form_item;
                    $this->_isRowEditing = true;
                }
            }
        }

        return $this->_isRowEditing;
    }

    public function addExistButtons ($arr, $arr_rows = array())
    {
        foreach ((array) $arr as $v) {
            switch ($v) {
                case 'add':
                    $this->_url['action'] = "add";
                    $this->registerButton("add", 
                            'Ext.getCmp("ext_form").getForm().reset();Ext.getCmp("ext_form").getForm().url="' . Baogg_Controller_Url::generate($this->_url) .
                                     '";Ext.getCmp("win_form").show().center();');
                    break;
                case 'remove':
                    $this->_url['action'] = "remove";
                    $this->registerButton("remove", 
                            'grid.deleteSelected("' . Baogg_Controller_Url::generate($this->_url) . '");');
                    break;
                case 'query':
                    $this->registerControl(
                            array('xtype' => 'textfield','name' => 'query','id' => 'query',
                                    'listeners' => array(
                                            "specialkey" => new Zend_Json_Expr(
                                                    "function(self,e){ if (e.getKey() == e.ENTER) {if (e.getKey() == e.ENTER) {form.getForm().reset();var els=form.query(\"textfield\");for(var i=0,cnt=els.length;i<cnt;i++){els[i].setRawValue(\"#\"+self.getValue().trim()+\"#\");}; Ext.StoreMgr.get('gridStore').getProxy().extraParams  = Ext.applyIf(form.getForm().getFieldValues(), {  query: self.getValue(),start:0,limit:" .
                                                             (self::$PAGE_SIZE) . " }); grid.getStore().load();     };}}"))), Baogg_Language::get("search") . ':');
                    break;
                case 'search':
                    $this->registerButton('search', 'Ext.getCmp("win_form").show().center();');
                    break;
                case 'xls':
                    $this->registerButton('xls', "var vExportContent = grid.getExcelXml();
                                //获取数据
                                if (true || Ext.firefoxVersion == 0) {//判断浏览器

                                    var fd = Ext.get('formExportExcelAction');
                                    if (!fd) {
                                        fd = Ext.DomHelper.append(Ext.getBody(), {
                                            tag : 'form',
                                            method : 'post',
                                            id : 'formExportExcelAction',
                                            action : '".BAOGG_BASE_URL."common/content/filecontent/format/xls/',
                                            target : '_blank',
                                            name : 'formExportExcelAction',
                                            cls : 'x-hidden',
                                            cn : [{
                                                tag : 'input',
                                                name : 'exportContent',
                                                id : 'exportContent',
                                                type : 'hidden'
                                            }]
                                        }, true);

                                    }
                                    fd.child('#exportContent').set({
                                        value : vExportContent
                                    });
                                    fd.dom.submit();
                                } else {
                                    document.location = 'data:application/vnd.ms-excel;base64,' + Base64.encode(vExportContent);
                                }");
                    break;
                
                 case 'pdf':
                    $this->registerButton('pdf', "var vExportContent = '<style type=\"text/css\">table{font-size:9px;border-collapse:collapse;border: 1px solid black;} td,th{border:1px solid black;}</style>'+grid.getHtmlContent();
                                //获取数据
                                if (true || Ext.firefoxVersion == 0) {//判断浏览器

                                    var fd = Ext.get('formExportPdfAction');
                                    if (!fd) {
                                        fd = Ext.DomHelper.append(Ext.getBody(), {
                                            tag : 'form',
                                            method : 'post',
                                            id : 'formExportPdfAction',
                                            action : '".BAOGG_BASE_URL."common/content/filecontent/format/pdf/',
                                            target : '_blank',
                                            name : 'formExportPdfAction',
                                            cls : 'x-hidden',
                                            cn : [{
                                                tag : 'input',
                                                name : 'exportContent',
                                                id : 'exportContent',
                                                type : 'hidden'
                                            }]
                                        }, true);

                                    }
                                    fd.child('#exportContent').set({
                                        value : vExportContent
                                    });
                                    fd.dom.submit();
                                } else {
                                    document.location = 'data:application/pdf;base64,' + Base64.encode(vExportContent);
                                }");
                    break;

                default:
                    throw new Exception("Your button type is not supported yet!");
                    break;
            }
        }
        // echo __FILE__.__LINE__.'<pre>';print_r($arr_rows);exit;
        foreach ((array) $arr_rows as $v) {
            switch ($v) {
                case 'edit':
                    $this->_url['action'] = "edit";
                    $url = Baogg_Controller_Url::generate($this->_url);
                    $this->registerActionColumn("edit", 
                            'grid.getSelectionModel().selectRange( rowIndex,rowIndex);Ext.getCmp("ext_form").getForm().loadRecord(store.getAt(rowIndex));Ext.getCmp("win_form").show().center();Ext.getCmp("ext_form").getForm().url="' .
                                     $url . '";');
                    break;
                case 'delete':
                    $this->_url['action'] = "delete";
                    $delete_fn = $this->getConfirmFn(
                            array("url" => Baogg_Controller_Url::generate($this->_url),
                                    "params" => array(
                                            "ids" => new Zend_Json_Expr('store.getAt(rowIndex).getId()')),
                                    "confirm" => \Baogg\Language::get('msg_confrim_delete'),
                                    'fn' => 'top.Ext.ux.ShowResult( result); grid.getStore().load({callback:function(){ if(result.success){setTimeout(function(){top.Ext.ux.HideMsg();},1000); }}});'));
                    $this->registerActionColumn("delete", 
                            'grid.getSelectionModel().selectRange( rowIndex,rowIndex);' . $delete_fn); // $this->language->_primary??
                    break;
                 case 'copy':
                    $this->_url['action'] = "add";
                     $this->_url['task'] = "copy";
                    $url = Baogg_Controller_Url::generate($this->_url);
                    $this->registerActionColumn("copy", 
                            'grid.getSelectionModel().selectRange( rowIndex,rowIndex);Ext.getCmp("ext_form").getForm().loadRecord(store.getAt(rowIndex));Ext.getCmp("win_form").show().center();Ext.getCmp("ext_form").getForm().url="' .
                                     $url . '";');
                    unset($this->_url['task']);
                    break;

                case 'run':
                    $this->_url['action'] = "run";
                   
                    $run_fn = $this->getConfirmFn(
                            array("url" => Baogg_Controller_Url::generate($this->_url),
                                    "params" => array(
                                            "ids" => new Zend_Json_Expr('store.getAt(rowIndex).getId()')),
                                    "confirm" => \Baogg\Language::get('msg_confrim_run'),
                                    'fn' => 'top.Ext.ux.ShowResult( result);grid.getStore().load({callback:function(){ if(result.success){setTimeout(function(){top.Ext.ux.HideMsg();},1000); }}});'));
                    $this->registerActionColumn("run", 
                            'grid.getSelectionModel().selectRange( rowIndex,rowIndex);' . $run_fn); // $this->language->_primary??
                    
                    break;

                case 'gen':
                    $this->_url['action'] = "gen";
                    $delete_fn = $this->getConfirmFn(
                            array("url" => Baogg_Controller_Url::generate($this->_url),
                                    "params" => array(
                                            "ids" => new Zend_Json_Expr('store.getAt(rowIndex).getId()')),
                                    "confirm" => \Baogg\Language::get('msg_confrim_gen'),
                                    'fn' => 'top.Ext.ux.ShowResult( result); if(result.success){setTimeout(function(){top.Ext.ux.HideMsg();},1000); };'));
                    $this->registerActionColumn("gen", 
                            'grid.getSelectionModel().selectRange( rowIndex,rowIndex);' . $delete_fn); // $this->language->_primary??
                    break;
                case 'model_field': // legacy
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu['url'] . 'relation_id/' . (int) @$this->_url['relation_id'] . '/model_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'relation_in_model':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu['url'] . 'model_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'model_relation':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu['url'] . 'model_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'store_info':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $Category = new Category();
                    $rs_category = $Category->getList(array("category_pid" => 0,"state" => 1));
                    $category_id = @$rs_category[0]['category_id'];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu['url'] . 'category_id/' . $category_id . '/store_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'user_group_role':
                    $url_params = array();
                    $Resource = new Resource();
                    $rs_subject = $Resource->getList(array('resource_code' => 'user'));
                    $url_params['subject_id'] = @$rs_subject[0]['resource_id'];
                    $rs_resource = $Resource->getList(array('resource_code' => 'role'));
                    $url_params['resource_id'] = @$rs_resource[0]['resource_id'];
                    
                    $this->registerMenuActionColumn(
                            array('key' => $v,'key_col' => 'subject_row_id','url_params' => $url_params));
                    break;
                
                case 'resource_op':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu["url"] . 'resource_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'op_in_resource':
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'resource_id'));
                    break;
                case 'role_resource':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu["url"] . 'resource_id/"+grid.getStore().getAt(rowIndex).getId()');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'model_detail':
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);
                    // echo '<pre>';print_r($arr_menu);exit;
                    $permission = new Permission();
                    if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
                        continue;
                    }
                    $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
                    $arr_menu["url"] = new Zend_Json_Expr(
                            '"' . $arr_menu["url"] . 'model_id/"+grid.getStore().getAt(rowIndex).get("model_id")');
                    
                    $this->registerActionColumn($v, 
                            'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, 
                                    array('enableJsonExprFinder' => true)) . ')', array('icon' => $arr_menu["img_icon"]), true);
                    break;
                case 'user_permission':
                    // echo __FILE__.__LINE__.$v;exit;
                    $url_params = array();
                    $Resource = new Resource();
                    $rs_subject = $Resource->getList(array('resource_code' => 'user'));
                    $url_params['subject_id'] = @$rs_subject[0]['resource_id'];
                    $rs_resource = $Resource->getList(array('resource_code' => 'qa_surveys'));
                    $url_params['resource_id'] = @$rs_resource[0]['resource_id'];
                    
                    $this->registerMenuActionColumn(
                            array('key' => $v,'key_col' => 'subject_row_id','url_params' => $url_params));
                    break;
                case 'role_permission':
                    $url_params = array();
                    $Resource = new Resource();
                    $rs_subject = $Resource->getList(array('resource_code' => 'role'));
                    $url_params['subject_id'] = @$rs_subject[0]['resource_id'];
                    $rs_resource = $Resource->getList(array('resource_code' => 'menu'));
                    $url_params['resource_id'] = @$rs_resource[0]['resource_id'];
                    
                    $this->registerMenuActionColumn(
                            array('key' => $v,'key_col' => 'subject_row_id','url_params' => $url_params));
                    break;
                case 'qa_question2':
                case 'qa_brand':
                case 'qa_rule':
                case 'qa_brand_rule':
                case 'qa_report_category':
                case 'qa_report':
                case 'reports_view':
                    // echo $v;exit;
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'qa_surveys_id'));
                    break;
                case 'attachment':
                    // echo $v;exit;
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'attachment_type_id'));
                    break;
                 case 'widget_value':
                    // echo $v;exit;
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'widget_entity_id'));
                    break;
                case 'report_data':
                     $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'report_id'));
                    break;
                case 'report_attachment':
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'key_id'));
                    break;

                case 'report_view':
                    // echo $v;exit;
                    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
                    // echo '<pre>';print_r($params);exit;
                    $url_params = array('qa_report_id' => @$params['qa_report_id']);
                    $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'id','url_params' => $url_params));
                    break;
                case 'xls':
                case 'pdf':
                  //  $this->registerMenuActionColumn(array('key' => $v,'key_col' => 'widget_entity_id'));
                    $Menu = new Menu();
                    $arr_menu = $Menu->getByKey($v);                   
                    $menu_id = Zend_Controller_Front::getInstance()->getRequest()->getParam(BAOGG_PARAM_MENU);

                    //echo __FILE__.__LINE__.'<pre>';var_dump(Zend_Controller_Front::getInstance()->getRequest()->getParams());print_r($arr_menu);var_dump("window.location.href='{$arr_menu['url']}/menu_id/{$menu_id}';");exit;

                    $this->registerActionColumn($v,"window.location.href=\"{$arr_menu['url']}menu_id/{$menu_id}/\"+store.model.prototype.idProperty+\"/\"+store.getAt(rowIndex).getId()+\"/\";"
                        );
                    break;
               
                default:
                    throw new Exception("Your menu type button  is not supported yet!");
                    break;
            }
        }
    }

    /**
     *
     *
     *
     *
     * menu as action button
     *
     * @param
     *            array,key,key_col at least,url_param is plus $params
     */
    private function registerMenuActionColumn ($params = array())
    {
        $key = @$params['key'];
        $key_column = @$params['key_col'];
        $url_params = (array) @$params['url_params'];
        
        $Menu = new Menu();
        $arr_menu = $Menu->getByKey($key);
         //echo __FILE__.__LINE__.'<pre>';print_r($arr_menu);exit;
        $permission = new Permission();
        /*
         * if($key=='qa_question2'){ echo __FILE__.__LINE__.'<pre>';
         * var_dump($arr_menu['menu_id']);
         * var_dump($permission->checkUrl($arr_menu["url"]."menu_id/{$arr_menu['menu_id']}/"));
         * exit; }
         */
        if (! $permission->checkUrl($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/")) {
            return;
        }
        $arr_menu["img_icon"] = BAOGG_FILE_URL . 'image/menu/' . $arr_menu["img_icon"];
        
        $url = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $url = array_merge($url, Baogg_Controller_Url::reverse($arr_menu["url"] . "menu_id/{$arr_menu['menu_id']}/"), $url_params);
        // echo '<pre>'.__FILE__;print_r($url);exit;
        $url = Baogg_Controller_Url::generate($url);
        $url .= $key_column;
        $arr_menu["url"] = new Zend_Json_Expr('"' . $url . '/"+grid.getStore().getAt(rowIndex).getId()');
        
        $this->registerActionColumn($key, 
                'top.addTabByMenu(' . Zend_Json::encode($arr_menu, false, array('enableJsonExprFinder' => true)) . ')', 
                array('icon' => $arr_menu["img_icon"],'tooltip' => $arr_menu["menu_name"],'altText' => $arr_menu["menu_name"]), true);
    }

    function getConfirmFn ($config)
    {
        $config['url'] = ($config['url'] instanceof Zend_Json_Expr) ? $config['url'] : '"' . $config['url'] . '"';
        if (isset($config['params'])) {
            if (is_array($config['params'])) {
                $config['params'] = Zend_Json::encode($config['params'], false, array('enableJsonExprFinder' => true));
            }
        } else {
            $config['params'] = "{}";
        }
        return 'Ext.ux.confirm( "' . $config['confirm'] . '", function(buttonId, text, opt){
					if (buttonId == "yes") {
							if(typeof(myMask) !== "undefined" && myMask){
								myMask.show();
							}
							Ext.Ajax.request({
							   url:' . $config['url'] . ',
							    params:' . $config['params'] . ',
							    success:  function(response, opts) {							    	
							    	var result=Ext.decode(response.responseText);	
							    	 ' . $config['fn'] . '						      				
							    },
							     failure: function(response, opts) {
							     	 top.Ext.example.msg("' . \Baogg\Language::get("failure") . '", "' .
                 \Baogg\Language::get("msg_ajax_failed") . '");
							     }				  
							});
						}
				});';
    }

    public function unregister ($k)
    {
        if (! $k || ! isset($this->_tbar[$k])) {
            return false;
        } else {
            unset($this->_tbar[$k]);
            return true;
        }
    }

    public function getId ()
    {
        return $this->_id;
    }

    function getSelected ()
    {
        return "function(){var s=[];Ext.each( Ext.getCmp('{$this->_id}').getSelectionModel().getSelection(),function(item,index,items){s[s.length]=item.get('{$this->_storePk}');});return (s.join(','));}";
    }

    public function render ()
    {
        $arr_cm = $this->_cm;
        $arr_store_field = array();
        foreach ($arr_cm as $col => $v) {
            if (isset($v['columns']) && is_array($v['columns'])) {
                foreach ($v['columns'] as $sub_col) {
                    $arr_store_field[] = @$sub_col['dataIndex'];
                }
            } else {
                $arr_store_field[] = $col;
            }
        }
        $jsField = Zend_JSON::encode($arr_store_field);
        $arr_cm = array_values($arr_cm);
        // render action column
        if ($this->_actionColumn) {
            $this->_actionColumn['xtype'] = 'actioncolumn';
            $this->_actionColumn['width'] = isset($this->_actionColumn['width']) ? $this->_actionColumn['width'] : count($this->_actionColumn['items']) * 23;
            $arr_cm[] = $this->_actionColumn;
        }
        
        $jsCm = Zend_JSON::encode($arr_cm, false, array('enableJsonExprFinder' => true));
        // echo '<pre>';print_r($arr_cm);echo $jsCm;exit;
        
        if ($this->_isRowNumberer) {
            $jsCm = substr_replace($jsCm, "Ext.create('Ext.grid.RowNumberer'),", 1, 0);
        }
        
        $jsSm = "";
        $gridSm = "";
        if ($this->_isSelectionModel) {
            
            // $jsSm="var sm = ;";
            $gridSm = "selModel: Ext.create('Ext.selection.CheckboxModel'),";
            // $jsCm=substr_replace($jsCm,"sm,",1,0);
        }
        if($this->_isRowEditing){
            $gridSm .= "plugins: [
                            Ext.create('Ext.grid.plugin.RowEditing', {
                                clicksToEdit: 1
                            })
                        ],";
        }
        
        $jsTBar = '';
        if ($this->_tbar && is_array($this->_tbar)) {
            $this->addExistButtons(array('xls','pdf'),array());
            $jsTBar = "{xtype:'toolbar',dock:'top', items:[" . implode(",", $this->_tbar) . "]},";
        }
        
        $jsStoreGroupField = '';
        $jsGroupField = '';
        if ($this->_groupField) {
            $jsStoreGroupField = "groupField: '{$this->_groupField}',";
            $jsGroupField = "features: [Ext.create('Ext.grid.feature.Grouping',{
						        groupHeaderTpl: ' {$this->_groupFieldTpl} ','hideGroupedHeader':true
						    })],";
        }
        $btnPreview = "";
        // echo
        // '<pre>';var_dump(in_array('sub_table',array_keys($arr_cm)));exit;
        if (in_array('sub_table', array_keys($arr_cm), true)) {
            $btnPreview = ", {
                pressed: false,
                enableToggle:true,
                text: LANG.show_detail,
                cls: 'x-btn-text-icon details',
                toggleHandler: function(btn, pressed){
                    var view = grid.getView();
                    view.showPreview = pressed;
                    view.refresh();
                }
            }";
        }
        
        $store_params = Zend_Json::encode($this->_store_params);
        
        $ret = "
			
	Ext.define('GridModel', {
		extend: 'Ext.data.Model',
		idProperty: '{$this->_storePk}',
		fields: {$jsField}
	});
    // create the Data Store
    var store =  Ext.create('Ext.data.Store',{ 
		storeId:'gridStore',
		 pageSize: " . self::$PAGE_SIZE . ",   
        // buffered: true,
        //  purgePageCount: 0,
		{$jsStoreGroupField}     
        remoteSort: true,
        model:'GridModel',
        // load using script tags for cross domain, if the data in on the same domain as
        // this page, an HttpProxy would be better
        proxy: {
            url: '{$this->_jsonUrl}',
            type: 'ajax',
             actionMethods: {
                create : 'POST',
                read   : 'POST',
                update : 'POST',
                destroy: 'POST'
			},
            reader: {
	            type: 'json',
				 totalProperty: 'count',
	            root: 'data'
	        }
        }/* ,
        listeners: {
            'beforeload':function(store, operation,eOpts){
                
             }
        } */
    });
    

	//msg:LANG.loading,
   // var myMask=new Ext.LoadMask(Ext.getBody(), {store:store });
	/*var cmb=Ext.create('Ext.form.ComboBox',{
                    store: store,
                    displayField: 'language_name',
                    typeAhead: true,
                    queryMode: 'local',
                    triggerAction: 'all',
                    emptyText: 'Select a state...',
                    selectOnFocus: true,
                    width: 135
                })*/
                
    		
    
    
	{$jsSm}
   
     var grid =Ext.create('Ext.grid.Panel',{
     	id:'{$this->_id}',
     	region:'center',     	
		columnLines: true,
		
      // el:document.body,
      // title:'Lanugage Management',
		//height:1000,
        store: store,
       disableSelection:false,
       enableColumnResize:true,
       // loadMask: true,
		listeners: {
			itemdblclick : function(self, r,item,rowIndex, e,opt) {
				//var r=self.getStore().getAt(rowIndex);
				//console.info(r);console.info(r.get('menu_pid'));
				form.getForm().loadRecord(r);

				var base_url_len=" . strlen(BAOGG_BASE_URL) . ";
				var form_url=Ext.getCmp('ext_form').getForm().url;
				Ext.getCmp('ext_form').getForm().url=form_url.substr(0,base_url_len)+changeUrl(form_url.substr(base_url_len),{action:'edit'})
				
				Ext.getCmp('win_form').show().center();
				Ext.select('.baogg-btn-row-cls').each(function(el,c,idx){ Ext.getCmp(el.id).setDisabled(false);});
			}
		},
        // grid columns       
     {$gridSm}
     //group 
     {$jsGroupField}
        columns:{$jsCm},
         cls: 'grid-row-span',	
         //forceFit:true,	
         scrollOffset: 0,
        // customize view config
        viewConfig: {
            
           stripeRows: true,
			trackOver:false,
            enableRowBody:true,
            showPreview:false,
            getRowClass : function(record, rowIndex, p, store){
                if(this.showPreview && record.data.sub_table){  //if set sub_table
                    p.body = '<p>'+record.data.sub_table+'</p>';
                    return 'x-grid3-row-expanded';
                }
                return 'x-grid3-row-collapsed';
            }
        },
		 dockedItems : [{$jsTBar} { xtype: 'pagingtoolbar',   
                    id : 'pt',   
                    store :  Ext.data.StoreManager.lookup('gridStore'), 
                    dock : 'bottom',   
                    displayInfo : true//, plugins : Ext.create('Ext.ux.ProgressBarPager', {})   
                }]
		
       
        
    });
	
     Ext.applyIf(grid, {
        getSelected: function(){
            var s = [];
            Ext.each(Ext.getCmp('{$this->_id}').getSelectionModel().getSelection(), function(item, index, items){
                s[s.length] = item.get('{$this->_storePk}');
            });
            return s.join(',');
        },
        deleteSelected: function(url){
            if (grid.getSelected() == '') {
            	 Ext.ux.ShowError({ msg: LANG.msg_select_item});            	
               	 return false;
            }
            
            
            
            Ext.ux.confirm(LANG.msg_confrim_delete, function(buttonId, text, opt){
                if (buttonId == 'yes') {
                    Ext.Ajax.request({
                        url: url,
                        success: function(res, opt){                 
                        	
							var result=Ext.decode(res.responseText,true);
							
                            top.Ext.ux.ShowResult(result);
                            grid.getStore().load({callback:function(){
								if (result.success) {
									setTimeout(function () {
										top.Ext.ux.HideMsg();
									}, 300);
								}
							}});
                            form.getForm().reset();
                        },
                        failure: function(res, opt){
                            top.Ext.ux.ShowError(LANG.msg_delete_failure);
                        },
                        params: {
                            ids: grid.getSelected()
                        }
                    });
                }
            });
        }
    });
    // render it
    //grid.render();

    // trigger the data store load
    store.proxy.extraParams={$store_params};    
    
         ";
         if($this->_auto_load_store){
            $ret .= "store.load({params:{$store_params}});";
         }
         if($this->_disabled){
            $ret .= 'grid.disable();';
         }
        return $ret;
    }

    public function setStoreParams ($params = array())
    {
        $this->_store_params = array_merge($this->_store_pararenderms, $params);
    }

    public function __toString ()
    {
        return $this->render();
    }

    public function setSelectionModel ($isSelection = false)
    {
        $this->_isSelectionModel = $isSelection;
    }

    public function setRowNumberer ($isRowNumberer = false)
    {
        $this->_isRowNumberer = $isRowNumberer;
    }

    public function convertCm ($cm)
    {
        if (! is_array($cm)) {
            $this->_cm = array();
        } else {
            $this->_cm = $cm;
        }
        
        foreach ($this->_cm as $k => $v) {
            $col = isset($v['dataIndex']) ? $v['dataIndex'] : $k;
            
            if (isset($v['cmType']) && $v['cmType'] == self::CMTYPE_PK) {
                
                $this->_storePk = $col;
            }
            if (isset($v['is_group']) && $v['is_group']) {
                
                $this->_groupField = $col;
                $this->_groupFieldTpl = isset($v['tpl']) ? $v['tpl'] : ' {name} ';
            }
            // $this->_cm[$k]['header']=isset($v['header'])?$v['header']:\Baogg\Language::get($k);
            $this->_cm[$k]['text'] = isset($v['text']) ? $v['text'] : (isset($v['header']) ? $v['header'] : \Baogg\Language::get($k));
            $this->_cm[$k]['dataIndex'] = $col;
            
            
            if (isset($v['PID']) && isset($this->_cm[$v['PID']])) {
                if (! isset($this->_cm[$v['PID']]['columns'])) {
                    $this->_cm[$v['PID']]['columns'] = array();
                }
                unset($this->_cm[$k]['PID']);
                $this->_cm[$v['PID']]['columns'][] = $this->_cm[$k];
                
                unset($this->_cm[$k]);
                unset($this->_cm[$v['PID']]['dataIndex']);
            } else {
                // $this->_cm[$k]=$v;
                if (isset($this->_cm[$k]['columns'])) {
                    unset($this->_cm[$k]['dataIndex']);
                } else {
                    //$this->_cm[$k]['flex'] = 1;
                }
            }
            
            // echo __FILE__.__LINE__.'<pre>';print_r($this->_cm);
        }
        // exit;
    }

    public function getCm ()
    {
        return $this->_cm;
    }




    public function setAutoLoadStore($flag){
        $this->_auto_load_store = $flag;
    }

    public function setDisabled($flag){
        $this->_disabled = $flag;
    }
}