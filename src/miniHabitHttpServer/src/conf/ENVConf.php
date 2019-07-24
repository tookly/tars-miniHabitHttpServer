<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/17
 * Time: 下午4:15.
 */

namespace HttpServer\conf;

use Tars\App;

class ENVConf
{
    /**
     * @return mixed
     * 获取当前环境的主控配置
     */
    public static function getLocator() {
        $tarsConfig = App::getTarsConfig();
        
        $tarsClientConfig = $tarsConfig['tars']['application']['client'];
        
        $locator = $tarsClientConfig['locator'];
        
        return $locator;
    }
    
    /**
     * @return mixed
     * 获取日志的路径
     */
    public static function getLogPath() {
        // $logPath = '/usr/local/app/tars/app_log/PHPTest/PHPHttpServer';
        $tarsConfig = App::getTarsConfig();
        
        $tarsServerConfig = $tarsConfig['tars']['application']['server'];
        
        $logPath = $tarsServerConfig['logpath'];
        
        return $logPath;
    }
    
    public static $socketMode = 2;
    
    public static function getTarsConf()
    {
        return App::getTarsConfig();
    }
    
    public static  function getRedisConf($name)
    {
        $redisConfs = [
            'storage' => [
                'host' => '127.0.0.1',
                'password' => '',
                'port' => '6379',
                'timeout' => 3,
            ]
        ];
        return $redisConfs[$name] ?? [];
    }
    
}
