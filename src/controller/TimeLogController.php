<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\model\TimeGridModel;
use HttpServer\service\TaskService;
use HttpServer\service\TimeGridService;
use HttpServer\component\Auth;

class TimeLogController extends Controller
{

    // 这里格子的填充方式有点迷，缓一缓再写。

    /**
     * @return mixed
     * @throws HabitException
     */
    public function actionIndex()
    {
        Auth::checkLogin();
        $data['date'] = date('Y.m.d 第W周', time());
        $data['dayGrids'] = TimeGridService::getTodayGrids();
        return $data;
    }

    /**
     * 填充时间段，暂时不支持
     *
     * @return array
     * @throws HabitException
     */
    public function actionFillDuration()
    {
        Auth::checkLogin();
        $startTime = $this->getPost('startTime', '');
        $endTime = $this->getPost('endTime', '');
        $taskId = $this->getPost('taskId', 0);
        if (empty($startTime) || empty($endTime) || empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TimeGridService::fillDuration($startTime, $endTime, $taskId);
    }

    /**
     * @throws HabitException
     */
    public function actionFillWithGrids()
    {
        Auth::checkLogin();
        $grids = $this->getPost('grids', '');
        $taskId = $this->getPost('taskId', 0);
        $content = $this->getPost('content', '');
        if (empty($grids) || empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TimeGridService::fillWithGrids($grids, $taskId, $content);
    }

    /**
     * @return array
     * @throws HabitException
     */
    public function actionStartTask()
    {
        Auth::checkLogin();
        $taskId = $this->getPost('taskId', 0);
        if (empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TaskService::start($taskId);
    }

    /**
     * @throws HabitException
     */
    public function actionFinishTask()
    {
        Auth::checkLogin();
        $taskId = $this->getPost('taskId', 0);
        if (empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TaskService::finish($taskId);
    }
    
}
