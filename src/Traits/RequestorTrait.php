<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Co\CoInterface;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Components\StreamHandler;
use mpyw\Cowitter\Helpers\ResponseYielder;

trait RequestorTrait
{
    public function getAsync($endpoint, array $params = [])
    {
        yield CoInterface::RETURN_WITH => $this->invokeFilter(
            yield ResponseYielder::asyncExecDecoded($this->curl->get($endpoint, $params))
        );
    }

    public function postAsync($endpoint, array $params = [])
    {
        yield CoInterface::RETURN_WITH => $this->invokeFilter(
            yield ResponseYielder::asyncExecDecoded($this->curl->post($endpoint, $params))
        );
    }

    public function postMultipartAsync($endpoint, array $params = [])
    {
        yield CoInterface::RETURN_WITH => $this->invokeFilter(
            yield ResponseYielder::asyncExecDecoded($this->curl->postMultipart($endpoint, $params))
        );
    }

    public function get($endpoint, array $params = [])
    {
        return $this->invokeFilter(ResponseYielder::syncExecDecoded($this->curl->get($endpoint, $params)));
    }

    public function post($endpoint, array $params = [])
    {
        return $this->invokeFilter(ResponseYielder::syncExecDecoded($this->curl->post($endpoint, $params)));
    }

    public function postMultipart($endpoint, array $params = [])
    {
        return $this->invokeFilter(ResponseYielder::syncExecDecoded($this->curl->postMultipart($endpoint, $params)));
    }

    public function streamingAsync($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null)
    {
        $handler = new StreamHandler($header_response_handler, $event_handler);
        $ch = $this->curl->streaming($endpoint, $params, $handler);
        try {
            $result = (yield $ch);
        } catch (CURLException $e) {
            if (!$handler->isHaltedByUser()) {
                throw $e;
            }
        }
        if (!$handler->isHaltedByUser()) {
            throw new \UnexpectedValueException('Streaming stopped unexpectedly.');
        }
        yield CoInterface::RETURN_WITH => $this->invokeFilter($handler->getHeaderResponse());
    }

    public function streaming($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null)
    {
        $handler = new StreamHandler($header_response_handler, $event_handler);
        $ch = $this->curl->streaming($endpoint, $params, $handler);
        $result = curl_exec($ch);
        if (!$handler->isHaltedByUser() && $result === false) {
            throw new CURLException(curl_error($ch), curl_errno($ch), $ch);
        }
        if (!$handler->isHaltedByUser()) {
            throw new \UnexpectedValueException('Streaming stopped unexpectedly.');
        }
        return $this->invokeFilter($handler->getHeaderResponse());
    }
}
