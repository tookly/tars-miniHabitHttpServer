<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\service\TargetService;
use HttpServer\service\TimeGridService;
use HttpServer\component\Auth;

class StatisticsController extends Controller
{

    /**
     * 主态 和 客态
     * @throws HabitException
     */
    public function actionStatistics()
    {
        Auth::checkLogin();
        $data['target'] = TargetService::statistics();
        $data['timeGrid'] = TimeGridService::statistics();
        return $data;
    }

    public function actionUserStatistics()
    {
        $userId = self::getPost('userId', 0);
        $data['target'] = TargetService::statistics();
        $data['timeGrid'] = TimeGridService::statistics();
        return $data;
    }
    
}
