<?php
/**
 * User: chengxiaoli
 * Date: 2019/12/31
 * Time: 下午14:30.
 */

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Redis;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\model\TargetModel;
use HttpServer\service\TargetService;
use HttpServer\service\TimeGridService;

class StatisticsController extends Controller
{

    /**
     * 主态 和 客态
     * @throws HabitException
     */
    public function actionStatistics()
    {
        $this->checkLogin();
        $data['target'] = TargetService::statistics();
        $data['timeGrid'] = TimeGridService::statistics();
        return $data;
    }
    
}
