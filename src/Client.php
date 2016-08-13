<?php

namespace mpyw\Cowitter;

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
}
