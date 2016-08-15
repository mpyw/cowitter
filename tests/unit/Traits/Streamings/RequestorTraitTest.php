<?php

namespace mpyw\TestOfCowitter;
require __DIR__ . '/../../CurlStubs/autoload.php';

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
class RequestorTraitTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;
    private static $CredentialNormalizer;

    public function _before()
    {
        CurlFaker::clear();
        $this->c = new Client(['x', 'y', 'z', 'w']);
    }

    public function testStreaming()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        CurlFaker::set('https://userstream.twitter.com/1.1/user.json', [
            ['HTTP/1.1 200 OK'],
            $statuses
        ]);
        $this->c->streaming('user', function ($status) use ($statuses) {
            static $counter = -1;
            $i = ++$counter;
            $expected = json_decode(is_string($statuses[$i]) ? $statuses[$i] : implode($statuses[$i]));
            $actual = $status;
            $this->assertEquals($expected, $actual);
            $this->assertTrue($i < count($statuses));
            if ($i === count($statuses) - 1) {
                return false;
            }
        });
    }

    public function testStreamingWithoutStopping()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        CurlFaker::set('https://userstream.twitter.com/1.1/user.json', [
            ['HTTP/1.1 200 OK'],
            $statuses
        ]);
        $this->c->streaming('user', function ($status) use ($statuses) {
            static $counter = -1;
            $i = ++$counter;
            $expected = json_decode(is_string($statuses[$i]) ? $statuses[$i] : implode($statuses[$i]));
            $actual = $status;
            $this->assertEquals($expected, $actual);
            $this->assertTrue($i < count($statuses));
            if ($i === count($statuses) - 1) {
                $this->setExpectedException(\UnexpectedValueException::class, 'Streaming stopped unexpectedly');
            }
        });
    }

    public function testStreamingInvalidEndpoint()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        $this->setExpectedException(CURLException::class, 'Could not connect');
        $this->c->streaming('user', function ($status) use ($statuses) {});
    }

    public function testStreamingAsync()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        CurlFaker::set('https://userstream.twitter.com/1.1/user.json', [
            ['HTTP/1.1 200 OK'],
            $statuses
        ]);
        $this->assertNull(Co::wait($this->c->streamingAsync('user', function ($status)  {
            return false;
        })));
    }

    public function testStreamingWithoutStoppingAsync()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        CurlFaker::set('https://userstream.twitter.com/1.1/user.json', [
            ['HTTP/1.1 200 OK'],
            $statuses
        ]);
        $this->setExpectedException(\UnexpectedValueException::class, 'Streaming stopped unexpectedly');
        Co::wait($this->c->streamingAsync('user', function ($status) {}));
    }

    public function testStreamingInvalidEndpointAsync()
    {
        $statuses = [
            '{"id_str":"114514","text":"yj"}',
            ['{"id_str":"364', '364","text":"mur"}']
        ];
        $this->setExpectedException(CURLException::class, 'Could not connect');
        Co::wait($this->c->streamingAsync('user', function ($status) use ($statuses) {}));
    }
}
