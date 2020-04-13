<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;

class TimeGridModel extends Model
{
    const GAP = 300; // 5分钟间隔
    const STR_DIARY = "STR:TIMEGRIDS:%s:%s"; // {user_id} {day_id}

    public static function initDayGrids()
    {
        $grids = [];
        for($i = 0; $i < 86400; $i = $i + self::GAP) {
            $grid['startTime'] = $i;
            $grid['endTime'] = $i + self::GAP - 1;
            $grid['content'] = '';
            $grids[] = $grid;
        }
        return $grids;
    }

    public static function getDayGrids($userId, $dayId)
    {
        $dayGrids = self::initDayGrids();
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
        $data = Redis::instance()->get($key);
        return $data ? json_decode($data, true) : $dayGrids;
//        if (!$dayGrids) {
//            $dayGrids = self::getGridByUserIdAndDayId($userId, $dayId);
//        }
//        return $dayGrids;
    }

    public static function fillDayGrids($grids, $userId, $dayId)
    {
        self::fillGrids($grids);
        // 如果有读写分离，会无法及时读取到写入的数据
         $grids = self::getGridByUserIdAndDayId($userId, $dayId);
        // 更新缓存
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
        $dayGrids = self::initDayGrids();
        foreach ($grids as $grid) {
            $startIndex = floor($grid['startTime'] / self::GAP);
            $endIndex = floor($grid['endTime'] / self::GAP);
            for ($i = $startIndex; $i <= $endIndex; $i++) {
                $dayGrids[$i]['content'] = $grid['content'];
            }
        }
        Redis::instance()->set($key, json_encode($dayGrids));
        return $dayGrids;
    }

    /**
     * 获取用户指定日期的时间格子
     *
     * @param $userId
     * @param int $dayId
     * @return mixed
     * @throws \Exception
     */
    public static function getGridByUserIdAndDayId($userId, $dayId)
    {
        return self::instance()->get("timeGrid", "*", ["userId" => $userId, "dayId" => $dayId]);
    }

    /**
     * 获取用户指定日期的时间格子
     *
     * @param $userId
     * @param int $startDate
     * @param int $endDate
     * @return mixed
     * @throws \Exception
     */
    public static function getGridByUserIdBetweenDayIds($userId, $startDate, $endDate)
    {
        return self::instance()->get("timeGrid", "*", ["userId" => $userId, "dayId" => [$startDate, $endDate]]);
    }

    /**
     * 填充格子，单个或多个
     *
     * @param $grids
     * @return bool
     * @throws \Exception
     */
    public static function fillGrids($grids)
    {
        self::instance()->insert("timeGrid", $grids);
        return true;
    }

}
