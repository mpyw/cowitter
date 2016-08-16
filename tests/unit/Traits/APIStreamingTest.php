<?php

namespace mpyw\TestOfCowitter;

require_once __DIR__ . '/../../assets/dummy_curl.php';

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
class APIStreamingTest extends \Codeception\TestCase\Test {

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

    public function testStreaming()
    {
        $i = 0;
        $this->c->streaming('statuses/filter', function ($status) use (&$i) {
            ++$i;
            $this->assertEquals((object)['text' => 'hello'], $status);
            if ($i === 3) {
                return false;
            }
        });
        $this->assertEquals(3, $i);
    }
}
