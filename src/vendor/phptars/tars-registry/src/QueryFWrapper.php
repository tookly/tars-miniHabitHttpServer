<?php

namespace Tars\registry;

use Tars\registry\contract\StoreCacheInterface;
use Tars\Utils;

class QueryFWrapper
{
    /** @var StoreCacheInterface */
    public static $cacheInstance = null;
    protected $_queryF;
    protected $_refreshInterval;

    public function __construct($locator, $socketMode, $refreshInterval = 60000)
    {
        $result = Utils::getLocatorInfo($locator);
        if (empty($result) || !isset($result['locatorName'])
            || !isset($result['routeInfo']) || empty($result['routeInfo'])) {
            throw new \Exception('Route Fail', -100);
        }

        $locatorName = $result['locatorName'];
        $routeInfo = $result['routeInfo'];
        $this->_refreshInterval = $refreshInterval;

        $this->_queryF = new QueryFServant($routeInfo, $socketMode, $locatorName);
    }

    public static function initStoreCache(StoreCacheInterface $storeCache)
    {
        self::$cacheInstance = $storeCache;
    }

    public static function getStoreCache()
    {
        return self::$cacheInstance;
    }

    public function findObjectById($id)
    {
        $cacheInstance = self::getStoreCache() ? self::getStoreCache() : RouteTable::getInstance();
        try {
            $result = $cacheInstance->getRouteInfo($id);

            if (isset($result['routeInfo'])) {
                $routeInfo = $result['routeInfo'];
                $timestamp = $result['timestamp'];
                if (time() - $timestamp < $this->_refreshInterval / 1000) {
                    return $routeInfo;
                }
            }

            $endpoints = $this->_queryF->findObjectById($id);
            $routeInfo = [];
            foreach ($endpoints as $endpoint) {
                $route['sIp'] = $endpoint['host'];
                $route['iPort'] = $endpoint['port'];
                $route['timeout'] = $endpoint['timeout'];
                $route['bTcp'] = $endpoint['istcp'];
                $routeInfo[] = $route;
            }
            $cacheInstance->setRouteInfo($id, $routeInfo);

            return $routeInfo;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
