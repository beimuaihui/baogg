<?php

/**
 * Created by PhpStorm.
 * User: bao
 * Date: 18-5-21
 * Time: 下午3:54
 */

namespace App\Controllers;

use Psr\Container\ContainerInterface;

class BaseController extends \Baogg\Controller
{


    // constructor receives container instance
    //public function __construct(ContainerInterface $container) {
    //    parent::__construct($container);

    //}

    public function __construct()
    {
        //load language file
        // \Baogg\App::getLogger()->debug("enter into class :".get_class($this));
        $arr_class_name = explode('\\', get_class($this));
        $last_class_name = $arr_class_name[count($arr_class_name) - 1];
        if (substr($last_class_name, -10) === 'Controller') {
            $last_class_name = substr($last_class_name, 0, -10);
        }
        \Baogg\Language::loadFile($last_class_name);
        // \Baogg\App::getLogger()->debug(" last controller name :".$last_class_name);
    }

    private function formatOutputArray($rs = array())
    {
        if (!$rs || !is_array($rs)) {
            return $rs;
        }

        foreach ($rs as $k => $v) {
            $first_k_char = strtolower(substr($k, 0, 1));
            if (!($first_k_char >= 'a' && $first_k_char <= 'z') && !is_int($k)) {
                $rs['col__' . $k] = $this->formatOutputArray($v);
                unset($rs[$k]);
                continue;
            }
            if (is_array($v)) {
                $rs[$k] = $this->formatOutputArray($v);
            }
        }
        return $rs;
    }

    public function formatOutput($res = array())
    {
        $res = $this->formatOutputArray($res);
        return json_encode($res);
    }

    public function cors($request, $response, $args)
    {
        //error_log(__FILE__.__LINE__." \n ".$request->getUri()->getHost());
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            // header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

            exit(0);
        }


        header('Access-Control-Allow-Origin: ' . (\Baogg\App::isDev() ? (isset($arr_headers['Origin']) ? $arr_headers['Origin'][0] : '*') : 'https://admin.xyzim.com'));
        //header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Cache-Control, Pragma, Origin,Authorization, Content-Type, X-Requested-With, loginToken");
        header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,HEAD,OPTIONS");
    }
}
