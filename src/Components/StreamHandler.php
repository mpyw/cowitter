<?php

namespace mpyw\Cowitter\Components;

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
        if ($str === "\r\n") {
            $this->headerResponse = new Response($this->headerResponseBuffer, $ch);
            if ($handle) {
                $handle($this->headerResponse);
            }
        }
        return strlen($str);
    }

    public function writeFunction($ch, $str)
    {
        $handle = $this->eventHandler;
        $this->eventBuffer .= $str;
        if (200 !== $code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            ResponseBodyDecoder::getDecodedResponse($this->headerResponse, $ch, $this->eventBuffer);
            throw new \UnexpectedValueException('Unexpected response: ' . $this->eventBuffer);
        }
        if (substr($this->eventBuffer, -1) !== "\n") {
            return strlen($str);
        }
        if (rtrim($this->eventBuffer) === '') {
            $this->eventBuffer = '';
            return strlen($str);
        }
        $event = ResponseBodyDecoder::getDecodedResponse($this->headerResponse, $ch, $this->eventBuffer);
        $signal = $handle ? $handle($event->getContent()) : true;
        if ($signal === false) {
            $this->haltedByUser = true;
            return 0;
        }
        $this->eventBuffer = '';
        return strlen($str);
    }

    public function isHaltedByUser()
    {
        return $this->haltedByUser;
    }

    public function getHeaderResponse()
    {
        return $this->headerResponse;
    }
}
