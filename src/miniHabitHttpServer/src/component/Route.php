<?php
/**
 * Created by PhpStorm.
 * User: chengxiaoli
 * Date: 2019/7/17
 * Time: 下午4:35
 */

namespace HttpServer\component;

use Tars\Core\Request;
use Tars\Core\Response;

class MyRoute implements \Tars\core\Route {
    
    public function dispatch(Request $request, Response $response)
    {
        //dispatch
    }
    
}