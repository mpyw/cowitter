<?php

use mpyw\Cowitter\Client;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
    }

    public function testInsufficientConstructorArgs()
    {
        $this->setExpectedException(\DomainException::class, '"consumer_key" and "consumer_secret" are at least required.');
        new Client(['ck']);
    }
}
