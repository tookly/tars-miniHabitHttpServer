<?php

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\service\TargetService;
use HttpServer\component\Auth;

class SetController extends Controller
{
    /**
     * @throws HabitException
     */
    public function actionSet()
    {
        Auth::checkLogin();
        $action = $this->getPost('action', '');
        $time = $this->getPost('time', 0);
        $number = $this->getPost('number', 0);
        $unit = $this->getPost('unit', 0);
        if (empty($action) || empty($time) || empty($number) || empty($unit)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TargetService::create($action, $time, $number, $unit);
    }
}
