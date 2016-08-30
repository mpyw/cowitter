<?php

namespace mpyw\TestOfCowitter;

require_once __DIR__ . '/../../assets/dummy_curl.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\ClientInterface;
use mpyw\Cowitter\HttpException;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\TokenParser;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class APIOAuth2Test extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
    }

    public function testOauthForBearerToken()
    {
        $c = new Client(['ck', 'cs']);
        $client = $c->oauthForBearerToken();
        $this->assertEquals('t', $client['token']);
    }

    public function testInvalidateBearerToken()
    {
        $c = new Client(['ck', 'cs', 't', 'ts']);
        $this->assertNull($c->invalidateBearerToken());
    }

    public function testGet2()
    {
        $c = new Client(['ck', 'cs', 't']);
        $response = $c->get2('sample_endpoint_for_oauth2');
        $this->assertEquals('114514', $response->id_str);
    }

    public function testOauthForBearerTokenAsync()
    {
        $c = new Client(['ck', 'cs']);
        $client = Co::wait($c->oauthForBearerTokenAsync());
        $this->assertEquals('t', $client['token']);
    }

    public function testInvalidateBearerTokenAsync()
    {
        $c = new Client(['ck', 'cs', 't', 'ts']);
        $this->assertNull(Co::wait($c->invalidateBearerTokenAsync()));
    }

    public function testGet2Async()
    {
        $c = new Client(['ck', 'cs', 't']);
        $response = Co::wait($c->get2Async('sample_endpoint_for_oauth2'));
        $this->assertEquals('114514', $response->id_str);
    }
}
