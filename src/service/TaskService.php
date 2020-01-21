<?php
namespace HttpServer\service;

use HttpServer\conf\Code;

class TaskService
{
    public static function getTypes() {
        return [];
    }

    public static function getLists() {
        return [];
    }

    public static function getList($type) {
        return [];
    }

    public static function add($task, $type) {
        return '';
    }

    public static function finish($taskId) {
        return Code::SUCCESS;
    }

    public static function remove($taskId) {
        return true;
    }
}
