<?php
/**
 * thrift 传输HTTP
 * @author yanghao
 * @date 17-03-30
 */

use Thrift\Transport\THttpClient;
use Thrift\Exception\TApplicationException;

class Thrift_Transport_HttpClient extends THttpClient
{

    // max read length 128MB
    const MAX_READ_LENGTH = 134217728;

    private $isFirstRead = TRUE;

    public function read($len) 
    {
        // check header code
        if ($this->isFirstRead) {
            $this->isFirstRead = FALSE;
            $headers = $this->parseHeader();
            if ($headers[Api_Response::CODE] !== '0') {
                throw new TApplicationException($this->parseBody(), $headers[Api_Response::CODE]);
            }
        }

        // check each read length
        if ($len > self::MAX_READ_LENGTH) {
            throw new TApplicationException(
            'read message too long. ' . $len, -1);
        }

        return parent::read($len);
    }

    private function parseHeader()
    {
        $raw = stream_get_meta_data($this->handle_);
        if (!isset($raw['wrapper_data'])) {
            throw new TApplicationException('Protocol Header Not Exists', -1);
        }

        $headers = array();
        foreach ($raw['wrapper_data'] as $line) {
            if (!strstr($line, ':')) {
                continue;
            }

            list($key, $val) = explode(':', $line, 2);
            switch ($key) {
                case Api_Response::CODE:
                case Api_Response::SEQ:
                    break;
                default: // skip other
                    continue;
            }
            $headers[trim($key)] = trim($val);
        }

        if (!isset($headers[Api_Response::CODE])) {
            throw new TApplicationException('Protocol Header Invalid', -1);
        }

        return $headers;
    }

    private function parseBody()
    {
        return stream_get_contents($this->handle_);
    }

}
