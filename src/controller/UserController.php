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
     * @throws \Exception
     * @throws \HttpServer\component\HabitException
     */
    public function actionLogin()
    {
        $code = $this->getPost('code', '');
        $user = UserModel::login($code);
        $this->setSession($user);
    }
    
}
