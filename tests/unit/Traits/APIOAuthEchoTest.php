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
class APIOAuthEchoTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;

    public function _before()
    {
        usleep(5000);
        $this->c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function testGetOut()
    {
        $user = $this->c->getOut('https://localhost:8081/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostOut()
    {
        $user = $this->c->postOut('https://localhost:8081/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostMultipartOut()
    {
        $user = $this->c->postMultipartOut('https://localhost:8081/oauth_echo/verify_credentials.php');
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testGetOutAsync()
    {
        $user = Co::wait($this->c->getOutAsync('https://localhost:8081/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostOutAsync()
    {
        $user = Co::wait($this->c->postOutAsync('https://localhost:8081/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }

    public function testPostMultipartOutAsync()
    {
        $user = Co::wait($this->c->postMultipartOutAsync('https://localhost:8081/oauth_echo/verify_credentials.php'));
        $this->assertEquals((object)['id_str' => '114514'], $user);
    }
}
