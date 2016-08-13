<?php

namespace mpyw\Cowitter;

interface CredentialInterface
{
    public function __construct(array $params = []);
    public function with(array $params = []);
    public function offsetGet($offset);
    public function offsetExists($offset);
    public function __get($key);
    public function __isset($key);
    public function getAuthorizeUrl($force_login = false);
    public function getAuthenticateUrl($force_login = false);
}
