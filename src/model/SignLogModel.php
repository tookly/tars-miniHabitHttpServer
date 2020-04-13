<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;

class SignLogModel extends Model
{
    const HASH_TARGET_INFO = 'STR:TARGET:SIGN:TIMES:%s'; // {target_id}

    /**
     * 记录打卡日志
     *
     * @param $record
     * @return mixed
     * @throws \Exception
     */
    public static function addRecord($record)
    {
        self::instance()->insert("signLog", $record);
        Redis::instance()->incr(sprintf(self::HASH_TARGET_INFO, $record['targetId']), $record['unit']);
    }

    /**
     * 获取用户目标打卡情况，可以限制时间
     *
     * @param $targetId
     * @param $startTime
     * @param $endTime
     * @return mixed
     * @throws \Exception
     */
    public static function getSignLogByTargetId($targetId, $startTime = '', $endTime = '')
    {
        return self::instance()->get("signLog", "*", ["id" => $targetId, "createdAt" => [$startTime, $endTime]]);
    }

}
