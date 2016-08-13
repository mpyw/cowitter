<?php

namespace mpyw\Cowitter;

interface ClientInterface
{
    public function __construct($credential, array $options = []);

    public function withCredential($credentail);
    public function withOptions(array $options);
    public function getOptions($stringify = false);
    public function getAuthorizeUrl($force_login = false);
    public function getAuthenticateUrl($force_login = false);

    public function oauthForRequestToken($oauth_callback = null);
    public function oauthForAccessToken($oauth_verifier);
    public function xauthForAccessToken($username, $password);
    public function login($username, $password);
    public function get($endpoint, array $params = []);
    public function post($endpoint, array $params = []);
    public function postMultipart($endpoint, array $params = []);
    public function streaming($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null);
    public function uploadFile(\SplFileObject $file, array $params = []);
    public function uploadLargeFile(\SplFileObject $file, array $params = [], callable $on_progress = null);

    public function oauthForRequestTokenAsync($oauth_callback = null);
    public function oauthForAccessTokenAsync($oauth_verifier);
    public function xauthForAccessTokenAsync($username, $password);
    public function loginAsync($username, $password);
    public function getAsync($endpoint, array $params = []);
    public function postAsync($endpoint, array $params = []);
    public function postMultipartAsync($endpoint, array $params = []);
    public function streamingAsync($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null);
    public function uploadFileAsync(\SplFileObject $file, array $params = []);
    public function uploadLargeFileAsync(\SplFileObject $file, array $params = [], callable $on_progress = null);
}
