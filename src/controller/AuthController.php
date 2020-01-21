<?php
/**
 * User: chengxiaoli
 * Date: 2019/12/31
 * Time: 下午14:30.
 */

namespace HttpServer\controller;

use HttpServer\component\Controller;
use HttpServer\component\Auth;

class AuthController extends Controller
{

    /**
     * @throws \HttpServer\component\HabitException
     */
    public function actionLogin()
    {
        $code = $this->getPost('code', '');
        Auth::login($code);
    }
    
}
