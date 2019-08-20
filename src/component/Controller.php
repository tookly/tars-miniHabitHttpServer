<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:43.
 */

namespace HttpServer\component;

use Tars\core\Request;
use Tars\core\Response;
use HttpServer\conf\Code;
use User;

class Controller
{
    protected $request;
    protected $response;
    
    protected $cookies;
    protected $postData;
    protected $getData;
    protected $user;
    
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        
        // 获取请求参数
        $this->getData = $this->request->data['get'] ?? [];
        $this->cookies = $this->request->data['cookie'] ?? [];
        if (isset($this->request->data['header']['content-type']) && strpos($this->request->data['header']['content-type'],
                'json') !== false) {
            $this->postData = json_decode($this->request->data['post'], true) ?? [];
        } else {
            $this->postData = $this->request->data['post'] ?? [];
        }
        
        // 缓存用户信息
        $this->user = new User();
        if (isset($this->request->data['header']) && isset($this->request->data['header']['x-real-ip'])) {
            $this->user->clientIP = $this->request->data['header']['x-real-ip'];
        }
        if (isset($this->request->data['header']) && isset($this->request->data['header']['user-agent'])) {
            $this->user->userAgent = $this->request->data['header']['user-agent'];
        }
        if (isset($this->request->data['header']) && isset($this->request->data['header']['referer'])) {
            $this->user->referer = $this->request->data['header']['referer'];
        }
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function cookie(
        $key,
        $value = '',
        $expire = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    // 给客户端发送数据
    public function sendRaw($result)
    {
        $this->response->send($result);
    }
    
    public function header($key, $value)
    {
        $this->response->header($key, $value);
    }
    
    public function status($http_status_code)
    {
        $this->response->status($http_status_code);
    }
    
    public function getCookie($key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }
    
    public function getGet($key, $default = null)
    {
        return $this->getData[$key] ?? $default;
    }
    
    public function getPost($key, $default = null)
    {
        return $this->postData[$key] ?? $default;
    }
    
    public function send($res)
    {
        $this->header('Content-Type', 'application/json');
        if (is_array($res['code'])) {
            $code = $res['code'];
            list($res['code'], $res['message']) = $code;
        }
        $this->response->send(json_encode($res));
    }
    
    public function sendSuccess($data = [])
    {
        $res['code'] = Code::SUCCESS;
        $res['data'] = $data;
        return $res;
    }
    
    public function sendParamErr($data = [])
    {
        $res['code'] = Code::ERROR_PARAMS;
        $res['data'] = $data;
        return $res;
    }
    
    public function run($actionName)
    {
        try {
            User::verify($this->cookies, $this->user, $isLogin);
            $result = $this->$actionName();
            $result['data']['user'] = $this->user;
            $this->send($result);
        } catch (\Exception $e) {
            $this->send($e);
        }
    }
    
}
