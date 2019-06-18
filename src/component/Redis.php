<?php
/**
 * Created by PhpStorm.
 * User: chengxiaoli
 * Date: 2019/1/14
 * Time: 下午9:50
 */
namespace component;

use Code;
use HttpServer\conf\ENVConf;

/**
 * todo 使用协程版redis
 *
 * Class Redis
 * @package component
 */
class Redis
{
    private static $instance = [];
    
    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig($name)
    {
        $config = ENVConf::getRedisConf($name);
        if (empty($config)) {
            throw new \Exception(1, 'redis config not exist');
        }
        return $config;
    }
    
    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function instance($name = 'storage')
    {
        if (empty(self::$instance[$name])) {
            $config = self::getConfig($name);
            self::$instance[$name] = new \Redis();
            self::$instance[$name]->connect($config['host'], $config['port'], $config['timeout']);
//            self::$instance[$name]->auth($config['password']);
        } else {
            try {
                if (!(self::$instance[$name]->ping())) {
                    self::$instance[$name] = null;
                    return self::instance($name);
                }
            } catch (\Exception $e) {
                self::$instance[$name] = null;
                return self::instance($name);
            }
        }
        return self::$instance[$name];
    }
    
}