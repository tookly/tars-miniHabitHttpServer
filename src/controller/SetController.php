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
use HttpServer\service\TargetService;

class SetController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionSet()
    {
        $this->checkLogin();
        $target = $this->getPost('target', '');
        $time = $this->getPost('time', 0);
        $timeSuffix = $this->getPost('timeSuffix', '');
        $number = $this->getPost('number', 0);
        if (empty($target) || empty($time) || empty($number)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TargetService::set($target, $time, $timeSuffix, $number);
    }
}
