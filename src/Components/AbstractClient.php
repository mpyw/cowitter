<?php

namespace mpyw\Cowitter\Components;

use mpyw\Cowitter\Response;

abstract class AbstractClient
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

    abstract protected function invokeFilter(Response $r);
}
