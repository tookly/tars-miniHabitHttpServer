<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:43.
 */

namespace HttpServer\component;

class HabitException extends \Exception
{
    
    public function __construct($info, $newMessage = '')
    {
        list($code, $message) = $info;
        $message = $newMessage ?: $message;
        parent::__construct($message, $code);
    }
    
}
