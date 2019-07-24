<?php
$app['router']->get('/',function(){
    return '<h1>Yeah！路由安装成功！</h1>';
});

$app['router']->get('hello', function (){
    return '<h1>Hello World</h1>';
});

$app['router']->get('/welcome','App\Http\Controllers\WelcomeController@index');