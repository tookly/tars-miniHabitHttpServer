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
     * 将时间转换为格子显示时间，比如 3000 即 00:50
     *
     * @param $time
     * @return string
     */
    public static function time2Grid($time)
    {
        $minutes = intval($time / 60);
        $hour = intval($minutes / 60) < 10 ? '0' . intval($minutes / 60) : intval($minutes / 60);
        $minute = $minutes % 60 < 10 ? '0' . $minutes % 60 : $minutes % 60;
        return $hour . ":" . $minute;
    }

    /**
     * 将格子显示时间转换为时间偏移  比如 00:50 转为 3000
     *
     * @param $grid
     * @return int
     */
    public static function grid2Time($grid)
    {
        $info = explode(':', $grid);
        return intval($info[0]) * 60 * 60 + intval($info[1]) * 60;
    }

    /**
     * 将一天按GAP划分为格子
     *
     * @return array
     */
    public static function getInitDayGrids()
    {
        $grids = [];
        for($i = 0; $i < 86400; $i = $i + self::GAP) {
            $grid['startTime'] = self::time2Grid($i);
            $grid['endTime'] = self::time2Grid($i + self::GAP);
            $grid['content'] = '';
            $grids[] = $grid;
        }
        return $grids;
    }

    public static function getTodayGrids() {
        return TimeGridModel::getTodayGrids(Auth::getUser()->userId, date('Ymd'));
    }

    public static function getWeekGrid() {
        return [];
    }

    public static function fillDuration($startTime, $endTime, $taskId) {
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        $grids = [
            [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]
        ];
        return self::fillTodayGrids($grids, $taskId, '', self::LEVEL_MANUAL, $userId, $dayId);
    }

    public static function fillWithGrids($grids, $taskId, $content) {
        $userId = Auth::getUser()->userId;
        $dayId = date('Ymd');
        return self::fillTodayGrids($grids, $taskId, $content, self::LEVEL_MANUAL, $userId, $dayId);
    }

    /**
     * @param $grids
     * @param $taskId
     * @param $content
     * @param $level
     * @param $dayId
     * @param $userId
     * @return array
     * @throws
     */
    public static function fillTodayGrids($grids, $taskId, $content, $level = 1, $userId = '', $dayId = '') {
        // 需要返回最新的grids来刷新页面吗？
        $content = $content ?: self::TASK_CONFIG[$taskId];
        $newGrids = [];
        foreach ($grids as $grid) {
            $temp['dayId'] = $dayId;
            $temp['userId'] = $userId;
            $temp['content'] = $content;
            $temp['taskId'] = $taskId;
            $temp['startTime'] = self::grid2Time($grid['startTime']);
            $temp['endTime'] = self::grid2Time($grid['endTime']);
            $temp['level'] = $level;
            $temp['createdAt'] = date('Y-m-d H:i:s');
            $temp['updatedAt'] = date('Y-m-d H:i:s');
            $newGrids[] = $temp;
        }
        return TimeGridModel::fillTodayGrids($newGrids, $userId, $dayId);
    }

    public static function statistics() {
        return [];
    }
}
