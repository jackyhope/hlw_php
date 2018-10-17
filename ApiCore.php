<?php

require __DIR__ . '/apicore/Class/Loader.class.php';

final class ApiCore 
{

    public static $loader;

    /**
     * init env
     * 
     * @param array $app_path 业务路径
     * @param array $sdk_lib SDK路径
     */
    public static function init($app_path, $sdk_lib = array('gdlApiSdk'))
    {
        self::$loader = new Class_Loader();
        self::$loader->appendPath('app', $app_path);
        self::$loader->appendSdk($sdk_lib);
        self::$loader->registerLoader();
    }

    public static function run() 
    {
        $ctx = new Thrift_Context();

        $error = new Error_Handler($ctx);
        $error->registerHandler();

        try {
            $source = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
            $header = $ctx->getHeader();
            $mapper = new Api_Mapper($source);
            $handler = $mapper->getHandler();

            $route = array(
                $handler->getHandlerName(),
                $header->name
            );

            ob_start();
            $proc = new Thrift_Processor($handler);
            $result = $proc->process($header->name, $ctx->getInput(), $ctx->getOutput());
            ob_end_clean();

            $ctx->writeReply($result);
        } catch (Exception $e) {
            $ctx->writeException($e->getMessage(), $e->getCode());
        }
    }
}
