<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:42.
 */

namespace HttpServer\controller;

use HttpServer\component\Controller;
use HttpServer\model\UserModel;

class UserController extends Controller
{
    
    /**
     * @throws \HttpServer\component\HabitException
     * @throws \HttpServer\model\ReflectionException
     */
    public function actionLogin()
    {
        $code = $this->getPost('code', '');
        list($user, $session) = UserModel::login($code);
        UserModel::genUser($this->user, $user);
        $this->response->cookie("session", $session, time() + (730 * 24 * 3600), '/', '.snowfifi.com');
        $this->sendSuccess();
    }
    
}
