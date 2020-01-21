<?php
namespace HttpServer\service;

use HttpServer\component\Controller;
use HttpServer\component\Redis;
use HttpServer\conf\Code;

class TargetService
{
    const HASH_TARGET_INFO = 'HASH_TARGET_INFO_%s'; // {target_id}

    private static function genTargetId()
    {
        Controller::getUser();
    }

    public static function set($target, $time, $timeSuffix, $number) {
        $targetId = self::genTargetId();
        Redis::instance()->hGetAll(sprintf(self::HASH_TARGET_INFO, $targetId));
        return $targetId = 1;
    }

    public static function get($targetId = 0) {
        return [];
    }

    public static function getString($targetId = 0) {
        return "";
    }

    public static function sign() {
        return $number = 1;
    }

    public static function statistics() {
        return [];
//        $targetId = $this->user->userId;
//
//        // 处理日打卡数据
//        $end = time();
//        $start = $end - 86400 * 7;
//        $day = [];
//        for($i = $end; $i > $start; $i = $i - 86400) {
//            $tmp['categories'] = date('m.d', $i);
//            $tmp['data'] = (int)Redis::instance()->zScore(sprintf(self::TARGET_SIGN_KEY, $targetId), date('Ymd', $i));
//            $day[] = $tmp;
//        }
//        $data['day']['title'] = '日打卡';
//        $data['day']['data'] = array_column($day, 'data');
//        $data['day']['categories'] = array_column($day, 'categories');
//
//        $target = Redis::instance()->hGetAll(sprintf(self::TARGET_KEY, $targetId));
//        $startWeek = date('YW', $target['dateline']);
//
//        $end = date('YW');
//        $start = $end - 3;
//        $week = [];
//        for($i = $end; $i > $start; $i = $i - 1) {
//            $tmp['categories'] = 'W' . (int)($i - $startWeek + 1);
//            $tmp['data'] = (int)Redis::instance()->zScore(sprintf(self::TARGET_SIGN_WEEK_KEY, $targetId), $i);
//            $week[] = $tmp;
//        }
//        $data['week']['title'] = '周打卡';
//        $data['week']['data'] = array_column($week, 'data');
//        $data['week']['categories'] = array_column($week, 'categories');
//        return $data;
    }
}
