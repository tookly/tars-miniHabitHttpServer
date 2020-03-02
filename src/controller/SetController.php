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
        $target = $this->getPost('target', '');
        $time = $this->getPost('time', 0);
        $number = $this->getPost('number', 0);
        if (empty($target) || empty($time) || empty($number)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        return TargetService::set($target, $time, $number);
    }
}
