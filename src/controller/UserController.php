<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:42.
 */

namespace HttpServer\controller;

use HttpServer\component\Redis;
use HttpServer\component\Controller;
use model\WechatModel;

class UserController extends Controller
{
    const TARGET_KEY = 'HASH:TARGET:%s';
    const TARGET_NOTE_KEY = 'ZSET:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_WEEK_KEY = 'ZSET:TARGET:WEEK:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s';
    
    /**
     * @throws \exception
     */
    public function actionLogin()
    {
        $code = $this->getGet('code', '');
        $ret = WechatModel::getOpenIdByCode($code);
        // todo 插入表
        // todo 登录过程，设置cookie
        $this->sendSuccess($data);
    }
    
}
