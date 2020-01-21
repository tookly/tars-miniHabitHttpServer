<?php

namespace HttpServer\common;

class CurlTool
{
    public static $connect_timeout_ms = 1000;
    public static $timeout_ms = 3000;
    
    public static function get($url, $data = [])
    {
        if (!empty($data)) {
            $query = http_build_query($data);
            $url = $url . '?' . $query;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::$connect_timeout_ms);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$timeout_ms);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_ENCODING, '');
        $body = curl_exec($ch);
        $no = curl_errno($ch);
        if ($no !== 0) {
            $error = curl_error($ch);
//            self::logError(sprintf('%s error(%s)', $url, $error), '', __METHOD__);
        }
        curl_close($ch);
//        self::logInfo(sprintf('get from result %s : %s', $url, $body), '', __METHOD__);
        return $body ? $body : '';
    }
    
    public static function post($url, $data = [], $header = false)
    {
        $ch = curl_init();
        $data = json_encode($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::$connect_timeout_ms);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::$timeout_ms);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        $body = curl_exec($ch);
        $no = curl_errno($ch);
        if ($no !== 0) {
            $error = curl_error($ch);
//            self::logError(sprintf('%s error(%s)', $url, $error), $no, __METHOD__);
        }
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
//        self::logMyDebug(sprintf('get from result %s : %s, content-type %s', $url, $body, $content_type));
        curl_close($ch);
        if (strpos($content_type, 'json') !== false) {
            $res = $body ? json_decode($body, true) : [];
        } else if (strpos($content_type, 'image') !== false) {
            $res = $body ?: '';
        }
//        self::logInfo(sprintf('get from result %s : %s, content-type %s', $url, $body, $content_type), $no, __METHOD__);
        return $res ?? '';
    }
    
}
