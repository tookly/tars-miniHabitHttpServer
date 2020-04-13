<?php

namespace HttpServer\component;

use HttpServer\model\UserModel;
use Tars\core\Request;
use Tars\core\Response;
use HttpServer\conf\Code;
use Swoole\Coroutine;

class Controller
{
    private static $controller = [];

    protected $request;
    protected $response;
    
    protected $cookieData;
    protected $getData;
    protected $postData;

    protected $user;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     * @throws \ReflectionException
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->user = $this->initUser();
        self::saveController($this);
    }

    public static function saveController($controller)
    {
        $id = Coroutine::getuid();
        self::$controller[$id] = $controller;
    }

    /**
     * @return Controller
     */
    public static function getController()
    {
        $id = Coroutine::getuid();
        if (isset(self::$controller[$id])) {
            return self::$controller[$id];
        } else {
            return null;
        }
    }

    /**
     * @return UserModel
     * @throws \ReflectionException
     */
    public function initUser()
    {
        $user = new UserModel();
        if (isset($this->request->data['header']) && isset($this->request->data['header']['x-real-ip'])) {
            $user->clientIP = $this->request->data['header']['x-real-ip'];
        }
        if (isset($this->request->data['header']) && isset($this->request->data['header']['user-agent'])) {
            $user->userAgent = $this->request->data['header']['user-agent'];
        }
        if (isset($this->request->data['header']) && isset($this->request->data['header']['referer'])) {
            $user->referer = $this->request->data['header']['referer'];
        }
        Auth::verify($this->getCookie('session'), $user);
        return $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getGet($key, $default = null)
    {
        if (!isset($this->getData)) {
            $this->getData = $this->request->data['get'] ?? [];
        }
        return $this->getData[$key] ?? $default;
    }

    public function getPost($key, $default = null)
    {
        if (!isset($this->postData)) {
            if (isset($this->request->data['header']['content-type']) && strpos($this->request->data['header']['content-type'],
                    'json') !== false) {
                $this->postData = json_decode($this->request->data['post'], true) ?? [];
            } else {
                $this->postData = $this->request->data['post'] ?? [];
            }
        }
        return $this->postData[$key] ?? $default;
    }

    public function getCookie($key, $default = null)
    {
        if (!isset($this->cookieData)) {
            $this->cookieData = $this->request->data['cookie'] ?? [];
        }
        return $this->cookieData[$key] ?? $default;
    }
    
    public function header($key, $value)
    {
        $this->response->header($key, $value);
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

    /**
     * @param $user
     * @throws
     */
    public function setSession($user)
    {
        $session = md5(time() . "_" . mt_rand() . "_" . $user['userId']) . '_' . uniqid();
        Redis::instance()->set(sprintf(UserModel::USER_SSO_SESSION, $session), json_encode($user));
        $this->cookie("session", $session, time() + (730 * 24 * 3600), '/', '.snowfifi.com');
        $this->user->userId = $user['userId'];  // 前端未登录，应返回未登录态让前端发起登录，服务端登录成功后，更新userId供后续流程使用。
    }

    public function status($http_status_code)
    {
        $this->response->status($http_status_code);
    }

    public function send($data)
    {
        $this->header('Content-Type', 'application/json');
        $this->response->send(json_encode($data));
    }

    public function sendSuccess($data = null)
    {
        list($data['code'], $data['message']) = Code::SUCCESS;
        $this->send($data);
    }
    
    public function sendByException(\Exception $e)
    {
        $data['code'] = $e->getCode();
        $data['message'] = $e->getMessage();
        $this->send($data);
    }
    
    /**
     * @param $actionName
     * @throws
     */
    public function run($actionName)
    {
        try {
            $result = $this->$actionName();
            $data['user'] = $this->user->getBasicInfo();
            $data['data'] = $result ?? null;
            $this->sendSuccess($data);
        } catch (\Exception $e) {
            $this->sendByException($e);
        }
    }
    
}
