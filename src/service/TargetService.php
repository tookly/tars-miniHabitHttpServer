<?php
namespace HttpServer\service;

use HttpServer\component\Redis;
use HttpServer\component\Auth;
use HttpServer\model\SignLogModel;
use HttpServer\model\TargetModel;
use HttpServer\component\HabitException;
use HttpServer\conf\Code;

class TargetService
{
    const HASH_TARGET_SIGN_TIMES = 'HASH_TARGET_SIGN_TIME_%s_%s'; // {target_id} {ym}

    /**
     * @param $action
     * @param $time
     * @param $number
     * @return string
     * @throws
     */
    public static function create($action, $time, $number) {
        $target = [
            'userId' => Auth::getUser()->userId,
            'action' => $action,
            'time' => $time,
            'number' => $number,
            'unit' => 1,
            'status' => TargetModel::STATUS_ACTIVE,
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
        ];
        return TargetModel::createTarget($target);
    }

    /**
     * @return array|null
     * @throws HabitException
     */
    public static function getUserTarget() {
        return TargetModel::getUserTarget();
    }

    /**
     * @return string
     * @throws HabitException
     */
    public static function getUserTargetString() {
        $target = self::getUserTarget();
        if ($target) {
            return sprintf("我决定每天%s，%s%s\^0^/", $target['time'], $target['action'], $target['number']);
        }
        return '';
    }

    /**
     * @return mixed
     * @throws
     */
    public static function sign() {
        $target = TargetModel::getUserTarget();
        if (!$target || !$target['id']) {
            throw new HabitException(Code::ILLEGAL_OPERATION);
        }
        $record = [
            "targetId" => $target['id'],
            "unit" => $target['unit'],
            "createdAt" => date('Y-m-d H:i:s'),
            "updatedAt" => date('Y-m-d H:i:s'),
        ];
        SignLogModel::addRecord($record);
        return $record['unit'];
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
