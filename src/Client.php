<?php

namespace mpyw\Cowitter;

class Client
{
    protected static $defaultOptions = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_ENCODING       => 'gzip',
    ];

    protected $credential;
    protected $options;
    protected $curl;

    use Traits\BaseClientTrait;
    use Traits\AuthenticatorTrait;
    use Traits\RequestorTrait;
    use Traits\UploaderTrait;
}
