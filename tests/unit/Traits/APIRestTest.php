<?php

namespace mpyw\TestOfCowitter;

require __DIR__ . '/../../assets/dummy_curl.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
use mpyw\Cowitter\ResponseInterface;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class APIRestTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;
    private static $CredentialNormalizer;

    private static function t($v)
    {
        return json_decode(json_encode($v));
    }

    public function _before()
    {
        $this->c = new Client(['ck', 'cs', 't', 'ts'], [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function testHomeTimeline()
    {
        $expected = self::t([['text' => 'a'], ['text' => 'b']]);
        $actual = $this->c->get('statuses/home_timeline');
        $this->assertEquals($expected, $actual);
    }

    public function testHomeTimelineAsync()
    {
        $expected = self::t([['text' => 'a'], ['text' => 'b']]);
        $actual = Co::wait($this->c->getAsync('statuses/home_timeline'));
        $this->assertEquals($expected, $actual);
    }
}
