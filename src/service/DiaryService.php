<?php
namespace HttpServer\service;

use HttpServer\component\Redis;
use HttpServer\conf\Code;
use HttpServer\model\DiaryModel;
use HttpServer\component\Auth;
use HttpServer\component\HabitException;

class DiaryService
{

    public static function getList($page = 1, $pageSize = 50) {
        return DiaryModel::getUserDiaryList($page, $pageSize);
//        $result = Redis::instance()->lrange(sprintf(self::LIST_DIARY, date('Y'), 0, 50));
//        return $result;
    }

    /**
     * @param $content
     * @return array
     * @throws
     */
    public static function write($content) {
        $diary = [
            "userId" => Auth::getUser()->userId,
            "content" => $content,
            "weather" => '',
            "status" => DiaryModel::STATUS_ACTIVE,
            "createdAt" => date('Y-m-d H:i:s'),
            "updatedAt" => date('Y-m-d H:i:s'),
        ];
        DiaryModel::addDiary($diary);
        return $diary;
    }

    /**
     * @param $id
     * @param $content
     */
    public static function edit($id, $content) {
        $diary = [
            "content" => $content,
            "updatedAt" => date('Y-m-d H:i:s'),
        ];
        DiaryModel::updateDiary($id, $diary);
    }

    /**
     * @param $id
     * @throws
     */
    public static function remove($id) {
        $diary = [
            "status" => DiaryModel::STATUS_DELETE,
            "updatedAt" => date('Y-m-d H:i:s'),
        ];
        DiaryModel::updateDiary($id, $diary);
    }

    public static function getTemplates() {
        return [];
    }

    // 默认模板，每日四问（使用html模板渲染？）
    public static function getTemplate($templateId = 1) {
        return '';
    }

}
