<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:43.
 */

namespace HttpServer\component;

use HttpServer\conf\Code;
use HttpServer\conf\ENVConf;
use Medoo\Medoo;

class Model
{
    private static $instance = [];
    
    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig($name)
    {
        $config = ENVConf::getDatabaseConf($name);
        if (empty($config)) {
            throw new HabitException(Code::FAIL, 'database config not exist');
        }
        return $config;
    }
    
    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function instance($name = 'habit')
    {
        if (empty(self::$instance[$name])) {
            $config = self::getConfig($name);
            self::$instance[$name] = new medoo($config);
        }
        return self::$instance[$name];
    }
    
}
