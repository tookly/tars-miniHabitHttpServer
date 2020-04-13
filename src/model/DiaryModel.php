<?php

namespace HttpServer\model;

use HttpServer\component\Model;
use HttpServer\component\Redis;
use HttpServer\component\Auth;

class DiaryModel extends Model
{
    // 日志要入库，这里的redis仅做缓存
    // 好的方法，其实就两种，一种是被动缓存，一种是消息队列更新（这两种都会存在延迟）
    const LIST_DIARY = "LIST:DIARY:UPDATE:%s"; // {user_id}

    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    public static function getUserDiaryList($page, $pageSize = 50)
    {
        // 这里仅从redis读取日志列表，缓存就是一个大的str，用list维护很麻烦
        // 这里先直接读库
//        $user = Auth::getUser();
//        $list = Redis::instance()->lrange(sprintf(self::LIST_DIARY, $user->userId, $page), 0, -1);
//        if (empty($list)) {
//
//        }
        return self::getDiaryByUserId(Auth::getUser()->userId, $page, $pageSize);
    }

    /**
     * 获取用户最新的日志，需要翻页，可以无限翻
     *
     * @param $userId
     * @param int $page
     * @param int $pageSize
     * @return mixed
     * @throws \Exception
     */
    public static function getDiaryByUserId($userId, $page = 1, $pageSize = 50)
    {
        return self::instance()->select(
            "diary",
            "*",
            ["userId" => $userId, "status" => 1],
            ["ORDER" => ['createdAt' => 'DESC'], "LIMIT" => [$page - 1, ($page - 1) * $pageSize]]
        );
    }

    /**
     * 写随笔
     *
     * @param $diary
     * @throws \Exception
     */
    public static function addDiary($diary)
    {
        // 在新增日志时，将修改入库，并发消息给下游更新缓存。(使用消息队列）
        self::instance()->insert("diary", $diary);
//        Redis::instance()->lpush(sprintf(self::LIST_DIARY, $diary['userId'], 0), json_encode($diary));
    }

    /**
     * 删除随笔、更新随笔内容等
     *
     * @param $diary
     * @throws \Exception
     */
    public static function updateDiary($diary)
    {
        self::instance()->update("diary", $diary['id'], $diary);
    }

}
