<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\service\DiaryService;
use HttpServer\service\TargetService;
use HttpServer\component\Auth;

class IndexController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionIndex()
    {
        Auth::checkLogin();
        $pageIndex = $this->getGet('pageIndex', 1);
        $pageSize = $this->getGet('pageSize', 50);
        $data['target'] = TargetService::getString();
        $data['dailyList'] = DiaryService::getList($pageIndex, $pageSize);
        return $data;
    }

    /**
     * @throws HabitException
     */
    public function actionSign()
    {
        Auth::checkLogin();
        return TargetService::sign();
    }
    
    /**
     * @throws HabitException
     */
    public function actionWriteDiary()
    {
        Auth::checkLogin();
        $content = $this->getPost('content', '');
        return DiaryService::write($content);
    }

    /**
     * @return array
     */
    public function actionGetDiaryTemplates()
    {
        return DiaryService::getTemplates();
    }

}
