<?php

namespace mpyw\Cowitter\Components;

use mpyw\Cowitter\Helpers\RequestParamValidator;
use mpyw\Cowitter\Helpers\UrlNormalizer;

class CurlInitializer
{
    protected $credential;
    protected $options;

    public function __construct(Credential $credential, array $options)
    {
        $this->credential = $credential;
        $this->options = $options;
    }

    public function get($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => !$params ? $url : $url . '?' . http_build_query($params, '', '&'),
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeaders($url, 'GET', $params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPGET        => true,
        ]));
        return $ch;
    }

    public function post($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeaders($url, 'POST', $params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params, '', '&'),
        ]));
        return $ch;
    }

    public function postMultipart($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateMultipartParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeaders($url, 'POST', []),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
        ]));
        return $ch;
    }

    public function getOut($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::outSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => !$params ? $url : $url . '?' . http_build_query($params, '', '&'),
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeadersForOAuthEcho(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPGET        => true,
        ]));
        return $ch;
    }

    public function postOut($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::outSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeadersForOAuthEcho(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params, '', '&'),
        ]));
        return $ch;
    }

    public function postMultipartOut($endpoint, array $params)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::outSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateMultipartParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeadersForOAuthEcho(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
        ]));
        return $ch;
    }

    public function streaming($endpoint, array $params, StreamHandler $handler)
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeaders($url, 'POST', $params),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params, '', '&'),
            CURLOPT_HEADERFUNCTION => [$handler, 'headerFunction'],
            CURLOPT_WRITEFUNCTION  => [$handler, 'writeFunction'],
        ]));
        return $ch;
    }

    public function oauthForRequestToken($oauth_callback = null)
    {
        if ($oauth_callback !== null) {
            $oauth_callback = RequestParamValidator::validateStringable('oauth_callback', $oauth_callback);
        }
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => 'https://api.twitter.com/oauth/request_token',
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeadersForRequestToken($oauth_callback),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
        ]));
        return $ch;
    }

    public function oauthForAccessToken($oauth_verifier)
    {
        $oauth_verifier = RequestParamValidator::validateStringable('oauth_verifier', $oauth_verifier);
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => 'https://api.twitter.com/oauth/access_token',
            CURLOPT_HTTPHEADER     => $this->credential->getOAuthHeadersForAccessToken($oauth_verifier),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
        ]));
        return $ch;
    }

    public function xauthForAccessToken($username, $password)
    {
        $username = RequestParamValidator::validateStringable('username', $username);
        $password = RequestParamValidator::validateStringable('password', $password);
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => 'https://api.twitter.com/oauth/access_token',
            CURLOPT_HTTPHEADER     => $this->credential->getXAuthHeadersForAccessToken($username, $password),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'x_auth_username' => $username,
                'x_auth_password' => $password,
                'x_auth_mode'     => 'client_auth',
            ], '', '&'),
        ]));
        return $ch;
    }

    public function oauthForBearerToken()
    {
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => 'https://api.twitter.com/oauth2/token',
            CURLOPT_HTTPHEADER     => $this->credential->getBasicHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'client_credentials',
            ], '', '&'),
        ]));
        return $ch;
    }

    public function get2($endpoint, $params = [])
    {
        $ch = curl_init();
        list($url, $extra) = UrlNormalizer::twitterSplitUrlAndParameters($endpoint);
        $params += $extra;
        $params = RequestParamValidator::validateParams($params);
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => !$params ? $url : $url . '?' . http_build_query($params, '', '&'),
            CURLOPT_HTTPHEADER     => $this->credential->getBearerHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPGET        => true,
        ]));
        return $ch;
    }

    public function invalidateBearerToken()
    {
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_URL            => 'https://api.twitter.com/oauth2/invalidate_token',
            CURLOPT_HTTPHEADER     => $this->credential->getBasicHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'access_token' => rawurldecode($this->credential->token),
            ], '', '&', PHP_QUERY_RFC3986),
        ]));
        return $ch;
    }

    public function browsing()
    {
        $ch = curl_init();
        curl_setopt_array($ch, array_replace($this->options, [
            CURLOPT_COOKIEFILE     => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
        ]));
        return $ch;
    }
}
