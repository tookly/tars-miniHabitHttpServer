<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\service\TaskService;
use HttpServer\service\TimeGridService;
use HttpServer\component\Auth;

class TimeLogController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionGetDayGrid()
    {
        Auth::checkLogin();
        $data['date'] = date('Y.m.d 第W周', time());
        $data['dayGrid'] = TimeGridService::getDayGrids();
        return $data;
    }
    
    /**
     * @throws HabitException
     */
    public function actionFillGrids()
    {
        Auth::checkLogin();
        $grids = $this->getPost('grids', '');
        $taskId = $this->getPost('taskId', 0);
        if (empty($grids) || empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TimeGridService::fillDayGrids($grids, $taskId);
    }

    /**
     *
     */
    public function actionStartTasks()
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
