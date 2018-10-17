<?php

interface Context_Interface 
{

    public function writeException($message, $code);

    public function writeReply($result);
}
