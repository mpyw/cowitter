<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\Response;
use mpyw\Cowitter\Components\AbstractClient;
use mpyw\Cowitter\Client;

class SimpleClient extends AbstractClient
{
    protected function invokeFilter(Response $r)
    {
        return $r->hasContent() ? $r->getContent() : null;
    }

    public function formalify()
    {
        return new Client($this->credential, $this->options);
    }
}
