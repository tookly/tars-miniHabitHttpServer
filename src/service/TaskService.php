<?php
namespace HttpServer\service;

use HttpServer\component\Auth;
use HttpServer\component\HabitException;
use HttpServer\component\Redis;
use HttpServer\conf\Code;
use HttpServer\model\TimeGridModel;

class TaskService
{
    const TYPES = [
        1 => '休息',  // 睡觉
        2 => '干活',  // 工作
        3 => '健身',  // 跑步、KEEP、瑜伽
        4 => '摸鱼',  // 游戏、微信、朋友圈
        5 => '码字',  // 写作、更新公众号
        6 => '充能',  // 阅读、专业学习、英语、代码
        7 => '待机'   // 胡思乱想、情绪崩溃、什么都不能做的状态
    ];

    const TASK_STATUS = "STR:TASK:STATUS:%s"; // {userId} 内容为taskId，有值表示这个任务正在进行中。只能先结束，再开始新任务。 有点麻烦，要不要预设一个时间段呢？

    public static function getTypes() {
        foreach ( self::TYPES as $id => $type ) {
            $temp['id'] = $id;
            $temp['type'] = $type;
            $types[] = $temp;
        }
        return $types ?? [];
    }

    public static function start($taskId = 0) {
        // 记录开始时间点
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        $grids = [
            [
                'taskId' => $taskId,
                'dayId' => $dayId,
                'startTime' => TimeGridModel::time2Grid(date('H:i')),
                'endTime' => TimeGridModel::time2Grid('23:59'),
            ]
        ];
        if (!Redis::instance()->set(sprintf(self::TASK_STATUS, $userId), json_encode($grids), ['nx' => 1])) {
            throw new HabitException(Code::FAIL, '当前有任务正在执行中');
        }
        TimeGridService::fillTodayGrids($grids, $taskId, TimeGridService::LEVEL_START_FINISH, $dayId, $userId);
        return Code::SUCCESS;
    }

    /**
     * @param int $taskId
     * @return array
     * @throws HabitException
     */
    public static function finish($taskId = 0) {
        // 记录结束时间点
        $dayId = date('Ymd');
        $userId = Auth::getUser()->userId;
        if (!$grid = Redis::instance()->get(sprintf(self::TASK_STATUS, $userId), $taskId)) {
            throw new HabitException(Code::FAIL, '没有需要停止的任务');
        }
        $gridInfo = json_decode($grid, true);
        if ($gridInfo['taskId'] !== $taskId || $gridInfo['dayId'] > $dayId || ($gridInfo['dayId'] == $dayId && $gridInfo['startTime'] > date('H:i'))) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        $grids = [
            [
                'startTime' => $gridInfo['dayId'] == $dayId ? $gridInfo['startTime'] : TimeGridModel::time2Grid('00:00'),
                'endTime' => TimeGridModel::time2Grid(date('H:i')),
            ]
        ];
        TimeGridService::fillTodayGrids($grids, $taskId, TimeGridService::LEVEL_START_FINISH, $dayId, $userId);
        Redis::instance()->del(sprintf(self::TASK_STATUS, $userId), $taskId);
        return Code::SUCCESS;
    }

    // 暂不细分
    public static function getLists() {
        return [];
    }

    // 暂不细分
    public static function getList($type) {
        return [];
    }

    // 暂不细分
    public static function add($task, $type) {
        return '';
    }

    public static function remove($taskId) {
        return true;
    }

}
