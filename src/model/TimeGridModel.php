<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;
use HttpServer\component\Auth;

class TimeGridModel extends Model
{
    const GAP = 15 * 60; // 15分钟间隔

    const STR_DIARY = "STR:TIME_GRIDS:%s:%s"; // {user_id} {day_id}

    /**
     * 将时间转换为格子显示时间，比如 3000 即 00:50
     * @param $time
     */
    public static function time2Grid($time)
    {
        return $time;
    }

    /**
     * 将格子显示时间转换为时间偏移  比如 00:50 转为 3000
     * @param $grid
     */
    public static function grid2Time($grid)
    {
        return $grid;
    }

    /**
     * 将一天按GAP划分为格子
     *
     * @return array
     */
    public static function initDayGrids()
    {
        $grids = [];
        for($i = 0; $i < 86400; $i = $i + self::GAP) {
            $grid['startTime'] = self::time2Grid($i);
            $grid['endTime'] = self::time2Grid($i + self::GAP - 1);
            $grid['content'] = '';
            $grids[] = $grid;
        }
        return $grids;
    }

    /**
     * @param $userId
     * @param $dayId
     * @throws
     */
    public static function genDayGrid($userId, $dayId)
    {
        // 如果有读写分离，会无法及时读取到写入的数据
        $grids = self::getGridByUserIdAndDayId($userId, $dayId);
        $dayGrids = self::initDayGrids();
        foreach ($grids as $grid) {
            $startIndex = floor($grid['startTime'] / self::GAP);
            $endIndex = floor($grid['endTime'] / self::GAP);
            for ($i = $startIndex; $i <= $endIndex; $i++) {
                $dayGrids[$i]['content'] = $grid['content'];
            }
        }
    }

    public static function getTodayGrids($userId, $dayId)
    {
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
        $data = Redis::instance()->get($key);
        return $data ? json_decode($data, true) : self::initDayGrids();
    }

    /**
     * @param $grids
     * @param $userId
     * @param $dayId
     * @return mixed
     * @throws \Exception
     */
    public static function fillTodayGrids($grids, $userId, $dayId)
    {
        self::fillGrids($grids);
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
        $dayGrids = self::genDayGrid($userId, $dayId);
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
        return self::instance()->get(
            "timeGrid",
            "*",
            [
                "userId" => $userId,
                "dayId" => $dayId,
                "ORDER" => ["level"],
            ]
        );
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
     * @throws \Exception
     */
    public static function fillGrids($grids)
    {
        self::instance()->insert("timeGrid", $grids);
    }

}
