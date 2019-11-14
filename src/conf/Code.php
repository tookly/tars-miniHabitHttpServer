<?php

namespace HttpServer\conf;

/**
 * 定义错误提示码
 * 支持定义code和message，如 const SUCCESS = [0, 'success'];
 * 支持仅定义code，如 const SUCCESS = 0; 此时message显示为 未定义异常
 * message均可被Exception的message覆盖
 */
class Code
{
    // 0-1000 通用提示码
    const SUCCESS = [0, 'success'];
    const FAIL = [1, 'fail'];
    const ERROR_PARAMS = [2, 'error params'];
    const LOGIN_FAILED = [3, 'login failed'];
    
    // 1000 - 业务提示码
    
}
