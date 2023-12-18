<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License 
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Plugin.php 478 2012-03-12 03:40:01Z beimuaihui@gmail.com $
 */
namespace Baogg\Controller;
class Plugin extends Zend_Controller_Plugin_Abstract
{
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
    	//if url is only dir such as /dir/,then will cause base_url(script_name) error!
    	if(strpos($_SERVER ['REQUEST_URI'],$_SERVER ['SCRIPT_NAME'])!==0)
    	{
    		$response = $this->getResponse();
			$response->setRedirect( rtrim($_SERVER ['REQUEST_URI'],'/').'/index.php' );
			$response->sendResponse();
			exit;
    	}
    	//strip magic quote
    	if (get_magic_quotes_gpc()) {
		    function stripslashes_deep($value)
		    {
		        $value = is_array($value) ?
		                    array_map('stripslashes_deep', $value) :
		                    stripslashes($value);
		
		        return $value;
		    }
		
		    $_POST = array_map('stripslashes_deep', $_POST);
		    $_GET = array_map('stripslashes_deep', $_GET);
		    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
		    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
		}
    	
        $Menu=new Menu();
        $rs_menu=$Menu->getOne(BAOGG_MENU_ID);
        $default_url=Baogg_Controller_Url::reverse($rs_menu[0]['url']);


        $Front=Zend_Controller_Front::getInstance();
        $Front->setDefaultModule($default_url['module']);
        $Front->setDefaultControllerName($default_url['controller']);
        $Front->setDefaultAction($default_url['action']);

       // $this->getResponse()->appendBody("<p>routeStartup() called</p>\n");
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
    	global $LANG;
    	//load language translate
    	$params=$request->getParams();
    	
    	Baogg_Language::loadFile($params['controller']);
    	
		if(@$_SESSION['admin']['user']){
			$Permission=new Permission();
			//unset($_SESSION);
            
            
			if(  !$Permission->checkUrl($this->getRequest()->getParams())){
				//echo __FILE__.__LINE__.'<pre>';var_dump($Permission->checkUrl($this->getRequest()->getParams()));exit;
				throw new Exception( \Baogg\Language::get('deny_access'));
				
			}else{
				//echo 'url ok';exit;
			}	
		}
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
    	//echo '<pre>';print_r($this->getRequest()->getPost());exit;
      //  $this->getResponse()->appendBody("<p>dispatchLoopStartup() called</p>\n");
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	
    	try {
    	
    		// do something that throws an exception
    	
    	} catch (Exception $e) {
    		// Repoint the request to the default error handler
    		$request->setModuleName('system');
    		$request->setControllerName('error');
    		$request->setActionName('error');
    	
    		// Set up the error handler
    		$error = new Zend_Controller_Plugin_ErrorHandler();
    		$error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
    		$error->request = clone($request);
    		$error->exception = $e;
    		$request->setParam('error_handler', $error);
    	}
    	
		if(!@$_SESSION['admin']['user'] && $this->getRequest()->getParam('action')!='login'){
			echo  '<script>top.location.href="'.BAOGG_BASE_URL.'system/user/login/";</script>'  ;
			exit;
		}
			

		$piece = strtolower(substr($_SERVER['SERVER_NAME'],0,4));
		 if (false && strcmp($piece, 'www.')==0)
		 {
			$url = 'http://' . substr($_SERVER['SERVER_NAME'],
					 4).$_SERVER['REQUEST_URI'];
			$response = $this->getResponse();
			$response->setRedirect( $url, 301 );
			$response->sendResponse();
			exit;
		 }
		 
		 

		 
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        //$this->getResponse()->appendBody("<p>postDispatch() called</p>\n");
    }

    public function dispatchLoopShutdown()
    {
       // $this->getResponse()->appendBody("<p>dispatchLoopShutdown() called</p>\n");
    }
}
