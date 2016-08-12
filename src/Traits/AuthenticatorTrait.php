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
        $response = (yield ResponseYielder::asyncExecDecoded($this->curl->oauthForRequestToken($oauth_callback)));
        $obj = $response->getContent();
        yield CoInterface::RETURN_WITH => $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
    }

    public function oauthForAccessTokenAsync($oauth_verifier)
    {
        $response = (yield ResponseYielder::asyncExecDecoded($this->curl->oauthForAccessToken($oauth_verifier)));
        $obj = $response->getContent();
        yield CoInterface::RETURN_WITH => $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
    }

    public function xauthForAccessTokenAsync($username, $password)
    {
        $response = (yield ResponseYielder::asyncExecDecoded($this->curl->xauthForAccessToken($username, $password)));
        $obj = $response->getContent();
        yield CoInterface::RETURN_WITH => $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
    }

    public function oauthForRequestToken($oauth_callback = null)
    {
        $response = ResponseYielder::syncExecDecoded($this->curl->oauthForRequestToken($oauth_callback));
        $obj = $response->getContent();
        return $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
    }

    public function oauthForAccessToken($oauth_verifier)
    {
        $response = ResponseYielder::syncExecDecoded($this->curl->oauthForAccessToken($oauth_verifier));
        $obj = $response->getContent();
        return $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
    }

    public function xauthForAccessToken($username, $password)
    {
        $response = ResponseYielder::syncExecDecoded($this->curl->xauthForAccessToken($username, $password));
        $obj = $response->getContent();
        return $this->withCredential(new Credential([
            $this->credential['consumer_key'],
            $this->credential['consumer_secret'],
            $obj->oauth_token,
            $obj->oauth_token_secret,
        ]));
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
