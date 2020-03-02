<?php

namespace HttpServer\component;

class HabitException extends \Exception
{
    
    public function __construct($info = 10000, $newMessage = '')
    {
        if (is_array($info)) {
            list($code, $message) = $info;
        } else {
            $code = $info;
            $message = '未定义异常';
        }
        $message = $newMessage ?: $message;
        parent::__construct($message, $code);
    }
    
}
