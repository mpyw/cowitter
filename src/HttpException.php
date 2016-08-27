<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\ResponseInterface;

class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    protected $ch;
    protected $response;

    public function __construct($message, $code, ResponseInterface $response)
    {
        parent::__construct($message, $code);
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

    public function getResponse()
    {
        return $this->response;
    }
}
