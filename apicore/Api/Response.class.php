<?php
/**
 * API 应答
 * @author yanghao
 * @date 17-03-31
 */
class Api_Response 
{

    const CODE = 'HLW_CODE';
    const SEQ = 'HLW_SEQ';

    private $headers = array();
    private $message = '';

    /**
     * create response
     * @param int $code
     * @param string $message
     * @param string $seq
     */
    public function __construct($code, $message, $seq)
    {
        $this->headers = array(
            self::CODE => (int) $code,
            self::SEQ => (string) $seq
        );
        $this->message = $message;
    }

    /**
     * send http response headers
     */
    public function send()
   {
        foreach ($this->headers as $k => $v) {
            $header = sprintf('%s: %s', $k, $v);
            // keep ok for client
            header($header, TRUE, 200);
        }

        if ($this->headers[self::CODE] != 0) {
            echo $this->message;
        }
    }

}
