<?php
/**
 * Created by PhpStorm.
 * User: 27580
 * Date: 2019/3/20
 * Time: 16:14
 */

namespace Tars\registry\contract;


interface StoreCacheInterface
{
    public function __construct($config);

    public static function getInstance();

    /**
     * set route info to cache.
     * @param $moduleName
     * @param $routeInfo
     * @return mixed
     */
    public function setRouteInfo($moduleName, $routeInfo);

    /**
     * get route info by moduleName
     * @param $moduleName
     * @return mixed
     */
    public function getRouteInfo($moduleName);
}