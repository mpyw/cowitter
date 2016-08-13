<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\ResponseInterface;

interface HttpExceptionInterface
{
    /**
     * Return HTTP status code.
     * @return int
     */
    public function getStatusCode();

    /**
     * Return HTTP reason pharse.
     * @return string
     */
    public function getReasonPhrase();

    /**
     * Return cURL handle used for sending request.
     * @return resource
     */
    public function getHandle();

    /**
     * Return Response object with no decoded content.
     * @return ResponseInterface
     */
    public function getResponse();
}
