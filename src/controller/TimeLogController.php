<?php
/**
 * User: chengxiaoli
 * Date: 2019/12/31
 * Time: 下午14:30.
 */

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\service\TaskService;
use HttpServer\service\TimeGridService;

class TimeLogController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionGetDayGrid()
    {
        $this->checkLogin();
        $data['date'] = date('Y.m.d 第W周', time());
        $data['dayGrid'] = TimeGridService::getDayGrids();
        return $data;
    }
    
    /**
     * @throws HabitException
     */
    public function actionFillGrids()
    {
        $this->checkLogin();
        $grids = $this->getPost('grids', '');
        $taskId = $this->getPost('taskId', 0);
        if (empty($grids) || empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TimeGridService::fillDayGrids($grids, $taskId);
    }
    
    /**
     * @throws HabitException
     */
    public function actionGetTasks()
    {
        $this->checkLogin();
        return TaskService::getLists();
    }

    /**
     * @throws HabitException
     */
    public function actionFinishTask()
    {
        $this->checkLogin();
        $taskId = $this->getPost('taskId', 0);
        if (empty($taskId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TaskService::finish($taskId);
    }

    /**
     * @throws HabitException
     */
    public function actionAddTask()
    {
        $this->checkLogin();
        $task = $this->getPost('task', '');
        $type = $this->getPost('type', 0);
        if (empty($task) || empty($type)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TaskService::add($task, $type);
    }
}
