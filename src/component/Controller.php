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
use HttpServer\model\UserModel;

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
        $this->user = new UserModel();
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
    
    public function sendSuccess($data = null)
    {
        list($data['code'], $data['message']) = Code::SUCCESS;
        $this->send($data);
    }
    
    public function sendByException(\Exception $e, $data = null)
    {
        $data['code'] = $e->getCode();
        $data['message'] = $e->getMessage();
        $this->send($data);
    }
    
    public function send($data)
    {
        $this->header('Content-Type', 'application/json');
        $this->response->send(json_encode($data));
    }
    
    public function run($actionName)
    {
        $data['isLogin'] = UserModel::verify($this->cookies, $this->user);
        $data['user'] = $this->user->getBasicUserInfo();
        try {
            $result = $this->$actionName();
            $data['data'] = $result ?? null;
            $this->sendSuccess($data);
        } catch (\Exception $e) {
            $this->sendByException($e, $data);
        }
    }
    
}
