<?php

/**
 * API Mapper
 * @author yanghao
 * @date 17-04-01
 */
class Api_Mapper
{

    private $handler = NULL;

    public function __construct($source)
    {
        if (!preg_match('/^\/([a-zA-Z0-9]+)$/', $source, $match)) {
            throw new Exception('Invalid API Route', -1);
        }
        $handler = 'api_' . $match[1];
        if (class_exists($handler, TRUE) == FALSE) {
            throw new Exception('API Handler Not Exists', -1);
        }

        // reflect interface
        $interface = array();
        $refClass = new ReflectionClass($handler);
        foreach ($refClass->getInterfaceNames() as $if) {
            if (!preg_match('/(.+)If$/', $if, $match)) {
                continue;
            }

            if (interface_exists($if, TRUE) == FALSE) {
                throw new Exception('Api Handler Interface Invalid', -1);
            }
            $interface[] = $if;
        }
        if ($interface == NULL) {
            throw new Exception('Api Handler Invalid', -1);
        }

        $this->handler = new Api_Handler($handler, $interface);
    }

    public function getHandler()
    {
        return $this->handler;
    }

}
