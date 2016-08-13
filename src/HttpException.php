<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\Response;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    protected $ch;
    protected $response;

    public function __construct($message, $code, $ch, Response $response)
    {
        parent::__construct($message, $code);
        $this->ch = $ch;
        $this->response = $response;
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function getHandle()
    {
        return $this->ch;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
