<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\Credential;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Components\CurlManager;
use mpyw\Cowitter\Helpers\ResponseYielder;
use mpyw\Cowitter\Helpers\RegexParser;
use mpyw\Co\CURLException;
use mpyw\Co\CoInterface;

trait AuthenticatorTrait
{
    public function oauthForRequestTokenAsync($oauth_callback = null)
    {
        $obj = (yield ResponseYielder::asyncExecDecoded($this->curl->oauthForRequestToken($oauth_callback)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function oauthForAccessTokenAsync($oauth_verifier)
    {
        $obj = (yield ResponseYielder::asyncExecDecoded($this->curl->oauthForAccessToken($oauth_verifier)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function xauthForAccessTokenAsync($username, $password)
    {
        $obj = (yield ResponseYielder::asyncExecDecoded($this->curl->xauthForAccessToken($username, $password)));
        yield CoInterface::RETURN_WITH => $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function oauthForRequestToken($oauth_callback = null)
    {
        $obj = ResponseYielder::syncExecDecoded($this->curl->oauthForRequestToken($oauth_callback));
        return $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function oauthForAccessToken($oauth_verifier)
    {
        $obj = ResponseYielder::syncExecDecoded($this->curl->oauthForAccessToken($oauth_verifier));
        return $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function xauthForAccessToken($username, $password)
    {
        $obj = ResponseYielder::syncExecDecoded($this->curl->xauthForAccessToken($username, $password));
        return $this->withCredentials([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]);
    }

    public function loginAsync($username, $password)
    {
        $author = (yield $this->oauthForRequestTokenAsync('oob'));
        $scraper = $this->curl->browsing();
        curl_setopt_array($scraper, [
            CURLOPT_HTTPGET => true,
            CURLOPT_URL     => $author->getAuthorizeUrl(true),
        ]);
        $authenticity_token = RegexParser::parseAuthenticityToken((yield ResponseYielder::asyncExec($scraper)), $scraper);
        curl_setopt_array($scraper, [
            CURLOPT_URL        => $author->getAuthorizeUrl(true),
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => $username,
                'session[password]'          => $password,
                'authenticity_token'         => $authenticity_token,
            ], '', '&'),
        ]);
        $verifier = RegexParser::parseVerifier((yield ResponseYielder::asyncExec($scraper)), $scraper);
        yield CoInterface::RETURN_WITH => $author->oauthForAccessTokenAsync($verifier);
    }

    public function login($username, $password)
    {
        $author = $this->oauthForRequestToken('oob');
        $scraper = $this->curl->browsing();
        curl_setopt_array($scraper, [
            CURLOPT_HTTPGET => true,
            CURLOPT_URL     => $author->getAuthorizeUrl(true),
        ]);
        $authenticity_token = RegexParser::parseAuthenticityToken(ResponseYielder::syncExec($scraper), $scraper);
        curl_setopt_array($scraper, [
            CURLOPT_URL        => $author->getAuthorizeUrl(true),
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'session[username_or_email]' => $username,
                'session[password]'          => $password,
                'authenticity_token'         => $authenticity_token,
            ], '', '&'),
        ]);
        $verifier = RegexParser::parseVerifier(ResponseYielder::syncExec($scraper), $scraper);
        return $author->oauthForAccessToken($verifier);
    }
}
