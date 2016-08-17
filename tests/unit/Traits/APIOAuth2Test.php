<?php

namespace mpyw\TestOfCowitter;

require_once __DIR__ . '/../../assets/dummy_curl.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\ClientInterface;
use mpyw\Cowitter\HttpException;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\RegexParser;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class APIOAuth2Test extends \Codeception\TestCase\Test {

    use \Codeception\Specify;

    public function _before()
    {
        usleep(50000);
    }

    public function testOauthForBearerToken()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = $c->oauthForBearerToken();
        $this->assertEquals('t', $client['token']);
    }

    public function testInvalidateBearerToken()
    {
        $c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $this->assertNull($c->invalidateBearerToken());
    }

    public function testGet2()
    {
        $c = new Client(['ck', 'cs', 't'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $response = $c->get2('sample_endpoint_for_oauth2');
        $this->assertEquals('114514', $response->id_str);
    }

    public function testOauthForBearerTokenAsync()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = Co::wait($c->oauthForBearerTokenAsync());
        $this->assertEquals('t', $client['token']);
    }

    public function testInvalidateBearerTokenAsync()
    {
        $c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $this->assertNull(Co::wait($c->invalidateBearerTokenAsync()));
    }

    public function testGet2Async()
    {
        $c = new Client(['ck', 'cs', 't'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $response = Co::wait($c->get2Async('sample_endpoint_for_oauth2'));
        $this->assertEquals('114514', $response->id_str);
    }
}
