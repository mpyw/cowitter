<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Co\CoInterface;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Components\StreamHandler;
use mpyw\Cowitter\Helpers\ResponseYielder;

trait OAuth2ClientTrait
{
    public function oauthForBearerToken()
    {
        $obj = ResponseYielder::syncExecDecoded($this->curl->oauthForBearerToken());
        return $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->access_token,
            '',
        ]);
    }

    public function oauthForBearerTokenAsync()
    {
        $obj = (yield ResponseYielder::asyncExecDecoded($this->curl->oauthForBearerToken()));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->access_token,
            '',
        ]);
    }

    public function invalidateBearerToken()
    {
        ResponseYielder::syncExecDecoded($this->curl->invalidateBearerToken());
    }

    public function invalidateBearerTokenAsync()
    {
        yield ResponseYielder::asyncExecDecoded($this->curl->invalidateBearerToken());
    }

    public function get2($endpoint, array $params = [], $return_response_object = false)
    {
        return ResponseYielder::syncExecDecoded($this->curl->get2($endpoint, $params), $return_response_object);
    }

    public function get2Async($endpoint, array $params = [], $return_response_object = false)
    {
        return ResponseYielder::asyncExecDecoded($this->curl->get2($endpoint, $params), $return_response_object);
    }
}
