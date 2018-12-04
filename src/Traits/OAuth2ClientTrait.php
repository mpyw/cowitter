<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Co\CoInterface;
use mpyw\Cowitter\Helpers\CurlExecutor;

trait OAuth2ClientTrait
{
    abstract public function withCredentials(array $credentails);
    abstract protected function getInternalCredential();
    abstract protected function getInternalCurl();

    public function oauthForBearerToken()
    {
        $obj = CurlExecutor::execDecoded($this->getInternalCurl()->oauthForBearerToken());
        return $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->access_token,
            '',
        ]);
    }

    public function oauthForBearerTokenAsync()
    {
        $obj = (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->oauthForBearerToken()));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->access_token,
            '',
        ]);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function invalidateBearerToken()
    {
        CurlExecutor::execDecoded($this->getInternalCurl()->invalidateBearerToken());
    }

    public function invalidateBearerTokenAsync()
    {
        yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->invalidateBearerToken());
    }

    public function get2($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->get2($endpoint, $params), $return_response_object);
    }

    public function get2Async($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->get2($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function post2($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->post2($endpoint, $params), $return_response_object);
    }

    public function post2Async($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->post2($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function delete2($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->delete2($endpoint, $params), $return_response_object);
    }

    public function delete2Async($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->delete2($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function put2($endpoint, array $params = [], $return_response_object = false)
    {
        return CurlExecutor::execDecoded($this->getInternalCurl()->put2($endpoint, $params), $return_response_object);
    }

    public function put2Async($endpoint, array $params = [], $return_response_object = false)
    {
        yield CoInterface::RETURN_WITH => (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->put2($endpoint, $params), $return_response_object));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
