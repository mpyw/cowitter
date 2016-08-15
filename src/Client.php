<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\Components\Credential;
use mpyw\Cowitter\Components\CurlInitializer;

class Client implements \ArrayAccess, ClientInterface
{
    protected static $defaultOptions = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_ENCODING       => 'gzip',
    ];

    protected $credential;
    protected $options;
    protected $curl;

    use \mpyw\Cowitter\Traits\BaseClientTrait;
    use \mpyw\Cowitter\Traits\AuthenticatorTrait;
    use \mpyw\Cowitter\Traits\RequestorTrait;
    use \mpyw\Cowitter\Traits\UploaderTrait;
    use \mpyw\Cowitter\Traits\OAuth2ClientTrait;

    protected function getInternalCredential()
    {
        return $this->credential;
    }

    protected function getInternalOptions()
    {
        return $this->options;
    }

    protected function getInternalCurl()
    {
        return $this->curl;
    }

    protected function setInternalCredential(Credential $credential)
    {
        return $this->credential = $credential;
    }

    protected function setInternalOptions(array $options)
    {
        return $this->options = $options;
    }

    protected function setInternalCurl(CurlInitializer $curl)
    {
        return $this->curl = $curl;
    }
}
