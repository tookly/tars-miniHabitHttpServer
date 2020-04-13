<?php

namespace HttpServer\model;

use HttpServer\component\HabitException;
use HttpServer\component\Model;
use HttpServer\conf\Code;

/**
 * Class UserModel
 * 三个user：UserModel 缓存、DB中的User 落地、BasicUserInfo 返回前端
 * @package HttpServer\model
 */
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
    
    public function getBasicInfo()
    {
        return [
            'userId' => $this->userId,
            'nickName' => $this->nickName,
            'avatar' => $this->avatar,
        ];
    }
    
    /**
     * 使用反射并不明智
     *
     * @param UserModel $user
     * @param $info
     * @throws \ReflectionException
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
     * @param $code
     * @throws HabitException
     * @return array
     */
    public static function getUserByCode($code)
    {
        if (empty($code)) {
            throw new HabitException(Code::LOGIN_FAILED);
        }
        $info = WechatModel::getOpenIdByCode($code);
        try {
            // 因为同一个微信账号的openId是一样的，而openId在数据是唯一索引，这里不会有并发问题。
            $user = self::instance()->get("user", "*", ["openId" => $info['openid']]);
            if (empty($user)) {
                $userInfo['openId'] = $info['openid'];
//                $user['sessionKey'] = $info['session_key'];
//                $user['nickName'] = $info['nickName'];
//                $user['avatarUrl'] = $info['avatarUrl'];
//                $user['gender'] = $info['gender'];
//                $user['country'] = $info['country'];
//                $user['province'] = $info['province'];
//                $user['city'] = $info['city'];
//                $user['language'] = $info['language'];
                $userInfo['createdAt'] = date('Y-m-d H:i:s');
                $userInfo['updatedAt'] = date('Y-m-d H:i:s');
                self::instance()->insert("user", $userInfo);
                $user['userId'] = self::instance()->id();
            }
            return $user;
        } catch (\Exception $e) {
            throw new HabitException(Code::LOGIN_FAILED);
        }
    }
    
}