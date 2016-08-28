<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Co\CoInterface;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Components\StreamHandler;
use mpyw\Cowitter\Helpers\CurlExecutor;

trait RequestorTrait
{
    abstract protected function getInternalCurl();

    public function getAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->get($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function postAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->post($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function postMultipartAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->postMultipart($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function get($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->get($endpoint, $params), $return_response_object);
    }

    public function post($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->post($endpoint, $params), $return_response_object);
    }

    public function postMultipart($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->postMultipart($endpoint, $params), $return_response_object);
    }

    public function streamingAsync($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null)
    {
        $handler = new StreamHandler($header_response_handler, $event_handler);
        $ch = $this->getInternalCurl()->streaming($endpoint, $params, $handler);
        try {
            yield $ch;
        } catch (CURLException $e) {
            if (!$handler->isHaltedByUser()) {
                throw $e;
            }
        }
        if (!$handler->isHaltedByUser()) {
            throw new \UnexpectedValueException('Streaming stopped unexpectedly.');
        }
    }

    public function streaming($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null)
    {
        $handler = new StreamHandler($header_response_handler, $event_handler);
        $ch = $this->getInternalCurl()->streaming($endpoint, $params, $handler);
        $result = curl_exec($ch);
        if (!$handler->isHaltedByUser() && $result === false) {
            throw new CURLException(curl_error($ch), curl_errno($ch), $ch);
        }
        if (!$handler->isHaltedByUser()) {
            throw new \UnexpectedValueException('Streaming stopped unexpectedly.');
        }
    }

    public function getOutAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->getOut($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function postOutAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->postOut($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function postMultipartOutAsync($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->postMultipartOut($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function getOut($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->getOut($endpoint, $params), $return_response_object);
    }

    public function postOut($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->postOut($endpoint, $params), $return_response_object);
    }

    public function postMultipartOut($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->postMultipartOut($endpoint, $params), $return_response_object);
    }
}
