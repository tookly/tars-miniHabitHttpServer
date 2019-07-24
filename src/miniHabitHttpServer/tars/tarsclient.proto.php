<?php
/**
 * Created by PhpStorm.
 * User: chengxiaoli
 * Date: 2019/5/17
 * Time: 下午8:06
 */
return array(
    'appName' => 'php',
    'serverName' => 'miniHabitHttpServer',
    'objName' => 'obj',
    'withServant' => false,//决定是服务端,还是客户端的自动生成
    'tarsFiles' => array(
        './example.tars'
    ),
    'dstPath' => '../src/servant',
    'namespacePrefix' => 'HttpServer\servant',
);