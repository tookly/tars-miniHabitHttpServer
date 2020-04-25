<?php
namespace HttpServer\service;

use HttpServer\component\HabitException;
use HttpServer\component\Redis;
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
        $content = $content ?: self::TASK_CONFIG[$taskId]['content'];
        $newGrids = [];
        foreach ($grids as $grid) {
            $newGrids[] = self::formatGrid($grid, $taskId, $content, $level, $userId, $dayId);
        }
        return TimeGridModel::fillTodayGrids($newGrids, $userId, $dayId);
    }

    /**
     * @param $taskId
     * @return array
     * @throws
     */
    public static function fillStartGrid($taskId) {
        $userId = Auth::getUser()->userId;
        $dayId = date('Ymd');
        $grid = [
            'taskId' => $taskId,
            'dayId' => $dayId,
            'level' => self::LEVEL_START_FINISH,
            'userId' => Auth::getUser()->userId,
            'content' => self::TASK_CONFIG[$taskId]['content'] ?: '',
            'startTime' => date('H:i'),
            'endTime' => '',  // 开始时不确定结束时间
        ];
        $grid = self::formatGrid($grid);
        if (!Redis::instance()->set(sprintf(TaskService::TASK_STATUS, $userId), json_encode($grid), ['nx'])) {
            throw new HabitException(Code::FAIL, '请先结束上一个任务，再开始新任务哦~');
        }
        TimeGridModel::fillGrids($grid);
        return self::getGoingGrid();
//        return TimeGridModel::fillTodayGrids($grid, $userId, $dayId);
    }

    public static function getGoingGrid() {
        $grid = Redis::instance()->get(sprintf(TaskService::TASK_STATUS, Auth::getUser()->userId));
        $grid = json_decode($grid, true) ?: [];
        if ($grid) {
            $goingGrid['length'] =  intval($grid['startTime'] / 60) . ':' . intval(self::grid2Time(date('H:i')) / 60);
            $goingGrid['content'] = $grid['content'];
        }
        return $goingGrid ?? [];
    }

    /**
     * @return array
     * @throws
     */
    public static function fillEndGrid() {
        // 记录结束时间点
        $dayId = date('Ymd');
        $yesterdayId = date('Ymd', strtotime("-1 day"));
        $userId = Auth::getUser()->userId;
        if (!$grid = Redis::instance()->get(sprintf(TaskService::TASK_STATUS, $userId))) {
            throw new HabitException(Code::FAIL, '当前没有进行中的任务~');
        }
        $gridInfo = json_decode($grid, true);
        if (!$gridInfo || !$gridInfo['uuid'] || !$gridInfo['dayId']) {
            // 进行中的任务不合法，直接删除
            Redis::instance()->del(sprintf(TaskService::TASK_STATUS, $userId));
            throw new HabitException(Code::FAIL, '任务停止');
        }
        if ($gridInfo['dayId'] == $dayId) {
            // 当天更新前一个grid
            $gridInfo['endTime'] = self::grid2Time(date('H:i'));
            TimeGridModel::updateGrid($gridInfo['uuid'], $gridInfo);
        } else if ($gridInfo['dayId'] = $yesterdayId) {
            // 跨一天则填两个格子
            $gridInfo['endTime'] = self::grid2Time(date('24:00'));
            TimeGridModel::updateGrid($gridInfo['uuid'], $gridInfo);
            $gridInfo['startTime'] = '00:00';
            $gridInfo['endTime'] = date('H:i');
            $gridInfo['dayId'] = $dayId;
            TimeGridModel::fillGrids(self::formatGrid($gridInfo));
        } else if ($gridInfo['dayId'] < $yesterdayId) {
            // 跨多天属于一个异常情况，直接停止之前的grid，后续不填充
            $gridInfo['endTime'] = self::grid2Time(date('24:00'));
            TimeGridModel::updateGrid($gridInfo['uuid'], $gridInfo);
        }
        Redis::instance()->del(sprintf(TaskService::TASK_STATUS, $userId));
        return TimeGridModel::genDayGrid($userId, $dayId);
    }

    /**
     * @param $grid
     * @param $taskId
     * @param $content
     * @param $level
     * @param $userId
     * @param $dayId
     * @return mixed
     */
    public static function formatGrid($grid, $taskId = '', $content = '', $level = '', $userId = '', $dayId = '') {
        $temp['uuid'] = uniqid();
        $temp['dayId'] = $dayId ?: $grid['dayId'];
        $temp['userId'] = $userId ?: $grid['userId'];
        $temp['content'] = $content ?: $grid['content'];
        $temp['taskId'] = $taskId ?: $grid['taskId'];
        $temp['startTime'] = self::grid2Time($grid['startTime']);
        $temp['endTime'] = self::grid2Time($grid['endTime']);
        $temp['level'] = $level ?: $grid['level'];
        $temp['createdAt'] = date('Y-m-d H:i:s');
        $temp['updatedAt'] = date('Y-m-d H:i:s');
        return $temp;
    }

    public static function statistics() {
        return [];
    }



}
