<?php
/**
 * User: chengxiaoli
 * Date: 2019/12/31
 * Time: 下午14:30.
 */

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\service\DiaryService;
use HttpServer\service\TargetService;

class IndexController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionIndex()
    {
        $this->checkLogin();
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
        $this->checkLogin();
        return TargetService::sign();
    }
    
    /**
     * @throws HabitException
     */
    public function actionWriteDiary()
    {
        $this->checkLogin();
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
