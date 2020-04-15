<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;
use HttpServer\component\Auth;
use HttpServer\component\HabitException;

class TargetModel extends Model
{
    const TARGET_KEY = 'HASH:TARGET:%s';
    const TARGET_NOTE_KEY = 'ZSET:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_WEEK_KEY = 'ZSET:TARGET:WEEK:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s';

    const STATUS_ACTIVE = 1;
    const STATUS_END = 2;
    const STATUS_GIVE_UP = 3;

    /**
     * 创建目标，仅当前不存在其他活跃目标时，可创建
     *
     * @param $target
     * @return mixed
     * @throws
     */
    public static function createTarget($target)
    {
        // 按第一版设计，希望每个用户只有一个target，但是后续可以扩展到有多个target，如果不在表上加唯一索引，那么这里会存在并发问题。
        // 那么这里要上事务，而且事务隔离级别为 可重复读。（可以解决吗？）
        $database = self::instance();
        $database->action(function ($database) use (&$target) {
            $exist = $database->get("target", "*", ["userId" => $target['userId'], "status" => self::STATUS_ACTIVE]);
            if ($exist) {
                return false;
            }
            $database->insert("target", $target);
            $target['id'] = $database->id();
        });
        Redis::instance()->hMset(sprintf(self::TARGET_KEY, $target['userId']), $target);
        return $target;
    }

    /**
     * 获取用户的目标，默认获取活跃目标
     *
     * @return array|null
     * @throws \HttpServer\component\HabitException
     */
    public static function getUserTarget()
    {
        $user = Auth::getUser();
        $key = sprintf(self::TARGET_KEY, (int)$user->userId);
        return Redis::instance()->hGetAll($key) ?: null;
//        $empty = [
//            'action' => '',
//            'number' => '',
//            'time' => '',
//        ];
//        return Redis::instance()->hGetAll($key) ?: null;
//        if (!$target) {
//            $target = self::getUserActiveTarget($user);
//            $target = $target ?: $empty;
//            Redis::instance()->hMSet($key, $target);
//        }
//        return $target;
    }

    public static function getUserActiveTarget($user)
    {
        return self::instance()->get("target", "*", ["userId" => $user->userId, "status" => self::STATUS_ACTIVE]);
    }

    public static function getTargetById($targetId)
    {
        return self::instance()->get("target", "*", ["id" => $targetId]);
    }

}
