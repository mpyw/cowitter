<?php

use mpyw\Co\Co;

use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\CurlExecutor;
use mpyw\Cowitter\HttpException;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ReponseAndHttpExceptionTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function _before()
    {
    }

    public function testInvalidResponseLine()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://localhost:8080/simple/invalid_response_line.php',
            CURLOPT_HEADER => true,
        ]);
        $this->setExpectedException(\UnexpectedValueException::class, 'Invalid response line.');
        $response = CurlExecutor::exec($ch);
    }

    public function testRedirectAndHeaders()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://localhost:8080/simple/redirect.php',
            CURLOPT_HEADER => true,
        ]);
        $response = CurlExecutor::exec($ch);
        $this->assertEquals('http://localhost:8080/simple/headers.php', curl_getinfo($response->getHandle(), CURLINFO_EFFECTIVE_URL));
        $this->assertEquals($response->getVersion(), '1.1');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getReasonPhrase(), 'OK');
        $this->assertArraySubset(['x-multiple-header' => ['a', 'b']], $response->getHeaders());
        $this->assertEquals(['a', 'b'], $response->getHeader('X-MULTIPLE-HEADER'));
        $this->assertEquals('a, b', $response->getHeaderLine('X-MULTIPLE-HEADER'));
        $this->setExpectedException(\UnderflowException::class, 'Decoded content has not created yet.');
        $response->getContent();
    }


    public function testRedirectAsync()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://localhost:8080/simple/redirect.php',
            CURLOPT_HEADER => true,
        ]);
        $response = Co::wait(CurlExecutor::execAsync($ch));
        $this->assertEquals('http://localhost:8080/simple/headers.php', curl_getinfo($response->getHandle(), CURLINFO_EFFECTIVE_URL));
    }

    public function testHttpException()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://localhost:8080/1.1/statuses/home_timeline.json.php',
            CURLOPT_HEADER => true,
        ]);
        try {
            CurlExecutor::execDecoded($ch);
        } catch (HttpException $e) {}
        $this->assertEquals('Bad Authentication data', $e->getMessage());
        $this->assertEquals(215, $e->getCode());
        $this->assertEquals(400, $e->getStatusCode());
        $this->assertEquals('Bad Request', $e->getReasonPhrase());
        $this->assertSame($ch, $e->getResponse()->getHandle());
    }
}
