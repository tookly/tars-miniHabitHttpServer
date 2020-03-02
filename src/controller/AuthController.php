<?php

namespace HttpServer\controller;

use HttpServer\component\Controller;
use HttpServer\component\Auth;
use HttpServer\component\HabitException;
use HttpServer\conf\Code;

class AuthController extends Controller
{

    /**
     * @throws \HttpServer\component\HabitException
     */
    public function actionLogin()
    {
        $code = $this->getPost('code', '');
        if (empty($code)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        Auth::login($code);
    }
    
}
