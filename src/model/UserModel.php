<?php
/**
 * Created by PhpStorm.
 * User: chengxiaoli
 * Date: 2019/8/19
 * Time: 下午7:09
 */

namespace HttpServer\model;

use HttpServer\component\HabitException;
use HttpServer\component\Model;
use HttpServer\component\Redis;
use HttpServer\conf\Code;

class UserModel extends Model
{
    const USER_SSO_SESSION = "STR:SSO:%s";
    
    public $userId;             // 用户id
    public $nickName;           // 用户昵称
    public $avatar;             // 用户头像Url
    public $clientIP;           // 用户Ip
    public $userAgent;          // 用户UserAgent
    public $referer;            // 用户请求的http referer - 请求的来源
    public $registerTime;       // 用户注册时间
    public $openId;             // 用户最近一次登录小程序的openId
    public $unionId;            // 用户最近一次登录小程序的onionId
    public $sessionKey;         // 用户最近一次登录小程序的sessionKey
    public $dateline;           // 用户最近一次登录小程序的openId的更新时间
    public $code;               // 起点读书小程序授权code 存在有效期，失效需要向前端重新获取
    
    public function getBasicUserInfo()
    {
        return [
            'userId' => $this->userId,
            'nickName' => $this->nickName,
            'avatar' => $this->avatar,
        ];
    }
    
    /**
     * @param UserModel $user
     * @param $info
     * @throws ReflectionException
     */
    public static function genUser(UserModel &$user, $info)
    {
        $reflect = new \ReflectionClass(UserModel::class);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            if (isset($info[$propName])) {
                $user->$propName = $info[$propName] ?? '';
            }
        }
    }
    
    /**
     * @param $cookie
     * @param UserModel $user
     * @return bool
     * @throws Exception
     */
    public static function verify($cookie, UserModel &$user)
    {
        $session = $cookie['session'] ?? '';
        if (!$session) {
            return false;
        }
        $info = Redis::instance()->get(sprintf(self::USER_SSO_SESSION, $session));
        if (empty($info)) {
            return false;
        }
        $info = json_decode($info, true);
        self::genUser($user, $info);
        if (empty($user->userId)) {
            return false;
        }
        return true;
    }
    
    /**
     * @param $code
     * @throws HabitException
     * @return array
     */
    public static function login($code)
    {
        if (empty($code)) {
            throw new HabitException(Code::LOGIN_FAILED);
        }
        try {
            $info = WechatModel::getOpenIdByCode($code);
            if (!$info || empty($info['openId'])) {
                throw new HabitException(Code::LOGIN_FAILED);
            }
            $user = self::instance()->get("user", "openId", ["openId" => $info['openId']]);
            if (empty($user)) {
                $userInfo['openId'] = $info['openId'];
                $userInfo['nickName'] = $info['nickName'];
                $userInfo['avatarUrl'] = $info['avatarUrl'];
                $userInfo['gender'] = $info['gender'];
                $userInfo['country'] = $info['country'];
                $userInfo['province'] = $info['province'];
                $userInfo['city'] = $info['city'];
                $userInfo['language'] = $info['language'];
                self::instance()->insert("user", $user);
            }
            $session = md5(time() . "_" . mt_rand()) . '_' . uniqid();
            Redis::instance()->set(sprintf(self::USER_SSO_SESSION, json_encode($user)));
            return [$session, $user];
        } catch (\Exception $e) {
            throw new HabitException(Code::LOGIN_FAILED);
        }
    }
    
}