<?php

use Thrift\Type\TMessageType;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TPhpStream;
use Thrift\Transport\TBufferedTransport;

class Thrift_Context implements Context_Interface
{

    private $input;
    private $output;
    private $header;
    private $is_output = FALSE;

    public function __construct() 
    {
        $stream = new TPhpStream(TPhpStream::MODE_R | TPhpStream::MODE_W);
        $transport = new TBufferedTransport($stream);
        $protocol = new TBinaryProtocol($transport, TRUE, TRUE);
        $this->input = $this->output = $protocol;
    }

    /**
     * @return TProtocol
     */
    public function getInput() 
    {
        if (!$this->input->getTransport()->isOpen()) {
            $this->input->getTransport()->open();
        }
        return $this->input;
    }

    /**
     * @return TProtocol
     */
    public function getOutput() 
    {
        if (!$this->output->getTransport()->isOpen()) {
            $this->output->getTransport()->open();
        }
        return $this->output;
    }

    /**
     * @return Thrift_Header
     */
    public function getHeader() 
    {
        if (!$this->header) {
            $this->header = new Thrift_Header();
            $this->getInput()->readMessageBegin(
                    $this->header->name, $this->header->type, $this->header->seqid);
        }

        return $this->header;
    }

    /**
     * @param string $message
     * @param int $code
     */
    public function writeException($message, $code) 
    {
        if ($code == 0) {
            $code = -1;
        }

        $rsp = new Api_Response($code, $message, Api_Request::get(Api_Request::SEQ));
        $rsp->send();

        $this->is_output = TRUE;
    }

    /**
     * @param mix $result
     */
    public function writeReply($result) 
    {
        $rsp = new Api_Response(0, 'SUCCESS', Api_Request::get(Api_Request::SEQ));
        $rsp->send();

        $output = $this->getOutput();

        $accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');

        $header = $this->getHeader();
        $name = $header->name;
        $seqid = $header->seqid;

        if ($accel) {
            thrift_protocol_write_binary(
                    $output, $name, TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
        } else {
            $output->writeMessageBegin($name, TMessageType::REPLY, $seqid);
            $result->write($output);
            $output->writeMessageEnd();
            $output->getTransport()->flush();
        }

        $this->is_output = TRUE;
    }

    /**
     * @return boolean
     */
    public function isOutput() 
    {
        return $this->is_output;
    }

}
