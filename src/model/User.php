<?php
/**
 * Created by PhpStorm.
 * User: chengxiaoli
 * Date: 2019/8/19
 * Time: 下午7:09
 */

use HttpServer\component\Model;

class User extends Model
{
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
   
    public static function verify($cookie, &$user, &$isLogin)
    {
        // todo 校验用户cookie，并完善用户信息
//        $data['data']['user'] = [
//            'isLogin' => $this->isLogin(),
//            'guid' => $this->user->ywGuid,
//            'avatar' => $this->user->avatar,
//            'nickName' => $this->user->nickName,
//            'userId' => $this->user->userId,
//            'ywGuid' => $this->user->ywGuid,
//        ];
    }
    
    public static function login()
    {
    
    }
    
}