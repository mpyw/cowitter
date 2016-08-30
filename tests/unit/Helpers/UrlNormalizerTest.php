<?php

use mpyw\Cowitter\Helpers\UrlNormalizer;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class UrlNormalizerTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function testSpecialUrls()
    {
        $urls = [
            'i/activity/about_me' =>
                'https://api.twitter.com/i/activity/about_me.json',
            'i/activity/by_friends' =>
                'https://api.twitter.com/i/activity/by_friends.json',
            'site' =>
                'https://sitestream.twitter.com/1.1/site.json',
            'statuses/filter' =>
                'https://stream.twitter.com/1.1/statuses/filter.json',
            'statuses/firehose' =>
                'https://stream.twitter.com/1.1/statuses/firehose.json',
            'statuses/sample' =>
                'https://stream.twitter.com/1.1/statuses/sample.json',
            '1.1/statuses/sample' => // Currently unsupported
                'https://api.twitter.com/1.1/statuses/sample.json',
            'media/upload' =>
                'https://upload.twitter.com/1.1/media/upload.json',
            'user' =>
                'https://userstream.twitter.com/1.1/user.json',
        ];
        foreach ($urls as $endpoint => $expected) {
            list($actual, $params) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
            $this->assertEquals($expected, $actual);
            $this->assertEmpty($params);
        }
    }

    public function testParseFailure()
    {
        $this->setExpectedException(\DomainException::class, 'Invalid URL.');
        UrlNormalizer::twitterSplitUrlAndParameters('///1.1/statuses/update.json');
    }

    public function testPartialPath()
    {
        $patterns = [
            '' => 'https://api.twitter.com/',
            'xyz' => 'https://api.twitter.com/1.1/xyz.json',
            '1.1/xyz' => 'https://api.twitter.com/1.1/xyz.json',
            '1/xyz' => 'https://api.twitter.com/1/xyz.json',
            'i/xyz' => 'https://api.twitter.com/i/xyz.json',
            'ii/xyz' => 'https://api.twitter.com/1.1/ii/xyz.json',
            'xyz.json' => 'https://api.twitter.com/1.1/xyz.json',
            'xyz.abc' => 'https://api.twitter.com/1.1/xyz.abc',
            'https://api.twitter.com/xyz' => 'https://api.twitter.com/xyz',
            'http://api.twitter.com/xyz' => 'http://api.twitter.com/xyz',
            'http://api.twitter.com' => 'http://api.twitter.com/',
            '//api.twitter.com/xyz' => 'https://api.twitter.com/xyz',
            '//user:pass@api.twitter.com:893/xyz?#www' => 'https://api.twitter.com:893/xyz',
        ];
        foreach ($patterns as $endpoint => $expected) {
            list($actual, $params) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
            $this->assertEquals($expected, $actual);
            $this->assertEmpty($params);
        }
    }

    public function testOutParseFailure()
    {
        $this->setExpectedException(\DomainException::class, 'Invalid URL.');
        UrlNormalizer::outSplitUrlAndParameters('///aaa/bbb.json');
    }

    public function testOutMissingHost()
    {
        $this->setExpectedException(\DomainException::class, 'Invalid URL: Missing host.');
        UrlNormalizer::outSplitUrlAndParameters('aaa/bbb.json');
    }

    public function testOut()
    {
        $patterns = [
            'https://api.twitter.com/xyz' => 'https://api.twitter.com/xyz',
            'http://api.twitter.com/xyz' => 'http://api.twitter.com/xyz',
            'http://api.twitter.com' => 'http://api.twitter.com/',
            '//api.twitter.com/xyz' => 'https://api.twitter.com/xyz',
            '//user:pass@api.twitter.com:893/xyz?#www' => 'https://user:pass@api.twitter.com:893/xyz',
        ];
        foreach ($patterns as $endpoint => $expected) {
            list($actual, $params) = UrlNormalizer::outSplitUrlAndParameters($endpoint);
            $this->assertEquals($expected, $actual);
            $this->assertEmpty($params);
        }
    }

    public function testParamExtraction()
    {
        $patterns = [
            'https://api.twitter.com/?a=b&c=d' => ['a' => 'b', 'c' => 'd'],
            '//example.com/?a=b&c=d' => ['a' => 'b', 'c' => 'd'],
        ];
        foreach ($patterns as $endpoint => $expected) {
            list($_, $actual) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
            $this->assertEquals($expected, $actual);
        }
        foreach ($patterns as $endpoint => $expected) {
            list($_, $actual) = UrlNormalizer::outSplitUrlAndParameters($endpoint);
            $this->assertEquals($expected, $actual);
        }
    }
}
