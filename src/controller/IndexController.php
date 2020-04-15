<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\service\DiaryService;
use HttpServer\service\TargetService;
use HttpServer\component\Auth;
use HttpServer\conf\Code;

class IndexController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionIndex()
    {
        Auth::checkLogin();
        $page = $this->getGet('page', 1);
        $pageSize = $this->getGet('pageSize', 50);
        $data['target'] = TargetService::getUserTargetString();
        $data['times'] = TargetService::getUserTargetTimes();
        $data['dailyList'] = DiaryService::getList($page, $pageSize);
        return $data;
    }

    /**
     * @throws HabitException
     */
    public function actionSign()
    {
        Auth::checkLogin();
        $data['times'] = TargetService::sign();
        return $data;
    }
    
    /**
     * @throws
     */
    public function actionWriteDiary()
    {
        Auth::checkLogin();
        $content = $this->getPost('content', '');
        if (empty($content)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return DiaryService::write($content);
    }

    /**
     * @throws HabitException
     */
    public function actionEditDiary()
    {
        Auth::checkLogin();
        $id = $this->getPost('id', '');
        $content = $this->getPost('content', '');
        if (empty($id) || empty($content)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        DiaryService::edit($id, $content);
    }

    /**
     * @throws HabitException
     */
    public function actionRemoveDiary()
    {
        Auth::checkLogin();
        $id = $this->getPost('id', '');
        if (empty($id)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        DiaryService::remove($id);
    }

    /**
     * @return array
     */
    public function actionGetDiaryTemplates()
    {
        // todo 微信小程序虽然有富文本的展示组件，但是并没有富文本的编辑组件，这里先保持原始文本吧。
        return DiaryService::getTemplates();
    }

}
