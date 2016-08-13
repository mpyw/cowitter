<?php

namespace mpyw\Cowitter;

interface ResponseInterface
{
    public function getVersion();
    public function getStatusCode();
    public function getReasonPhrase();
    public function getHeaders();
    public function getHeader($name);
    public function getHeaderLine($name, $delimiter = ', ');
    public function getRawContent();
    public function getContent();
    public function hasContent();
}
