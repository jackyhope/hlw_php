<?php

/**
 * API SDK处理
 * @author yanghao
 * @date 17-4-1
 */
class Api_Handler 
{

    private $handler = '';
    private $intreface = array();

    public function __construct($handler, array $interface)
    {
        $this->handler = $handler;
        $this->intreface = $interface;
    }

    public function getHandlerName() 
    {
        return $this->handler;
    }

    public function getHandlerInstance()
    {
        return new $this->handler();
    }

    public function getArgsInstance($method)
    {
        foreach ($this->intreface as $interface) {
            $class = preg_replace('/If$/', "_{$method}_args", $interface);
            if (class_exists($class, TRUE)) {
                return new $class();
            }
        }

        throw new Exception('API Args Load Failed', -1);
    }

    public function getResultInstance($method)
    {
        foreach ($this->intreface as $interface) {
            $class = preg_replace('/If$/', "_{$method}_result", $interface);
            if (class_exists($class, TRUE)) {
                return new $class();
            }
        }

        throw new Exception('API Load Failed', -1);
    }

}
