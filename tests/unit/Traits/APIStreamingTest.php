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
class APIStreamingTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
        $this->c = new Client(['ck', 'cs', 't', 'ts']);
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

    public function testStreamingAsync()
    {
        $i = 0;
        Co::wait($this->c->streamingAsync('statuses/filter', function ($status) use (&$i) {
            ++$i;
            $this->assertEquals((object)['text' => 'hello'], $status);
            if ($i === 3) {
                return false;
            }
        }));
        $this->assertEquals(3, $i);
    }

    public function testStreamingAsyncYield()
    {
        $i = 0;
        Co::wait($this->c->streamingAsync('statuses/filter', function ($status) use (&$i) {
            ++$i;
            yield;
            $this->assertEquals((object)['text' => 'hello'], $status);
            if ($i === 3) {
                return false;
            }
        }));
        $this->assertEquals(3, $i);
    }

    public function testStreamingStopError()
    {
        $this->setExpectedException(\UnexpectedValueException::class, 'Streaming stopped unexpectedly.');
        $this->c->streaming('statuses/filter', function () {});
    }

    public function testStreamingStopErrorAsync()
    {
        $this->setExpectedException(\UnexpectedValueException::class, 'Streaming stopped unexpectedly.');
        Co::wait($this->c->streamingAsync('statuses/filter', function () {}));
    }

    public function testStreamingHeader()
    {
        $i = 0;
        $this->c->streaming('statuses/filter', function () { return false; }, [],
        function ($response) use (&$i) {
            ++$i;
            $this->assertInstanceOf(ResponseInterface::class, $response);
        });
        $this->assertEquals(1, $i);
    }

    public function testStreamingHeaderAsync()
    {
        $i = 0;
        Co::wait($this->c->streamingAsync('statuses/filter', function () { return false; }, [],
        function ($response) use (&$i) {
            ++$i;
            $this->assertInstanceOf(ResponseInterface::class, $response);
        }));
        $this->assertEquals(1, $i);
    }

    public function testStreamingHeaderAsyncYield()
    {
        $i = 0;
        Co::wait($this->c->streamingAsync('statuses/filter', function () { return false; }, [],
        function ($response) use (&$i) {
            yield;
            ++$i;
            $this->assertInstanceOf(ResponseInterface::class, $response);
        }));
        $this->assertEquals(1, $i);
    }
}
