<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\Helpers\CurlOptionNormalizer;
use mpyw\Cowitter\Components\CurlInitializer;
use mpyw\Cowitter\Components\Credential;

trait BaseClientTrait
{
    public function __construct(array $credentials, array $options = [])
    {
        $this->credential = new Credential($credentials);
        $this->options = CurlOptionNormalizer::numerifyAll($options);
        $this->curl = new CurlInitializer($this->credential, $this->options);
    }

    public function offsetGet($offset)
    {
        return $this->credential->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->credential->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->credential->offsetUnset($offset);
    }

    public function offsetExists($offset)
    {
        return $this->credential->offsetExists($offset);
    }

    public function __get($key)
    {
        return $this->credential->$key;
    }

    public function __isset($key)
    {
        return isset($this->credential->$key);
    }

    public function withCredentials(array $credentials)
    {
        return new static($credentials, $this->options);
    }

    public function withOptions(array $options)
    {
        return new static($this->credential, $options);
    }

    public function getCredentials($assoc = false)
    {
        return $this->credential->toArray($assoc);
    }

    public function getOptions($stringify = false)
    {
        return $stringify ? CurlOptionNormalizer::stringifyAll($this->options) : $this->options;
    }

    public function getAuthorizeUrl($force_login = false)
    {
        return $this->credential->getAuthorizeUrl($force_login);
    }

    public function getAuthenticateUrl($force_login = false)
    {
        return $this->credential->getAuthenticateUrl($force_login);
    }
}
