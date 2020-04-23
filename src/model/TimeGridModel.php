<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;
use HttpServer\component\Auth;

class TimeGridModel extends Model
{

    const STR_DIARY = "STR:TIME_GRIDS:%s:%s"; // {user_id} {day_id}

    /**
     * 将一天按分钟划分
     *
     * @return array
     */
    public static function initDayMinuteGrids()
    {
        $grids = [];
        for($i = 0; $i < 86400; $i = $i + 60) {
            $grids[] = '';
        }
        return $grids;
    }

    /**
     * @param $userId
     * @param $dayId
     * @return array
     * @throws
     */
    public static function genDayGrid($userId, $dayId)
    {
        // 如果有读写分离，会无法及时读取到写入的数据
        $dayMinuteGrids = self::initDayMinuteGrids();
        $grids = self::getGridByUserIdAndDayId($userId, $dayId);
        foreach ($grids as $grid) {
            $start = intval($grid['startTime'] / 60);
            $end = intval($grid['endTime'] / 60);
            for ($i = $start; $i < $end; $i++) {
                $dayMinuteGrids[$i] = $grid['content'];
            }
        }
        $dayGrids = [];
        $start = 0;
        $current = 0;
        $size = count($dayMinuteGrids);
        while ($current < $size - 1) {
            // 最后一个之前的元素，如果content一样，则归并为一个数组
            if ($dayMinuteGrids[$current] == $dayMinuteGrids[$current+1]) {
                $current++;
                continue;
            }
            $dayGrid['length'] = $start . ':' . $current;
            $dayGrid['content'] = $dayMinuteGrids[$current];
            $dayGrids[] = $dayGrid;
            $current++;
            $start = $current;
        }
        // 将最后一个元素加入grid
        $dayGrid['length'] = $start . ':' . $current;
        $dayGrid['content'] = $dayMinuteGrids[$current];
        $dayGrids[] = $dayGrid;
        return $dayGrids;
    }

    public static function getTodayGrids($userId, $dayId)
    {
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
        $data = Redis::instance()->get($key);
        return $data ? json_decode($data, true) : [];
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
        $dayGrids = self::genDayGrid($userId, $dayId);
        $key = sprintf(self::STR_DIARY, $userId, $dayId);
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
        return self::instance()->select(
            "timeGrid",
            "*",
            [
                "userId" => $userId,
                "dayId" => $dayId,
                "ORDER" => [
                    "level" => 'ASC',
                    "id" => "ASC",
                ],
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

    /**
     * 根据uuid更新格子，单个
     *
     * @param $uuid
     * @param $grid
     * @throws \Exception
     */
    public static function updateGrid($uuid, $grid)
    {
        self::instance()->update("timeGrid", $grid, ['uuid' => $uuid]);
    }

}
