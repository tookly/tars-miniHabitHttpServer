<?php
namespace HttpServer\service;

use HttpServer\conf\Code;
use HttpServer\model\TimeGridModel;
use HttpServer\component\Auth;

class TimeGridService
{
    // 暂时定几个任务
    const TASK_CONFIG = [
        1 => ['id' => 1, 'content' => '工作']
    ];

    public static function getDayGrids() {
        return TimeGridModel::getDayGrids();
    }

    public static function getWeekGrid() {
        return [];
    }

    /**
     * @param $girds
     * @param $taskId
     * @return array
     * @throws
     */
    public static function fillDayGrids($girds, $taskId) {
        // 需要返回最新的grids来刷新页面吗？
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        $content = self::TASK_CONFIG[$taskId];
        foreach ($girds as &$grid) {
            $grid['dayId'] = $dayId;
            $grid['userId'] = $userId;
            $grid['content'] = $content;
            $grid['taskId'] = $taskId;
            $grid['createdAt'] = date('Y-m-d H:i:s');
            $grid['updatedAt'] = date('Y-m-d H:i:s');
        }
        TimeGridModel::fillDayGrids($girds, $userId, $dayId);
        return [];
    }

    public static function statistics() {
        return [];
    }
}
