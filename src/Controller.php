<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Controller.php 479 2012-03-14 03:46:16Z beimuaihui@gmail.com $
 */

namespace Baogg;

use Baogg\File\Html;

// use Psr\Container\ContainerInterface;

class Controller
{

    const CODE_ERR_LACK_FIELD = Html::CODE_ERR_LACK_FIELD;
    const CODE_ERR_EXISTS_DUPLICATE = Html::CODE_ERR_EXISTS_DUPLICATE;
    const CODE_ERR_DATA = Html::CODE_ERR_DATA;
    const CODE_ERR_NO_DATA = Html::CODE_ERR_NO_DATA;
    const CODE_OK =  Html::CODE_OK;
    const CODE_EMPTY =  Html::CODE_EMPTY;
    const CODE_ERR_SQL =  Html::CODE_ERR_SQL;
    const CODE_CREATED =  Html::CODE_CREATED;
    const CODE_UPDATED =  Html::CODE_UPDATED;
    const CODE_DELETED =  Html::CODE_DELETED;
    const CODE_SAVE_LIST =  Html::CODE_SAVE_LIST;
    const CODE_NOT_FOUND =  Html::CODE_NOT_FOUND;
    const CODE_UNPROCESSABLE =  Html::CODE_UNPROCESSABLE;
    const CODE_UNAUTHORIZED_USER = Html::CODE_UNAUTHORIZED_USER;


    protected $container;

    // constructor receives container instance
    //public function __construct(ContainerInterface $container) {
    //    $this->container = $container;
    //}



    public static function getUrlParams()
    {
        $request = null; // Zend_Controller_Front::getInstance()->getRequest();
        $params = $request ? $request->getParams() : $_REQUEST;
        if (!isset($params['module'])) {
            $params = array_merge($params, self::getCustomControllerName());
        }
        return $params;
    }

    public static function getCustomControllerName()
    {
        $sParams = substr($_SERVER['REQUEST_URI'], strlen(BAOGG_BASE_URL));
        $aParams = explode("/", $sParams);
        $ctlName['module'] = isset($aParams[0]) && $aParams[0] ? $aParams[0] : "default";
        $ctlName['controller'] = isset($aParams[1]) && $aParams[1] ? $aParams[1] : "index";
        $ctlName['action'] = isset($aParams[2])  && $aParams[2] ? $aParams[2] : "index";
        return $ctlName;
    }



    public function buildParamList($params)
    {
        $retval = array();
        foreach ($params as $key => $param) {
            if (is_string($key)) {
                $key = htmlspecialchars(var_export($key, true) . ' => ');
            } else {
                $key = '';
            }
            switch (gettype($param)) {
                case 'array':
                    $retval[] = $key . 'array(' . buildParamList($param) . ')';
                    break;
                case 'object':
                    $retval[] = $key . '[object <em>' . get_class($param) . '</em>]';
                    break;
                case 'resource':
                    $retval[] = $key . '[resource <em>' . htmlspecialchars(get_resource_type($param)) . '</em>]';
                    break;
                case 'string':
                    $retval[] = $key . htmlspecialchars(var_export(strlen($param) > 51 ? substr_replace($param, ' 鈥� ', 25, -25) : $param, true));
                    break;
                default:
                    $retval[] = $key . htmlspecialchars(var_export($param, true));
            }
        }
        return implode(', ', $retval);
    }
}
