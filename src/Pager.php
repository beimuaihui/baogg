 <?php

/**
   * 基于Zend Framework分页类
   *
   * 作者：cone
   * 博客: http://www.amcone.cn/
   * @package Util
   * 修改网上整理的类
   **/
namespace Baogg;
class Pager {

    /**
     * 数据总数
     * 
     * @var int
     */
    protected $_total;

    /**
     * 当前页码
     * 
     * @var int
     */
    protected $_curpage;

    /**
     * 每页记录数
     * 
     * @var int
     */
    protected $_perpage;

    /**
     * 分页参数名称
     * 
     * @var string
     */
    protected $_pagename;

    /**
     * Zend_Controller_Request_Http对象
     * 
     * @var object
     */
    protected $_request;

    /**
     * 类构造函数
     * 初始化类内部属性
     * 
     * @param int $total
     *            总数
     * @param int $curpage
     *            当前页码
     * @param int $perpage
     *            每页记录数
     * @return void
     */
    public function __construct ($total, $perpage = 10, $pagename = '')
    {
        $this->_total = intval($total);
        $this->_perpage = $perpage;
        $this->_pagename = $pagename?$pagename:BAOGG_PARAM_PAGE;
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_curpage = intval($this->_request->getParam($this->_pagename));
    }

    /**
     * 根据已经赋值的类属性计算相关参数
     * 
     * @return array
     */
    public function calculatePage ()
    {
        $pageArray = array();
        $pageArray['pagename'] = $this->_pagename;
        $pageArray['total'] = $this->_total;
        // 算出总页数
        $pageArray['totalpage'] = (int) ceil($this->_total / $this->_perpage);
        $pageArray['pagestart'] = 0;
        // 算出数据开始的行数
        if ($this->_curpage < 1) {
            $this->_curpage = 1;
        } else {
            $this->_curpage > $pageArray['total'] && $this->_curpage = $pageArray['total'];
            $pageArray['pagestart'] = ($this->_curpage - 1) * $this->_perpage;
        }
        $pageArray['curpage'] = $this->_curpage;
        $pageArray['perpage'] = $this->_perpage;
        
        // 通过传入的Zend_Controller_Request_Http类获得当前控制器的相关信息
        $moduleName = $this->_request->getModuleName();
        $controllerName = $this->_request->getControllerName();
        $actionName = $this->_request->getActionName();
        // 获得参数
        $params = $this->_request->getParams();
        
        $current_url=Baogg_Controller_Url::getCurrentUrl();
        $pageArray['url']=preg_replace('/\/'.$this->_pagename.'\/\d+/','',$current_url).$this->_pagename.'/';
        // 初始化基本的链接
      /*   $pageArray['url'] = '/public/' . $moduleName . '/' . $controllerName . '/' . $actionName . '/';
        if ($params && is_array($params)) {
            // 反转数组的键与值
            $params = array_flip($params);
            // 过滤参数中的页码参数和值
            $params = array_filter($params, array($this,'_filterPage'));
            // 再次反转数组的键与值
            $params = array_flip($params);
            // 循环生成参数链接
            foreach ($params as $key => $value) {
                $pageArray['url'] .= $key . '/' . $value . '/';
            }
        } */
        return $pageArray;
    }

    /**
     * 清空类属性的赋值
     * 
     * @return array
     */
    public function cleanUp ()
    {
        $this->_curpage = $this->_perpage = $this->_curpage = 0;
        $this->_request = null;
    }

    /**
     * 设定内部属性$total的值
     * 
     * @return void
     */
    public function setTotal ($total)
    {
        $this->_total = intval($total);
    }

    /**
     * 设定内部属性$curpage的值
     * 
     * @return void
     */
    public function setCurpage ($curpage)
    {
        $this->_curpage = intval($curpage);
    }

    /**
     * 设定内部属性$perpage的值
     * 
     * @return void
     */
    public function setPerpage ($perpage)
    {
        $this->_perpage = intval($perpage);
    }

    /**
     * 设定内部属性$perpage的值
     * 
     * @return void
     */
    public function setPagename ($pagename)
    {
        $this->_pagename = $pagename;
    }

    /**
     * 设定内部对象$request
     * 
     * @return void
     */
    public function setRequest (Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
    }

    /**
     * 过滤Zend_Controller_Request_Http对象参数中的模块、控制器、动作及分页标识参数
     * 
     * @return bool
     */
    protected function _filterPage ($paramname)
    {
        $filter = array('module','action','controller',$this->_pagename);
        return (in_array($paramname, $filter) ? false : true);
    }
}
