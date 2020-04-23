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
