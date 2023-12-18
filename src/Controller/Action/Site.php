<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id$
 */

//require_once 'Baogg/Controller/Action.php';

namespace Baogg\Controller\Action;
class Site extends Baogg\Controller\Action {	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
		parent::__construct($request, $response,$invokeArgs);
		Zend_Controller_Action_HelperBroker::addPrefix('Baogg_Controller_Action_Helper');
		
		Zend_Controller_Action_HelperBroker::addPath(BAOGG_ROOT .'site/Widgets','Widgets');
		$this->initCssFiles();
		$this->initJsFiles();
		$this->initMeta();
		foreach((array)$this->view->header as $k=>$v){
			$this->view->assign($k,$v);
		}
	}
	
	function initCssFiles() {
		$this->view->addCssFile ( BAOGG_CSS . '960/reset.css' );
		$this->view->addCssFile ( BAOGG_CSS . '960/text.css' );
		$this->view->addCssFile ( BAOGG_CSS . '960/960_24_col.css' );
		$this->view->addCssFile ( BAOGG_CSS . 'jquery/thickbox.css' );
		$this->view->addCssFile ( BAOGG_CSS . 'jquery/jquery-ui.css' );
		$this->view->addCssFile ( BAOGG_CSS . 'jquery/ui.jqgrid.css' );
		$this->view->addCssFile ( BAOGG_CSS . 'index.css' );
	}
	
	function initJsFiles() {
		$this->view->addJsFile ( BAOGG_JS . 'jquery/jquery.js' );
		$this->view->addJsFile ( BAOGG_JS . 'jquery/jquery-ui.js' );
		$this->view->addJsFile ( BAOGG_JS . 'jquery/i18n/grid.locale-cn.js' );
		$this->view->addJsFile ( BAOGG_JS . 'jquery/jqGrid.js' );
		$this->view->addJsFile ( BAOGG_JS . 'json2.js' );
		
	}
	public function initMeta(){
		$this->view->addMeta(array('http-equiv'=>'content-type','content'=>'text/html; charset=UTF-8'));
	}
}