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
class APIOAuthTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;

    public function _before()
    {
        usleep(5000);
    }

    public function testOauthForRequestToken()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = $c->oauthForRequestToken('oob');
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testOauthForAccessToken()
    {
        $c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = $c->oauthForAccessToken('1919810');
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testXauthForAccessToken()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = $c->xauthForAccessToken('username', 'password');
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testOauthForRequestTokenAsync()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = Co::wait($c->oauthForRequestTokenAsync('oob'));
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testOauthForAccessTokenAsync()
    {
        $c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = Co::wait($c->oauthForAccessTokenAsync('1919810'));
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testXauthForAccessTokenAsync()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $client = Co::wait($c->xauthForAccessTokenAsync('username', 'password'));
        $this->assertEquals('t', $client['token']);
        $this->assertEquals('ts', rtrim($client['token_secret']));
    }

    public function testParseAuthenticityTokenSuccess()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $ch = (function () {
            return $this->getInternalCurl();
        })->call($c)->browsing();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://localhost:8081/oauth/authorize.php',
        ]);;
        $response = new Response(curl_exec($ch), $ch);
        $this->assertEquals('114514364364', RegexParser::parseAuthenticityToken($response, $ch));
    }

    public function testParseAuthenticityTokenFailure()
    {
        $this->setExpectedException(HttpException::class, 'Failed to get authenticity_token.');
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $ch = (function () {
            return $this->getInternalCurl();
        })->call($c)->browsing();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://localhost:8081/path/to/invalid/file',
            CURLOPT_POST => true,
        ]);;
        $response = new Response(curl_exec($ch), $ch);
        RegexParser::parseAuthenticityToken($response, $ch);
    }

    public function testParseVerifierSuccess()
    {
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $ch = (function () {
            return $this->getInternalCurl();
        })->call($c)->browsing();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://localhost:8081/oauth/authorize.php',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => 'username',
                'session[password]' => 'password',
                'authenticity_token' => '114514364364',
            ], '', '&'),
        ]);;
        $response = new Response(curl_exec($ch), $ch);
        $this->assertEquals('1919810', RegexParser::parseVerifier($response, $ch));
    }

    public function testParseVerifierFailure()
    {
        $this->setExpectedException(HttpException::class, 'Wrong username or password. Otherwise, you may have to verify your email address.');
        $c = new Client(['ck', 'cs'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $ch = (function () {
            return $this->getInternalCurl();
        })->call($c)->browsing();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://localhost:8081/oauth/authorize.php',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => 'username',
                'session[password]' => 'password',
            ], '', '&'),
        ]);;
        $response = new Response(curl_exec($ch), $ch);
        RegexParser::parseVerifier($response, $ch);
    }
}
