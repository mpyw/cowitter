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
class UploadTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
        $this->c = new Client(['ck', 'cs', 't', 'ts']);
    }

    public function testInvalidChunkType()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Chunk size must be integer.');
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, null, []));
    }

    public function testInvalidChunkLength()
    {
        $this->setExpectedException(\LengthException::class, 'Chunk size must be no less than 10000 bytes.');
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, null, 893));
    }

    public function testUploadAsync()
    {
        $info = Co::wait($this->c->uploadAsync(new \SplFileObject(__FILE__, 'rb')));
        $this->assertTrue(!isset($info->processing_info));
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadImageAsync()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, function ($percent) {
            static $i = -1;
            if (++$i === 0) {
                $this->assertEquals(0, $percent);
            } else {
                $this->assertEquals(53, $percent);
            }
        }));
        $this->assertEquals('done', $info->processing_info->state);
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadImageAsyncYieldAbort01()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, function ($percent) {
            yield;
            return false;
        }));
        $this->assertEquals('pending', $info->processing_info->state);
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadImageAsyncYieldAbort02()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, function ($percent) {
            yield Co::DELAY => 0.1;
            return false;
        }));
        $this->assertEquals('in_progress', $info->processing_info->state);
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadImageAsyncAbort()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), null, function ($percent) {
            return false;
        }));
        $this->assertEquals('pending', $info->processing_info->state);
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadAnimeGifAsync()
    {
        $this->setExpectedException(HttpException::class, 'tweet_gif always fails in this test.');
        $info = Co::wait($this->c->uploadAnimeGifAsync(new \SplFileObject(__FILE__, 'rb')));
    }

    public function testUploadVideoAsync()
    {
        $this->setExpectedException(HttpException::class, 'tweet_video always fails in this test.');
        $info = Co::wait($this->c->uploadVideoAsync(new \SplFileObject(__FILE__, 'rb')));
    }

    public function testUploadProgressAsync()
    {
        $invoked = false;
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), function ($percent) use (&$invoked) {
            $invoked = true;
        }));
        $this->assertTrue($invoked);
        $this->assertEquals('done', $info->processing_info->state);
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadProgressAsyncAbort()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), function ($percent) {
            return false;
        }));
        $this->assertTrue(!isset($info->processing_info));
        $this->assertNotEmpty($info->media_id_string);
    }

    public function testUploadProgressAsyncYieldAbort()
    {
        $info = Co::wait($this->c->uploadImageAsync(new \SplFileObject(__FILE__, 'rb'), function ($percent) {
            yield;
            return false;
        }));
        $this->assertTrue(!isset($info->processing_info));
        $this->assertNotEmpty($info->media_id_string);
    }
}
