<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Grid.php 208 2011-03-04 13:23:19Z beimuaihui $
 */

namespace Baogg\Controller;
class Widget {
	/**
	 * $_actionController
	 *
	 * @var Zend_Controller_Action $_actionController
	 */
	protected $_actionController = null;
	
	/**
	 * @var mixed $_frontController
	 */
	protected $_frontController = null;
	
	private $_view;
	private $_model;
	
	
	public function __call($method, $args){
		/* if(method_exists($this, $method))
        {
            $this->$method();
        } */
	}

	
	public function getView(){
		return $this->_view;
	}
	public function getModel(){
		return $this->_model;
	}
	
	
	public function setView($obj){
		 $this->_view=$obj;
	}
	public function setModel($obj){
		$this->_model=$obj;
	}
	
	
	
	
	/**
	 * setActionController()
	 *
	 * @param  Zend_Controller_Action $actionController
	 * @return Zend_Controller_ActionHelper_Abstract Provides a fluent interface
	 */
	public function setActionController(Zend_Controller_Action $actionController = null)
	{
		$this->_actionController = $actionController;
		return $this;
	}
	
	/**
	 * Retrieve current action controller
	 *
	 * @return Zend_Controller_Action
	 */
	public function getActionController()
	{
		return $this->_actionController;
	}
	
	/**
	 * Retrieve front controller instance
	 *
	 * @return Zend_Controller_Front
	 */
	public function getFrontController()
	{
		return Zend_Controller_Front::getInstance();
	}
	
	/**
	 * Hook into action controller initialization
	 *
	 * @return void
	 */
	public function init()
	{
	}
	
	/**
	 * Hook into action controller preDispatch() workflow
	 *
	 * @return void
	 */
	public function preDispatch()
	{
	}
	
	/**
	 * Hook into action controller postDispatch() workflow
	 *
	 * @return void
	 */
	public function postDispatch()
	{
	}
	
	/**
	 * getRequest() -
	 *
	 * @return Zend_Controller_Request_Abstract $request
	 */
	public function getRequest()
	{
		$controller = $this->getActionController();
		if (null === $controller) {
			$controller = $this->getFrontController();
		}
	
		return $controller->getRequest();
	}
	
	/**
	 * getResponse() -
	 *
	 * @return Zend_Controller_Response_Abstract $response
	 */
	public function getResponse()
	{
		$controller = $this->getActionController();
		if (null === $controller) {
			$controller = $this->getFrontController();
		}
	
		return $controller->getResponse();
	}
	
	public function render(){		
		$trace=debug_backtrace(0);
		$caller=$trace[1];
		
		$method=$caller['function'];
		$class=$caller['class'];
		
		if($caller['function']=='indexAction'){
			return $this->getView()->render(substr($class,8,-11).'.tpl');
		}else{
			return $this->getView()->render(substr($class,8,-11).'_'.substr($method,0,-6).'.tpl');
		}
		
		
	}
	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		$fullClassName = get_class($this);
		
		//remove widgets_ prefixed and _controller suffixed
		$name = substr($fullClassName,8,-11);
		return $name;
		
		/* if (strpos($fullClassName, '_') !== false) {
			$helperName = strrchr($fullClassName, '_');
			return ltrim($helperName, '_');
		} elseif (strpos($fullClassName, '\\') !== false) {
			$helperName = strrchr($fullClassName, '\\');
			return ltrim($helperName, '\\');
		} else {
			
		} */
	}
	
}