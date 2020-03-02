<?php

namespace HttpServer\component;

use HttpServer\conf\Code;
use HttpServer\model\UserModel;

class Auth
{

    const USER_SSO_SESSION = "STR:SSO:%s";

    /**
     * @param $session
     * @param $user
     * @return bool
     * @throws \ReflectionException
     */
    public static function verify($session, &$user)
    {
        if (!$session) {
            return false;
        }
        $info = Redis::instance()->get(sprintf(self::USER_SSO_SESSION, $session));
        if (empty($info)) {
            return false;
        }
        $info = json_decode($info, true);
        UserModel::genUser($user, $info);
        if (empty($user->userId) || $user->userId <= 0) {
            return false;
        }
        return true;
    }

    /**
     * @return UserModel
     * @throws HabitException
     */
    public static function getUser()
    {
        if (Controller::getController() == null) {
            throw new HabitException();
        }
        return Controller::getController()->getUser();
    }

    /**
     * @param $code
     * @throws HabitException
     */
    public static function login($code)
    {
        $controller = Controller::getController();
        if ($controller == null) {
            throw new HabitException();
        }
        $user = UserModel::getUserByCode($code);  // 此处返回用户基本信息，但会丢失用户的登录相关信息
        $controller->setSession($user);
    }

    /**
     * @throws HabitException
     */
    public static function checkLogin()
    {
        $user = self::getUser();
        if (!$user || $user->userId <= 0) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
    }

    /**
     * @return bool
     * @throws HabitException
     */
    public static function isLogin()
    {
        $user = self::getUser();
        if (!$user && $user->userId <= 0) {
            return true;
        }
        return false;
    }

}