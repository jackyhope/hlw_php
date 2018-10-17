<?php

/**
 * 错误Handle
 * @author yanghao  <yh38615890@sina.cn>
 * @date 17-03-30
 * @copyright (c) gandianli
 */
class Error_Handler 
{

    private $ignore_error = array(
        E_STRICT,
        E_NOTICE,
        E_WARNING,
        E_DEPRECATED,
        E_USER_NOTICE,
        E_USER_WARNING,
        E_USER_DEPRECATED
    );
    private $ctx;

    public function __construct(Context_Interface $ctx)
    {
        $this->ctx = $ctx;
    }

    public function registerHandler() 
    {
        register_shutdown_function(array($this, 'shutdownHandler'));
    }

    public function shutdownHandler() 
    {
        if ($this->ctx->isOutput()) {
            return;
        }
        $error = error_get_last();
        if ($error && !in_array($error['type'], $this->ignore_error)) {
            $message = '';
            foreach ($error as $key => $val) {
                $message .= "{$key}: {$val}. ";
            }
            $message .= "output: " . ob_get_clean();
            $code = 44444;
            $this->ctx->writeException($message, $code);
        }
    }

}
