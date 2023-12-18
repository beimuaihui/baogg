<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Date.php 239 2011-06-13 04:08:13Z beimuaihui $
 */

namespace Baogg;

use Baogg\Redis\PhpRedis;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory as AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\RedisSequenceResolver;

/**
 * App
 *
 * This is the primary class with which you instantiate,
 * configure, and run a Slim Framework application.
 * The \Baogg\App class also accepts Slim Framework middleware.
 */
class App
{
    protected static $_instance = null; //slim/app
    protected static $_settings = null; //setting array
    protected static $_logger = null;  //Logger
    protected static $_response = null;
    protected static $snowflake = null;
    //this can't be construct outside,single pattern
    protected function __construct()
    {
    }

    /**
     * Create new application
     *
     * @param array $settings
     */
    public static function setInstance(\Slim\App $app)
    {
        self::$_instance = $app;
        return self::$_instance;
    }
    public static function getInstance(): \Slim\App
    {
        return self::$_instance;
    }

    public static function setSettings(array $arr_setting)
    {
        self::$_settings = $arr_setting;
        return self::$_settings;
    }
    public static function getSettings(): array
    {
        if (!self::$_settings) {
            self::$_settings = \Baogg\File::getSetting('');
        }

        return self::$_settings;
    }
    /*
        public static function getContainer():Container{
            return self::$_container;
        }
    */

    public static function getLogger(): Logger
    {
        if (!self::$_logger) {
            $settings = self::getSettings()['settings']['logger'];

            $logger = new Logger($settings['name']);
            $handler = new StreamHandler($settings['path'], $settings['level']);
            $logger->pushHandler($handler);
            self::$_logger = $logger;
        }
        return self::$_logger;
    }


    public static function getApp()
    {
        return self::$_instance;
    }

    public function getResponse(): ResponseInterface
    {
        if (!self::$_response) {
            self::$_response = self::getInstance()->handle(ServerRequestCreatorFactory::create()->createServerRequestFromGlobals());
        }
        return self::$_response;
    }
    public function setResponse($response)
    {
        self::$_response = $response;
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public static function getSnowflake(): Snowflake
    {
        if (!self::$snowflake) {

            // ip地址中第三位及第四位 取模 32 为data center id ,work id
            $arr_server_ip = explode('.', $_SERVER['SERVER_ADDR']);
            self::$snowflake = (new Snowflake($arr_server_ip[2] % 32, $arr_server_ip[3] % 32))
                ->setStartTimeStamp(strtotime('2021-01-30') * 1000)
                ->setSequenceResolver(
                    (new RedisSequenceResolver(
                        PhpRedis::getInstance(self::getSettings()['settings']['redis'])->getRedis('snowflake')
                    ))->setCachePrefix('snowflake')
                );
        }

        return self::$snowflake;
    }

    /**
     * 判断当前环境是否为测试环境
     *
     * @return boolean
     */
    public static function isDev()
    {
        //error_log(__FILE__ . __LINE__ . " env HOST_ENV = {$_ENV['HOST_ENV']}");
        return getenv('HOST_ENV') === 'dev';
    }
}
