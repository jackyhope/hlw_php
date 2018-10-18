<?php

/**
 * API 请求处理
 * @author yanghao
 * @date 17-03-30
 *
 */
class Api_Request 
{
    const APPID = 'HLW_APPID';
    const SIGNATURE = 'HLW_SIGNATURE';
    const TIMESTAMP = 'HLW_TIMESTAMP';
    const SEQ = 'HLW_SEQ';
    const DEBUG = 'HLW_DEBUG';
    const INVOKE_SERVICE = 'INVOKE_SERVICE';
    const INVOKE_SERVICE_PARAMETERS = 'INVOKE_SERVICE_PARAMETERS';

    public static $invoke_service = '';
    public static $invoke_service_parameters = array();

    /**
     * get request data
     * 
     * @param string $key
     * @return mix
     */
    public static function get($key) 
    {
        switch ($key) {
            case self::APPID:
            case self::DEBUG:
            case self::SEQ:
            case self::SIGNATURE:
            case self::TIMESTAMP:
                $_key = 'HTTP_' . $key;
                return isset($_SERVER[$_key]) ? $_SERVER[$_key] : '';
            case self::INVOKE_SERVICE:
                return self::$invoke_service;
                break;
            case self::INVOKE_SERVICE_PARAMETERS:
                return self::$invoke_service_parameters;
                break;
            default:
                return '';
        }
    }

}
