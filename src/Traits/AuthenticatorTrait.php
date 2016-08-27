<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\Helpers\CurlExecutor;
use mpyw\Cowitter\Helpers\TokenParser;
use mpyw\Co\CoInterface;

trait AuthenticatorTrait
{
    abstract public function withCredentials(array $credentails);
    abstract protected function getInternalCurl();
    abstract protected function getInternalCredential();

    public function oauthForRequestTokenAsync($oauth_callback = null)
    {
        $obj = (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->oauthForRequestToken($oauth_callback)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function oauthForAccessTokenAsync($oauth_verifier)
    {
        $obj = (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->oauthForAccessToken($oauth_verifier)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function xauthForAccessTokenAsync($username, $password)
    {
        $obj = (yield CurlExecutor::execDecodedAsync($this->getInternalCurl()->xauthForAccessToken($username, $password)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function oauthForRequestToken($oauth_callback = null)
    {
        $obj = CurlExecutor::execDecoded($this->getInternalCurl()->oauthForRequestToken($oauth_callback));
        return $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function oauthForAccessToken($oauth_verifier)
    {
        $obj = CurlExecutor::execDecoded($this->getInternalCurl()->oauthForAccessToken($oauth_verifier));
        return $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function xauthForAccessToken($username, $password)
    {
        $obj = CurlExecutor::execDecoded($this->getInternalCurl()->xauthForAccessToken($username, $password));
        return $this->withCredentials([
            $this->getInternalCredential()['consumer_key'],
            $this->getInternalCredential()['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function loginAsync($username, $password)
    {
        $author = (yield $this->oauthForRequestTokenAsync('oob'));
        $scraper = $this->getInternalCurl()->browsing();
        curl_setopt_array($scraper, [
            CURLOPT_HTTPGET => true,
            CURLOPT_URL     => $author->getAuthorizeUrl(true),
        ]);
        $authenticity_token = TokenParser::parseAuthenticityToken((yield CurlExecutor::execAsync($scraper)));
        curl_setopt_array($scraper, [
            CURLOPT_URL        => $author->getAuthorizeUrl(true),
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => $username,
                'session[password]'          => $password,
                'authenticity_token'         => $authenticity_token,
            ], '', '&'),
        ]);
        $verifier = TokenParser::parseVerifier((yield CurlExecutor::execAsync($scraper)));
        yield CoInterface::RETURN_WITH => $author->oauthForAccessTokenAsync($verifier);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function login($username, $password)
    {
        $author = $this->oauthForRequestToken('oob');
        $scraper = $this->getInternalCurl()->browsing();
        curl_setopt_array($scraper, [
            CURLOPT_HTTPGET => true,
            CURLOPT_URL     => $author->getAuthorizeUrl(true),
        ]);
        $authenticity_token = TokenParser::parseAuthenticityToken(CurlExecutor::exec($scraper));
        curl_setopt_array($scraper, [
            CURLOPT_URL        => $author->getAuthorizeUrl(true),
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => $username,
                'session[password]'          => $password,
                'authenticity_token'         => $authenticity_token,
            ], '', '&'),
        ]);
        $verifier = TokenParser::parseVerifier(CurlExecutor::exec($scraper));
        return $author->oauthForAccessToken($verifier);
    }
}
