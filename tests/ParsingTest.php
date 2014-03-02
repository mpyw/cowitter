<?php

require '../settings_for_tests_and_examples.php';

class ParsingTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Endpoint parsing: path
     */
    public function testValidPaths() {
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('statuses/update')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('STATUSES/UPDATE')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('statuses update')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('statuses/update.json')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('/statuses//update///')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('1.1/statuses/update')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('/1.1/statuses/update')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('https://api.twitter.com/1.1/statuses/update.json')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('https://api.twitter.com/1.1/statuses/update')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/update.json',
            TwistRequest::post('https://api.twitter.com/statuses/update')->endpoint
        );
        $this->assertEquals(
            '/oauth/authorize',
            TwistRequest::post('oauth/authorize')->endpoint
        );
        $this->assertEquals(
            '/oauth/authenticate',
            TwistRequest::post('oauth/authenticate')->endpoint
        );
        $this->assertEquals(
            '/1.1/user.json',
            TwistRequest::post('user')->endpoint
        );
        $this->assertEquals(
            '/1.1/statuses/filter.json',
            TwistRequest::post('statuses/filter')->endpoint
        );
    }
    
    /**
     * Endpoint parsing: path (unexpected)
     */
    public function testInvalidPaths() {
        // parse_url() fails on consecutive 3 slashes.
        try {
            $request = TwistRequest::post('///statuses/update.json');
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Invalid endpoint: ///statuses/update.json', $e->getMessage());
        }
        // parse_url() ignores sequences with consecutive 2 slashes except last one.
        $this->assertEquals(
            '/1.1/update.json',
            TwistRequest::post('//statuses//update.json')->endpoint
        );
        // Do not attach unexpected extension.
        $this->assertEquals(
            '/1.1/statuses/update.xml.json',
            TwistRequest::post('statuses/update.xml')->endpoint
        );
    }
    
    /**
     * Endpoint parsing: params
     */
    public function testValidParams() {
        $this->assertEquals(
            array(
                'status' => '@re4k Test',
                'in_reply_to_status_id' => 'xxx',
            ),
            TwistRequest::post('statuses/update', array(
                'status' => '@re4k Test',
                'in_reply_to_status_id' => 'xxx',
            ))->params
        );
        $this->assertEquals(
            array(
                'status' => '@re4k Test',
                'in_reply_to_status_id' => 'xxx',
            ),
            TwistRequest::post('statuses/update',
                'status=@re4k Test&in_reply_to_status_id=xxx'
            )->params
        );
        $this->assertEquals(
            array(
                'status' => '@re4k Test',
                'in_reply_to_status_id' => 'xxx',
            ),
            TwistRequest::post(
                'statuses/update?status=@re4k Test&in_reply_to_status_id=xxx'
            )->extraParams
        );
    }
    
    /**
     * Endpoint parsing: params (unexpected)
     */
    public function testInvalidParams() {
        // urldecode() is not available.
        $this->assertEquals(
            array(
                'status' => '@re4k+Test',
                'in_reply_to_status_id' => 'xxx',
            ),
            TwistRequest::post('statuses/update',
                'status=@re4k+Test&in_reply_to_status_id=xxx'
            )->params
        );
        // urldecode() is not available.
        $this->assertEquals(
            array(
                'status' => '@re4k%20Test',
                'in_reply_to_status_id' => 'xxx',
            ),
            TwistRequest::post('statuses/update',
                'status=@re4k%20Test&in_reply_to_status_id=xxx'
            )->params
        );
    }
    
}