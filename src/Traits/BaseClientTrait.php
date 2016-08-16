<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\Helpers\CurlOptionNormalizer;
use mpyw\Cowitter\Components\CurlInitializer;
use mpyw\Cowitter\Components\Credential;

trait BaseClientTrait
{
    abstract protected function getInternalCredential();
    abstract protected function getInternalOptions();
    abstract protected function getInternalCurl();
    abstract protected function setInternalCredential(Credential $credential);
    abstract protected function setInternalOptions(array $options);
    abstract protected function setInternalCurl(CurlInitializer $curl);

    public function __construct(array $credentials, array $options = [])
    {
        $this->setInternalCredential(new Credential($credentials));
        $this->setInternalOptions(CurlOptionNormalizer::numerifyAll($options));
        $this->setInternalCurl(new CurlInitializer($this->getInternalCredential(), $this->getInternalOptions()));
    }

    public function offsetGet($offset)
    {
        return $this->getInternalCredential()->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->getInternalCredential()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->getInternalCredential()->offsetUnset($offset);
    }

    public function offsetExists($offset)
    {
        return $this->getInternalCredential()->offsetExists($offset);
    }

    public function __get($key)
    {
        return $this->getInternalCredential()->$key;
    }

    public function __isset($key)
    {
        return isset($this->getInternalCredential()->$key);
    }

    public function withCredentials(array $credentials)
    {
        return new static(
            array_replace($this->getInternalCredential()->toArray(), $credentials),
            $this->getInternalOptions()
        );
    }

    public function withOptions(array $options)
    {
        return new static(
            $this->getInternalCredential()->toArray(),
            array_replace($this->getInternalOptions(), $options)
        );
    }

    public function getCredentials($assoc = false)
    {
        return $this->getInternalCredential()->toArray($assoc);
    }

    public function getOptions($stringify = false)
    {
        return $stringify ? CurlOptionNormalizer::stringifyAll($this->getInternalOptions()) : $this->getInternalOptions();
    }

    public function getAuthorizeUrl($force_login = false)
    {
        return $this->getInternalCredential()->getAuthorizeUrl($force_login);
    }

    public function getAuthenticateUrl($force_login = false)
    {
        return $this->getInternalCredential()->getAuthenticateUrl($force_login);
    }
}
