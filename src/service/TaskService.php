<?php
namespace HttpServer\service;

use HttpServer\conf\Code;

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
        return Code::SUCCESS;
    }

    public static function finish($taskId = 0) {
        // 记录结束时间点
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
