<?php

/**
 * User: bao
 * Date: 19-3-12
 * Time: 上午10:27
 */

namespace Baogg\Redis;

class PhpRedis
{
    /** @var \Baogg\Redis\PhpRedis */
    protected static $Instance;
    protected static $ArrRedis = array();
    private $_config;

    public const MASTER_TYPE = 'master';

    //this can't be construct outside,single pattern
    protected function __construct($config = array())
    {
        $this->_config = $config;
    }




    /**
     * Get a instance of MyRedisClient
     *
     * @param string $key
     * @return \Baogg\Redis\PhpRedis|false
     */
    public static function getInstance($configs = array())
    {
        if (!extension_loaded('redis')) {
            return false;
        }

        if (!$configs) {
            $configs = \Baogg\App::getSettings()['settings']['redis'];
        }

        if (!self::$Instance) {
            self::$Instance = new self($configs);
        }
        return self::$Instance;
    }


    /**
     * @param $key redis存的key/或随机值
     * @param string $type master/slave
     * @return \Redis
     */
    public function getRedis($key = '', $type = self::MASTER_TYPE)
    {
        if (!$key) {
            $key = uniqid();
        }

        if (isset(self::$ArrRedis[$type]) && self::$ArrRedis[$type]) {
            return self::$ArrRedis[$type];
        }
        if ($type == 'slave' && !isset($this->_config[$type])) {
            return $this->getRedis($key, 'master');
        }
        $arr_type_config = $this->_config[$type];
        $type_index = $this->getTypeIndex($key, $arr_type_config);

        $Instance = new \Redis();
        $res_connect = $Instance->pconnect($arr_type_config[$type_index]['host'], $arr_type_config[$type_index]['port']);


        if (!$res_connect) {
            //error_log(__FILE__.__LINE__." redis connect fail ;config=".var_export($arr_type_config[$type_index],true)." res_connect =".var_export($res_connect,true));
            return false;
        }
        if ($arr_type_config[$type_index]['auth']) {
            $Instance->auth($arr_type_config[$type_index]['auth']);
        }
        //error_log(__FILE__.__LINE__." \n arr_type_config".var_export($this->_config,true));

        self::$ArrRedis[$type] = $Instance;

        return $Instance;
    }

    public function getTypeIndex($key, $arr_type_config)
    {

        if (count($arr_type_config) <= 1) {
            return 0;
        }

        $index = sprintf("%u", crc32(strtolower($key)));

        return bcmod($index, count($arr_type_config));
    }


    public function close()
    {
        foreach (self::$ArrRedis as $instance) {
            $instance->close();
        }
    }


    public function set($key, $value, $expire = 0, $type = self::MASTER_TYPE)
    {
        $redis = $this->getRedis($key, $type);
        if (!$expire) {
            return $redis->set($key, $value);
        } else {
            return $redis->set($key, $value, (int)$expire);
        }
    }


    public function get($key, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->get($key);
    }


    public function incr($key, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->incr($key);
    }

    public function decr($key, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->decr($key);
    }



    public function ping($type = self::MASTER_TYPE)
    {
        try {
            return $this->getRedis($type)->ping();
        } catch (\Exception $e) {
            return false;
        } finally {
            return false;
        }
    }

    public function publish($channel, $messsage, $type = self::MASTER_TYPE)
    {
        //error_log(__FILE__.__LINE__." phpredis publish  channels=".var_export($channel,true).";callback=".var_export($messsage,true));
        return $this->getRedis('message_queue', $type)->publish($channel, $messsage);
    }


    public function subscribe($channels, $callback, $type = self::MASTER_TYPE)
    {
        //error_log(__FILE__.__LINE__." phpredis subscribe  channels=".var_export($channels,true).";callback=".var_export($callback,true));
        return $this->getRedis('message_queue', $type)->subscribe($channels, $callback);
    }

    public function delete($key = '', $type = self::MASTER_TYPE)
    {
        if (is_array($key)) {
            foreach ((array)$key as $sub_key) {
                $this->delete($sub_key);
            }
        }
        return $this->getRedis($key, $type)->expire($key, 0);
    }

    // public function expire($key = '', $seconds = 0)
    // {
    //     return $this->getRedis($key,$type)->expire($key, $seconds);
    // }


    public function lPush($key, $value, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->lPush($key, $value);
    }


    public function lPop($key, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->lPop($key);
    }

    public function rPush($key, $value, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->rPush($key, $value);
    }

    public function lRange($key, $start = 0, $end = -1, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->lRange($key, $start, $end);
    }

    public function lRem($key, $value, $count = 0, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->lRem($key, $value, $count);
    }


    public function expireAt($key, $time, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->expireAt($key, $time);
    }

    /**
     * 失效时间
     *
     * @param string $key 键值
     * @param string $times ttl秒
     * @return void
     */
    public function expire($key, $times, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->expire($key, $times);
    }

    public function hGet($key, $hashKey, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->hGet($key, $hashKey);
    }
    public function hSet($key, $hashKey, $value, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->hSet($key, $hashKey, $value);
    }


    public function setBit($key, $offset, $value, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->setBit($key, $offset, $value);
    }

    public function getBit($key, $offset, $type = self::MASTER_TYPE)
    {
        return $this->getRedis($key, $type)->getBit($key, $offset);
    }

    /**
     * 等待获取某个键值
     *
     * @param string $cache_key 键
     * @param integer $lock_value 键
     * @param integer $second_time 等待秒数
     * @return mixed false获取失败，否则获取成功
     */
    public function getWait($cache_key, $lock_value = 1, $second_time = 3, $type = self::MASTER_TYPE)
    {

        $wait_times = 0; // 等待次数
        while ($wait_times < $second_time * 10) {
            $data = $this->get($cache_key, $type);
            if ($data !== false) { // 存在缓存数据，则直接获取返回
                return $data;
            }

            $flag_lock = $this->set($cache_key.':lock', $lock_value, 0, false, ['nx', 'ex' => $second_time], $type);
            if ($flag_lock) { // 锁定成功,则继续执行，否则休息100毫秒
                break;
            }

            usleep(100000);
            $wait_times++;
        }

        return false;
    }

    /**
     * 等待获取某个键值
     *
     * @param string $cache_key 键
     * @param integer $lock_value 键
     * @param integer $second_time 等待秒数
     * @return mixed false获取失败，否则获取成功
     */
    public function hGetWait($cache_key, $hashKey, $lock_value = 1, $second_time = 3, $type = self::MASTER_TYPE)
    {

        $wait_times = 0; // 等待次数
        while ($wait_times < $second_time * 10) {
            $data = $this->hGet($cache_key, $hashKey, $type);
            if ($data !== false) { // 存在缓存数据，则直接获取返回
                return $data;
            }

            $flag_lock = $this->set($cache_key.'::'.$hashKey. ':lock', $lock_value, 0, false, ['nx', 'ex' => $second_time], $type);
            if ($flag_lock) { // 锁定成功,则继续执行，否则休息100毫秒
                break;
            }

            usleep(100000);
            $wait_times++;
        }

        return false;
    }
}
