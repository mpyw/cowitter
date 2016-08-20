<?php

namespace mpyw\Cowitter\Components;

use mpyw\Co\Co;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\ResponseBodyDecoder;

class StreamHandler
{
    protected $headerResponse;
    protected $headerResponseBuffer = '';
    protected $headerResponseHandler;
    protected $eventBuffer = '';
    protected $eventHandler;
    protected $haltedByUser = false;

    public function __construct(callable $header_response_handler = null, callable $event_handler = null)
    {
        $this->headerResponseHandler = $header_response_handler;
        $this->eventHandler          = $event_handler;
    }

    public function headerFunction($ch, $str)
    {
        $handle = $this->headerResponseHandler;
        $this->headerResponseBuffer .= $str;
        if (substr($this->headerResponseBuffer, -4) === "\r\n\r\n") {
            $this->headerResponse = new Response($this->headerResponseBuffer, $ch);
            if ($handle) {
                (new \ReflectionFunction($handle))->isGenerator()
                ? Co::async($handle($this->headerResponse))
                : $handle($this->headerResponse);
            }
        }
        return strlen($str);
    }

    protected function processLine($line)
    {
        $handle = $this->eventHandler;
        if ('' === $line = rtrim($line)) {
            return;
        }
        $event = ResponseBodyDecoder::getDecodedResponse($this->headerResponse, $line);
        if ($handle) {
            if ((new \ReflectionFunction($handle))->isGenerator()) {
                Co::async(function () use ($handle, $event) {
                    if (false === (yield $handle($event->getContent()))) {
                        $this->haltedByUser = true;
                    }
                });
            } elseif (false === $handle($event->getContent())) {
                $this->haltedByUser = true;
            }
        }
    }

    public function writeFunction($ch, $str)
    {
        if ($this->haltedByUser) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }
        $this->eventBuffer .= $str;
        if (200 !== $code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            ResponseBodyDecoder::getDecodedResponse($this->headerResponse, $this->eventBuffer);
            throw new \UnexpectedValueException('Unexpected response: ' . $this->eventBuffer);
        }
        while (false !== $pos = strpos($this->eventBuffer, "\n")) {
            $line = substr($this->eventBuffer, 0, $pos + 1);
            $this->eventBuffer = substr($this->eventBuffer, $pos + 1);
            $this->processLine($line);
            if ($this->haltedByUser) {
                return 0;
            }
        }
        return strlen($str);
    }

    public function isHaltedByUser()
    {
        return $this->haltedByUser;
    }
}
