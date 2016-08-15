<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Co\CoInterface;
use mpyw\Cowitter\Helpers\ResponseYielder;

trait OAuth2ClientTrait
{
    abstract public function withCredentials(array $credentails);
    abstract protected function getInternalCredential();
    abstract protected function getInternalCurl();

    public function oauthForBearerToken()
    {
        $obj = ResponseYielder::syncExecDecoded($this->getInternalCurl()->oauthForBearerToken());
        return $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->access_token,
            '',
        ]);
    }

    public function oauthForBearerTokenAsync()
    {
        $obj = (yield ResponseYielder::asyncExecDecoded($this->getInternalCurl()->oauthForBearerToken()));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->access_token,
            '',
        ]);
    }

    public function invalidateBearerToken()
    {
        ResponseYielder::syncExecDecoded($this->getInternalCurl()->invalidateBearerToken());
    }

    public function invalidateBearerTokenAsync()
    {
        yield ResponseYielder::asyncExecDecoded($this->getInternalCurl()->invalidateBearerToken());
    }

    public function get2($endpoint, array $params = [], $return_response_object = false)
    {
        return ResponseYielder::syncExecDecoded($this->getInternalCurl()->get2($endpoint, $params), $return_response_object);
    }

    public function get2Async($endpoint, array $params = [], $return_response_object = false)
    {
        return ResponseYielder::asyncExecDecoded($this->getInternalCurl()->get2($endpoint, $params), $return_response_object);
    }
}
