<?php

namespace mpyw\Cowitter;

interface HttpExceptionInterface
{
    public function getStatusCode();
    public function getReasonPhrase();
    public function getHandle();
    public function getResponse();
}
