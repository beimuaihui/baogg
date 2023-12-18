<?php
namespace Baogg\Controller\Action\Helper;
class Widget extends Zend_Controller_Action_Helper_Abstract
{
	private static $view;
	private $controllerDir;
	public function preDispatch()
	{
		
	}
	public function __construct ()
	{
		$this->controllerDir=BAOGG_ROOT;
	}
	public static function getSmarty()
	{
		if (!isset(self::$view)) {			
			$view_cfg = array(
				'debugging' => (BAOGG_DEBUG?1:0),  //0
				'left_delimiter' => "{", 
				'right_delimiter' => "}", 
				'compile_check' => (BAOGG_DEBUG?1:0), 
				'force_compile' => (BAOGG_DEBUG?1:0), 
				'compile_locking' => false,
				'caching' => BAOGG_READONLY?0 : (BAOGG_DEBUG?0:1),   //
	            'cache_lifetime' => 20, 
	            //'SMARTY_DIR' => BAOGG_ROOT . 'vendor/smarty/smarty/libs/', 
	            'template_dir' => BAOGG_ROOT . 'views/widgets', 
	            'compile_dir' => BAOGG_WIDGETS_COMPILE_DIR,  
	              'cache_dir' =>BAOGG_WIDGETS_CACHE_DIR,  
	            'plugins_dir' => array(BAOGG_ROOT . 'vendor/smarty/smarty/libs/plugins')
			);
	
			
			self::$view = new Baogg_View_Smarty(null,$view_cfg);
		}

		self::$view->clearVars();

		return self::$view;
	}
	
	public function getWidgetCodeById($widget_entity_id)
	{
		$WidgetEntity=new WidgetEntity();
		$rs_widget_position=$WidgetEntity->getOne($widget_entity_id);
		$widget_id=$rs_widget_position[0]['widget_id'];

		$Widget=new Widget();
		return $Widget->getTplName($widget_id);
	}
	

	public  function fixedId($id){
		$id= preg_replace("/[^a-z0-9]+/"," ",strtolower($id));
		$id=ucwords($id);
		return str_replace(" ","",$id);
	}

	public function __call($method, $args)
	{
		

		//echo __FILE__.__LINE__.'<pre>'; print_r( $args);exit; 
		$id=$args[0];
		$options=isset($args[1])?$args[1]:array();
		$widget_entity_id=(string)@$options['id'];

		$View = self::getSmarty();
		$View->assign('widget_entity_id',$widget_entity_id);
		$View->assign('widget_id',$widget_entity_id);

		

		if(preg_match("/[^a-z0-9]+/",strtolower($id))){
			$id=$this->fixedId($id);
		}
		
		$class='Widgets_'.$id.'_Controller';

		//echo __FILE__.__LINE__.'<pre>';var_dump($id);exit;
		
		//if not exist class name $class

		/*if($id=='ZdstudyTabNews'){
				echo __FILE__.__LINE__.'<pre>'; 
				var_dump( class_exists($class));

				exit; 
			}*/

		if(!@class_exists($class) && file_exists($this->controllerDir.str_replace('_',DIRECTORY_SEPARATOR,$class).'.php')){
			require $this->controllerDir.str_replace('_',DIRECTORY_SEPARATOR,$class).'.php';
			
		}		
		
		$action=lcfirst($method).'Action';
		

		

		//$Model_class='Widget_'.$this->fixedId($id);
		//$Model=new $Model_class;
		
		//if not exist $class.$method,call models/Widget/
		

		if(@class_exists($class) && method_exists($class, $action)){
			$instance=new $class;
			$instance->setActionController($this->getActionController());
			$instance->setView($View);
			//$instance->setModel($Model);
			
			/*if($id=='ZdstudyTabNews'){
				echo __FILE__.__LINE__.'<pre>'; print_r( $options);exit; 
			}*/
			$arr_info= $instance->$action($options);
			
		}else{			

			if($action=='indexAction'){			
				
				/*if($id == 'ZdstudyBookService'){
					$all_tpl_vars = $view->getEngine()->getTemplateVars();
					echo __FILE__.__LINE__.'<pre>'; 
					// take a look at them
					print_r($all_tpl_vars);exit;
				}*/
				
				return $View->render(substr($class,8,-11).'.tpl');
			}else{
				return $View->render(substr($class,8,-11).'_'.substr($action,0,-6).'.tpl');
			}

			//$arr_info= $Model->$method($options);
		}
		
		return $arr_info;
	}

	public static function __callStatic($method, $args) {
		$id=$args[0];
		$options=isset($args[1])?$args[1]:array();

		$class='Widgets_'.$this->fixedId($id).'_Controller';		
		$params=array_merge($this->getRequest()->getParams(),$options);
		try{
			//if not exist class name $class
			if(!@class_exists($class)){
				require_once $this->controllerDir.str_replace('_',DIRECTORY_SEPARATOR,$class).'.php';
			}
			
			$instance=new $class();			
			$action=lcfirst($method).'Action';
			
			$Model_class='Widget_'.$this->fixedId($id);
			$Model=new $Model_class;
			
			//if not exist $class.$method,call models/Widget/
			if(!method_exists($class, $action	)){				
				$arr_info= $Model->$method($options);
			}else{
				$instance->setRequest($params);
				$instance->setResponse($this->getResponse());
				$instance->setView($this->getSmarty());
				$instance->setModel($Model);
				$arr_info= $instance->$action($options);		
			}		
			
		}catch(Exception $e){
			echo __FILE__.__LINE__.'<pre>';var_dump($class);var_dump($method);exit;
		}
		return $arr_info;
	}

	/**
	 * Strategy pattern: call helper as broker method
	 *
	 * @param  string $name
	 * @param  array|Zend_Config $options
	 * @return Zend_Form
	 */
	public function direct ($name, $options = array())
	{
		
		if(is_int($name)){
			$options['id']=$name;
			$name=$this->getWidgetCodeById($name);			
		}

		if (isset($options['action'])){
			$action=$options['action'];		
		}else{
			$action='index';
		}
		return $this->$action($name,$options);		
				
		
	}

	public function loadWidget ($id, $options = array())
	{
		$this->direct($id,$options);
	}


	
}