<?php

namespace Tars\registry;

use Tars\registry\contract\StoreCacheInterface;

class RouteTable implements StoreCacheInterface
{
    private static $_instance;

    const SWOOLE_TABLE_SET_FAILED = -1001;
    const SWOOLE_TABLE_GET_FAILED = -1002;
    /** @var \swoole_table $swooleTable */
    public $swooleTable;

    // routeInfo由一个结构体组成
    public function __construct($config = [])
    {
        $size = isset($config['size']) ? $config['size'] : 200;
        //100个服务,每个长度1000 需要100000个字节,这里申请200行,对应200个服务
        $this->swooleTable = new \swoole_table($size);
        $this->swooleTable->column('routeInfo', \swoole_table::TYPE_STRING, 1000);
        $this->swooleTable->column('timestamp', \swoole_table::TYPE_INT, 4);
        $this->swooleTable->create();
    }

    public static function getInstance()
    {
        if (self::$_instance) {
            return self::$_instance;
        } else {
            self::$_instance = new self();

            return self::$_instance;
        }
    }

    public function setRouteInfo($moduleName, $routeInfo)
    {
        $routeInfoStr = \serialize($routeInfo);
        $this->swooleTable->set($moduleName,
            ['routeInfo' => $routeInfoStr, 'timestamp' => time()]);
    }

    public function getRouteInfo($moduleName)
    {
        $result = $this->swooleTable->get($moduleName);
        if ($result) {
            $routeInfoStr = $result['routeInfo'];
            $timestamp = $result['timestamp'];
            $routeInfo = \unserialize($routeInfoStr);

            return [
                'routeInfo' => $routeInfo,
                'timestamp' => $timestamp,
            ];
        }
        return [];
    }
}
