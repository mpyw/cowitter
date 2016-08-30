<?php

namespace mpyw\TestOfCowitter;

require_once __DIR__ . '/../../assets/dummy_curl.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\ClientInterface;
use mpyw\Cowitter\HttpException;
use mpyw\Cowitter\ResponseInterface;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class APIOAuthEchoTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
        $this->c = new Client(['ck', 'cs', 't', 'ts']);
    }

    public function testGetOut()
    {
        $user = $this->c->getOut('http://localhost:8080/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostOut()
    {
        $user = $this->c->postOut('http://localhost:8080/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostMultipartOut()
    {
        $user = $this->c->postMultipartOut('http://localhost:8080/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testGetOutAsync()
    {
        $user = Co::wait($this->c->getOutAsync('http://localhost:8080/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostOutAsync()
    {
        $user = Co::wait($this->c->postOutAsync('http://localhost:8080/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostMultipartOutAsync()
    {
        $user = Co::wait($this->c->postMultipartOutAsync('http://localhost:8080/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }
}
