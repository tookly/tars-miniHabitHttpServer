<?php
namespace HttpServer\service;

use HttpServer\conf\Code;
use HttpServer\model\TimeGridModel;
use HttpServer\component\Auth;

class TimeGridService
{
    const GAP = 15 * 60; // 15分钟间隔

    const LEVEL_DEFAULT = 1;
    const LEVEL_START_FINISH = 2;
    const LEVEL_MANUAL = 3;

    // 暂时定几个任务
    const TASK_CONFIG = [
        1 => ['id' => 1, 'content' => '工作']
    ];

    /**
     * 将一天按GAP划分为格子
     *
     * @return array
     */
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
     * 获得用户当天的时间格子
     *
     * @return array
     */
    public static function getTodayGrids() {
        $initDayGrids = self::initDayGrids();
        $dayGrids = TimeGridModel::getTodayGrids();
        return $dayGrids ?: $initDayGrids;
    }

    public static function getWeekGrid() {
        return [];
    }

    public static function fillDuration($startTime, $endTime, $taskId) {
        $grids = [
            [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]
        ];
        return self::fillTodayGrids($grids, $taskId, self::LEVEL_MANUAL);
    }

    public static function fillWithGrids($grids, $taskId) {
        return self::fillTodayGrids($grids, $taskId, self::LEVEL_MANUAL);

    }

    /**
     * @param $girds
     * @param $taskId
     * @param $level
     * @return array
     * @throws
     */
    public static function fillTodayGrids($girds, $taskId, $level = 1) {
        // 需要返回最新的grids来刷新页面吗？
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        $content = self::TASK_CONFIG[$taskId];
        foreach ($girds as $grid) {
            $grid['dayId'] = $dayId;
            $grid['userId'] = $userId;
            $grid['content'] = $content;
            $grid['taskId'] = $taskId;
            $grid['startTime'] = self::grid2Time($grid['startTime']);
            $grid['endTime'] = self::grid2Time($grid['endTime']);
            $grid['level'] = $level;
            $grid['createdAt'] = date('Y-m-d H:i:s');
            $grid['updatedAt'] = date('Y-m-d H:i:s');
        }
        return TimeGridModel::fillTodayGrids($girds, $userId, $dayId);
    }

    public static function statistics() {
        return [];
    }
}
