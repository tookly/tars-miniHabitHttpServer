<?php
/**
 * User: chengxiaoli
 * Date: 2019/12/31
 * Time: 下午14:30.
 */

namespace HttpServer\controller;

use HttpServer\component\Controller;
use HttpServer\model\UserModel;

class AuthController extends Controller
{
    
    public function actionLogin()
    {
        $code = $this->getPost('code', '');
        $user = UserModel::login($code);
        return $this->setSession($user);
    }
    
}
