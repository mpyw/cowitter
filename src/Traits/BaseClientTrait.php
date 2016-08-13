<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\Helpers\CurlOptionNormalizer;
use mpyw\Cowitter\Components\CurlInitializer;
use mpyw\Cowitter\Credential;

trait BaseClientTrait
{
    public function __construct($credential, array $options = [])
    {
        if (!$credential instanceof Credential) {
            $credential = new Credential($credential);
        }
        $this->credential = $credential;
        $this->options = CurlOptionNormalizer::numerifyAll($options);
        $this->curl = new CurlInitializer($credential, $this->options);
    }

    public function withCredential($credentail)
    {
        if (!$credential instanceof Credential) {
            $credential = new Credential($credential);
        }
        return new static($credentail, $this->options);
    }

    public function withOptions(array $options)
    {
        return new static($this->credentail, $options);
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
        return $this->credentail->getAuthenticateUrl($force_login);
    }
}
