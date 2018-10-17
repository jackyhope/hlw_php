<?php

use Thrift\Protocol\TProtocol;
use Thrift\Exception\TApplicationException;

class Thrift_Processor
{
    private $handler;
    
    public function __construct(Api_Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * execute server process return mixed result
     * 
     * @param string $method
     * @param TProtocol $input
     * @param TProtocol $output
     * @throws Exception
     * @return mixed
     */
    public function process($method, TProtocol $input, TProtocol $output)
    {
        $instance = $this->handler->getHandlerInstance();
        $handler = $this->handler->getHandlerName();
        if (!method_exists($instance, $method)) {
            throw new Exception(
            'Function ' . $method . ' not implemented.', TApplicationException::UNKNOWN_METHOD);
        }

        $args = $this->handler->getArgsInstance($method);
        $args->read($input);
        $input->readMessageEnd();
        $params = array();
        if ($args::$_TSPEC) {
            foreach ($args::$_TSPEC as $spec) {
                $params[] = $args->$spec['var'];
            }
        }

        Api_Request::$invoke_service = "{$handler}::{$method}";
        Api_Request::$invoke_service_parameters = $params;

        $result = $this->handler->getResultInstance($method);
        $result->success = call_user_func_array(
                array($instance, $method), $params);

        return $result;
    }

}
