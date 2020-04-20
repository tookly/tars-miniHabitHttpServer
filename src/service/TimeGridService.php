<?php
namespace HttpServer\service;

use HttpServer\conf\Code;
use HttpServer\model\TimeGridModel;
use HttpServer\component\Auth;

class TimeGridService
{

    const LEVEL_DEFAULT = 1;
    const LEVEL_START_FINISH = 2;
    const LEVEL_MANUAL = 3;

    // 暂时定几个任务
    const TASK_CONFIG = [
        1 => ['id' => 1, 'content' => '工作']
    ];

    public static function getTodayGrids() {
        return TimeGridModel::getTodayGrids(Auth::getUser()->userId, date('Ymd'));
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
        return self::fillTodayGrids($grids, $taskId, '', self::LEVEL_MANUAL);
    }

    public static function fillWithGrids($grids, $taskId, $content) {
        return self::fillTodayGrids($grids, $taskId, $content, self::LEVEL_MANUAL);
    }

    /**
     * @param $girds
     * @param $taskId
     * @param $content
     * @param $level
     * @return array
     * @throws
     */
    public static function fillTodayGrids($girds, $taskId, $content, $level = 1) {
        // 需要返回最新的grids来刷新页面吗？
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        $content = $content ?: self::TASK_CONFIG[$taskId];
        foreach ($girds as $grid) {
            $grid['dayId'] = $dayId;
            $grid['userId'] = $userId;
            $grid['content'] = $content;
            $grid['taskId'] = $taskId;
            $grid['startTime'] = TimeGridModel::grid2Time($grid['startTime']);
            $grid['endTime'] = TimeGridModel::grid2Time($grid['endTime']);
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
