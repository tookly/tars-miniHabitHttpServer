<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;

class TargetModel extends Model
{
    const TARGET_KEY = 'HASH:TARGET:%s';
    const TARGET_NOTE_KEY = 'ZSET:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_WEEK_KEY = 'ZSET:TARGET:WEEK:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s';
    
    public static function getInfo($user)
    {
        return Redis::instance()->hGetAll(sprintf(self::TARGET_KEY, (int)$user->userId)) ?: null;
    }
}
