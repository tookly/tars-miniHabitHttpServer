<?php

namespace model;

use common\HttpUtil;
use HttpServer\component\Model;
use HttpServer\conf\Code;

class WechatModel extends Model
{
    const GrantType = 'client_credential';
    const AppId = 'wx2b9e3aa781b99d6c';
    const AppSecret = '471c56e0c33f23eb9961716393c8a279';
    
    const TEMPLATE_ID_NEW_CALL = 1;
    const TEMPLATE_ID_NEW_REMIND = 2;
    const TEMPLATE_ID_OLD_CALL = 3;
    const TEMPLATE_ID_EXCHANGE_REMIND = 4;
    
    const TARGET_STATE_INIT = 0;
    const TARGET_STATE_OK = 1;
    
    const TEMPLATE_ID_REMIND = '8i5pEOmqAvliAm9kK4GVDVbejkG6T17Uny3rcuBl2yA';
    const TEMPLATE_EXCHANGE_REMIND = 'IQzU_x4bOGoI0ew5XPVa0rxvFqI-J--dC2RGI9IZOJI';
    
    const TEMPLATE_USERS = 'ZSET:TEMPLATE:USERS:%s'; // 有formId的用户，预留一位hash，score：userId value：formId个数
    const TEMPLATE_USER_FROMID = 'LIST:TEMPLATE:USER:FORMID:%d'; // 用户的formId，score：时间戳 value：formId
    const USER_WECHAT_INFO = 'HASH:INFO:WECHAT:%d'; // 用户的微信信息，openId、昵称、头像等，存一份。
    const OPENID_WECHAT_INFO = 'HASH:INFO:WECHAT:OPENID:%s'; // 微信openId的openId、昵称、头像等，存一份。
    
    public static $getAccessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token';
    public static $getWxaCodeUnlimitUrl = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';
    public static $getWxaCodeUrl = 'https://api.weixin.qq.com/wxa/getwxacode';
    public static $sendTemplateMessageUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send';
    public static $createActivityUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/activityid/create';
    public static $updateActivityUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/updatablemsg/send';
    public static $code2session = 'https://api.weixin.qq.com/sns/jscode2session';


//    public static function getTemplateUserKey($userId = 0)
//    {
//        return sprintf(self::TEMPLATE_USERS, 'T');
//    }
//
//    public static function getTemplateUserFormIdKey($userId)
//    {
//        return sprintf(self::TEMPLATE_USER_FROMID, $userId);
//    }
//
//    /**
//     * @param $userId
//     * @param $formId
//     * @param string $openId
//     * @throws
//     */
//    public static function collectFormId($userId, $formId, $openId)
//    {
//        if (empty($userId) || empty($formId)) {
//            return;
//        }
//        $redisInstance = self::getRedis();
//        if ($redisInstance == null) {
////            self::logError(sprintf("redis instance is null"), -1, __METHOD__);
//            throw new \Exception(Code::FAIL);
//        } else {
//            $redisInstance->zIncrBy(self::getTemplateUserKey($userId), 1, $userId);
//            $redisInstance->lPush(self::getTemplateUserFormIdKey($userId),
//                json_encode(['time' => time(), 'formId' => $formId, 'openId' => $openId]));
//        }
//    }
//
//    /**
//     * @return string
//     */
//    protected static function getAccessToken()
//    {
//        $data = [
//            'grant_type' => self::GrantType,
//            'appid' => self::AppId,
//            'secret' => self::AppSecret,
//        ];
//        $res = HttpUtil::get(self::$getAccessTokenUrl, $data);
//        $res = json_decode($res, true);
//        if (empty($res) || empty($res['access_token'])) {
//            self::logError('getAccessToken failed res:' . var_export($res), 0, __METHOD__);
//            return '';
//        }
//        return $res['access_token'];
//    }
//
//    /**
//     * @param $path
//     * @return array|mixed|string
//     * @throws
//     */
//    public static function getWxaCode($path)
//    {
//        $accessToken = self::getAccessToken();
//        if (empty($accessToken)) {
//            return '';
//        }
//        $data = [
//            'path' => $path,
//        ];
//        return HttpUtil::post(self::$getWxaCodeUrl . '?access_token=' . $accessToken, $data);
//    }
//
//    /**
//     * @param $scene
//     * @param $page
//     * @param string $width
//     * @param bool $auto_color
//     * @param bool $line_color
//     * @param bool $is_hyaline
//     * @return array|mixed|string
//     * @throws
//     */
//    public static function getWxaCodeUnlimit(
//        $scene,
//        $page,
//        $width = '430',
//        $auto_color = false,
//        $line_color = false,
//        $is_hyaline = false
//    ) {
//        $accessToken = self::getAccessToken();
//        if (empty($accessToken)) {
//            return '';
//        }
//        $data = [
//            'scene' => $scene,
//            'page' => $page,
//            'width' => $width,
//        ];
//        $auto_color && $data['auto_color'] = $auto_color;
//        $line_color && $data['line_color'] = $line_color;
//        $is_hyaline && $data['is_hyaline'] = true;
//        return HttpUtil::post(self::$getWxaCodeUnlimitUrl . '?access_token=' . $accessToken, $data);
//    }
//
//    /**
//     * @param $touser
//     * @param $templateId
//     * @param $page
//     * @param $formId
//     * @param $data
//     * @param $emphasisKeyword
//     * @return bool|mixed
//     * @throws \Exception
//     */
//    public static function sendTemplateMessage(
//        $touser,
//        $templateId,
//        $formId,
//        $data = [],
//        $page = '',
//        $emphasisKeyword = ''
//    ) {
//        $accessToken = self::getAccessToken();
//        if (empty($accessToken)) {
//            throw new \Exception(Code::FAIL, 'get accessToken failed');
//        }
//        $templateData = [
//            "touser" => $touser,
//            "template_id" => $templateId,
//            "form_id" => $formId,
//        ];
//        $page && $templateData['page'] = $page;
//        $data && $templateData['data'] = $data;
//        $emphasisKeyword && $templateData['emphasis_keyword'] = $emphasisKeyword;
//        $ret = HttpUtil::post(self::$sendTemplateMessageUrl . '?access_token=' . $accessToken, $templateData);
//        if (empty($ret) || $ret['errcode'] != 0) {
//            $code = $ret['errcode'] ?? Code::FAIL;
//            $message = $ret['errmsg'] ?? '';
//            throw new \Exception($code, $message);
//        }
//    }
//
//    /**
//     * @return mixed
//     * @throws
//     */
//    public static function createActivity()
//    {
//        $accessToken = self::getAccessToken();
//        if (empty($accessToken)) {
//            throw new \Exception(Code::FAIL, 'get accessToken failed');
//        }
//        $ret = HttpUtil::get(self::$createActivityUrl . '?access_token=' . $accessToken);
//        $ret = json_decode($ret, true);
//        if (empty($ret) || $ret['errcode'] != 0) {
//            $code = $ret['errcode'] ?? Code::FAIL;
//            $message = $ret['errmsg'] ?? '';
//            throw new \Exception($code, $message);
//        }
//        return $ret ?? [];
//    }
//
//    /**
//     * @return mixed
//     * @throws
//     */
//    public static function updateActivity($activityId, $targetState, $templateInfo)
//    {
//        $accessToken = self::getAccessToken();
//        if (empty($accessToken)) {
//            throw new \Exception(Code::FAIL, 'get accessToken failed');
//        }
//        $data = [
//            'activity_id' => $activityId,
//            'target_state' => $targetState,
//            'template_info' => $templateInfo,
//        ];
//        $ret = HttpUtil::post(self::$updateActivityUrl . '?access_token=' . $accessToken, $data);
//        if (empty($ret) || $ret['errcode'] != 0) {
//            $code = $ret['errcode'] ?? Code::FAIL;
//            $message = $ret['errmsg'] ?? '';
//            throw new \Exception($code, $message);
//        }
//    }
    
    /**
     * @param $code
     * @return mixed|string
     * @throws
     */
    public static function getOpenIdByCode($code)
    {
        $data = [
            'appid' => self::AppId,
            'secret' => self::AppSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $ret = HttpUtil::get(self::$code2session, $data);
        $ret = json_decode($ret, true);
        if (empty($ret) || !empty($ret['errcode'])) {
            $code = $ret['errcode'] ?? Code::FAIL;
            $message = $ret['errmsg'] ?? '';
//            self::logError('get openId failed. ' . json_encode($ret), $ret['errcode'], __METHOD__);
            throw new \Exception($code, $message);
        }
        return $ret ?: [];
    }
    
}
