<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\Response;
use mpyw\Cowitter\Components\AbstractClient;
use mpyw\Cowitter\SimpleClient;

class Client extends AbstractClient
{
    protected function invokeFilter(Response $r)
    {
        return $r;
    }

    public function simplify()
    {
        return new SimpleClient($this->credential, $this->options);
    }
}
