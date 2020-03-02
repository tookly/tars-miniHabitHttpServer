<?php
namespace HttpServer\service;

use HttpServer\conf\Code;

class DiaryService
{
    public static function getList($pageIndex = 1, $pageSize = 50) {
        return [];
    }

    public static function write($content) {
        return Code::SUCCESS;
    }

    public static function getTemplates() {
        return [];
    }

    // 默认模板，每日四问（使用html模板渲染？）
    public static function getTemplate($templateId = 1) {
        return '';
    }

}
