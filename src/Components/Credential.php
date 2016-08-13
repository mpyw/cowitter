<?php

namespace mpyw\Cowitter\Components;
use mpyw\Cowitter\Helpers\CredentialNormalizer;

class Credential implements \ArrayAccess
{
    protected $consumer_key;
    protected $consumer_secret;
    protected $token = '';
    protected $token_secret = '';

    public function __construct(array $params = [])
    {
        foreach ($params as $key => $value) {
            $this->{CredentialNormalizer::normalizeCredentialParamName($key)} = $value;
        }
        if ($this->consumer_key === null || $this->consumer_secret === null) {
            throw new \DomainException('"consumer_key" and "consumer_secret" are at least required.');
        }
    }

    public function with(array $params = [])
    {
        $clone = clone $this;
        foreach ($params as $key => $value) {
            $this->{CredentialNormalizer::normalizeCredentialParamName($key)} = $value;
        }
        return $clone;
    }

    public function toArray($assoc = false)
    {
        $values = get_object_vars($this);
        return $assoc ? $values : array_values($values);
    }

    public function offsetGet($offset)
    {
        return $this->{CredentialNormalizer::normalizeCredentialParamName($offset)};
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Unsupported operation.');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Unsupported operation.');
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function __get($key)
    {
        return $this->{CredentialNormalizer::normalizeCredentialParamName($key)};
    }

    public function __isset($key)
    {
        return isset($this->{CredentialNormalizer::normalizeCredentialParamName($key)});
    }

    public function getAuthorizeUrl($force_login = false)
    {
        $params = http_build_query([
            'oauth_token' => $this->token,
            'force_login' => $force_login ? 1 : null,
        ], '', '&');
        return 'https://api.twitter.com/oauth/authorize?' . $params;
    }

    public function getAuthenticateUrl($force_login = false)
    {
        $params = http_build_query([
            'oauth_token' => $this->token,
            'force_login' => $force_login ? 1 : null,
        ], '', '&');
        return 'https://api.twitter.com/oauth/authenticate?' . $params;
    }

    public function getOAuthHeaders($url, $method, array $endpoint_params)
    {
        return static::buildOAuthHeaders(
            $url,
            $method,
            [
                'oauth_consumer_key' => $this->consumer_key,
                'oauth_token'        => $this->token,
            ],
            [$this->consumer_secret, $this->token_secret],
            $endpoint_params
        );
    }

    public function getBasicHeaders()
    {
        $header = base64_encode(implode(':', array_map('rawurlencode', [
            $this->consumer_key,
            $this->consumer_secret,
        ])));
        return ['Authorization: Basic ' . $header];
    }

    public function getBearerHeaders()
    {
        return ['Authorization: Bearer ' . $this->token];
    }

    public function getOAuthHeadersForOAuthEcho()
    {
        $url     = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $headers = static::getOAuthHeaders($url, 'GET', []);
        return [
            'X-Auth-Service-Provider: ' . $url,
            'X-Verify-Credentials-Authorization: OAuth realm="http://api.twitter.com/", ' . substr($headers[0], 21),
        ];
    }

    public function getOAuthHeadersForRequestToken($oauth_callback = null)
    {
        return static::buildOAuthHeaders(
            'https://api.twitter.com/oauth/request_token',
            'POST',
            [
                'oauth_consumer_key' => $this->consumer_key,
            ] + ($oauth_callback === null ? [] :
            [
                'oauth_callback' => $oauth_callback,
            ]),
            [$this->consumer_secret, ''],
            []
        );
    }

    public function getOAuthHeadersForAccessToken($oauth_verifier)
    {
        return static::buildOAuthHeaders(
            'https://api.twitter.com/oauth/access_token',
            'POST',
            [
                'oauth_consumer_key' => $this->consumer_key,
                'oauth_token'        => $this->token,
                'oauth_verifier'     => $oauth_verifier,
            ],
            [$this->consumer_secret, $this->token_secret],
            []
        );
    }

    public function getXAuthHeadersForAccessToken($username, $password)
    {
        return static::buildOAuthHeaders(
            'https://api.twitter.com/oauth/access_token',
            'POST',
            [
                'oauth_consumer_key' => $this->consumer_key,
            ],
            [$this->consumer_secret, ''],
            [
                'x_auth_username' => $username,
                'x_auth_password' => $password,
                'x_auth_mode'     => 'client_auth',
            ]
        );
    }

    protected static function buildOAuthHeaders($url, $method, array $oauth_params, array $key_params, array $endpoint_params)
    {
        $oauth_params += [
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0a',
            'oauth_nonce'            => sha1(openssl_random_pseudo_bytes(32)),
        ];
        $base = $oauth_params + $endpoint_params;
        uksort($base, 'strnatcmp');
        $oauth_params['oauth_signature'] = base64_encode(hash_hmac(
            'sha1',
            implode('&', array_map('rawurlencode', [
                strtoupper($method),
                $url,
                http_build_query($base, '', '&', PHP_QUERY_RFC3986)
            ])),
            implode('&', array_map('rawurlencode', $key_params)),
            true
        ));
        $tmp = [];
        foreach ($oauth_params as $key => $value) {
            $tmp[] = urlencode($key) . '="' . urlencode($value) . '"';
        }
        return ['Authorization: OAuth ' . implode(', ', $tmp)];
    }
}
