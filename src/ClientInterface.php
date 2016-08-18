<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\ClientInterface;
use mpyw\Cowitter\ResponseInterface;
use mpyw\Cowitter\HttpExceptionInterface;
use mpyw\Cowitter\MediaInterface;

interface ClientInterface
{
    /**
     * Customize or retrive credentials and cURL options.
     *
     * @param array $credentials
     *     Free-style credential notations.
     *       e.g.
     *        Recommended (assoc-style):
     *            ['consumer_key' => 'a', 'consumer_secret' => 'b', 'token' => 'c', 'token_secret' => 'd']
     *        Recomended (array-style):
     *            ['a', 'b', 'c', 'd']
     *        Others:
     *            ['consumerKey' => 'a', 'consumerSecret' => 'b', 'token' => 'c', 'tokenSecret' => 'd']
     *            ['consumer_key' => 'a', 'consumer_secret' => 'b', 'oauth_token' => 'c', 'oauth_token_secret' => 'd']
     *            ['ck' => 'a', 'cs' => 'b', 't' => 'c', 'ts' => 'd']
     *            and more...
     *
     * @param array $options
     *     cURL options.
     *       e.g. [CURLOPT_USERAGENT => 'abc'],
     *            ['CURLOPT_USERAGENT' => 'abc']
     *
     * @param bool $assoc
     *     Use Assoc-style or not.
     *
     * @param bool $stringify
     *     Stringify CURLOPT_* constant names or not.
     */
    public function __construct(array $credentials, array $options = []);
    public function withCredentials(array $credentails); // : ClientInterface
    public function withOptions(array $options); // : ClientInterface
    public function getCredentials($assoc = false); // : array
    public function getOptions($stringify = false); // : array

    /**
     * Retrive part of credentials using magic methods or ArrayAccess interface.
     *
     * @param string $key
     * @param string $offset
     *
     *       e.g.
     *           $a = $client->consumer_key;
     *           $b = $client['consumer_secret'];
     *           $c = $client[2];
     *           $d = $client->tokenSecret;
     */
    public function __get($key); // : string
    public function __isset($key); // : boolean
    public function offsetGet($offset); // : string
    public function offsetExists($offset); // : boolean

    /**
     * Generate OAuth 1.0a authorization/authentication URL.
     *
     * @param string $force_login Include query "force_login=1" or not.
     */
    public function getAuthorizeUrl($force_login = false); // : string
    public function getAuthenticateUrl($force_login = false); // : string

    /**
     * Renew with OAuth 1.0a authorized/authenticated credential.
     * login() uses scraping.
     *
     * @param string|null $oauth_callback Callback URL or "oob".
     * @param string      $oauth_verifier
     * @param string      $username       Username or email.
     * @param string      $password
     * @throws HttpExceptionInterface
     */
    public function oauthForRequestToken($oauth_callback = null); // : ClientInterface
    public function oauthForAccessToken($oauth_verifier); // : ClientInterface
    public function xauthForAccessToken($username, $password); // : ClientInterface
    public function login($username, $password); // : ClientInterface
    public function oauthForRequestTokenAsync($oauth_callback = null); // : \Generator<ClientInterface>
    public function oauthForAccessTokenAsync($oauth_verifier); // : \Generator<ClientInterface>
    public function xauthForAccessTokenAsync($username, $password); // : \Generator<ClientInterface>
    public function loginAsync($username, $password); // : \Generator<ClientInterface>

    /**
     * Send OAuth 1.0a request.
     * postMultipart() should be used for uploading files.
     *
     * @param string $endpoint
     *     Partial or full URL.
     *       e.g. "statuses/update"
     *            "https://api.twitter.com/1.1/statuses/update.json"
     *
     * @param array $params
     *     String, number or CURLFile object can be included.
     *     Null is ignored.
     *     CURLFile is automatically Base64-encoded in non-multipart mode.
     *
     * @param bool $return_response_object
     *     Wrap content with Response object or not.
     *
     * @throws HttpExceptionInterface
     */
    public function get($endpoint, array $params = [], $return_response_object = false); // : \stdClass|array|MediaInterface|ResponseInterface
    public function post($endpoint, array $params = [], $return_response_object = false); // : \stdClass|ResponseInterface
    public function postMultipart($endpoint, array $params = [], $return_response_object = false); // : \stdClass|ResponseInterface
    public function getAsync($endpoint, array $params = [], $return_response_object = false); // : \Generator<\stdClass|array|MediaInterface|ResponseInterface
    public function postAsync($endpoint, array $params = [], $return_response_object = false); // : \Generator<\stdClass|ResponseInterface>
    public function postMultipartAsync($endpoint, array $params = [], $return_response_object = false); // : \Generator<\stdClass|ResponseInterface>

    /**
     * Send OAuth Echo request to external JSON API.
     * postMultipartOut() should be used for uploading files.
     *
     * @param string $url
     *     Full URL.
     *       e.g "https://aclog.rhe.jp/api/tweets/user_timeline.json"
     *
     * @param array $params
     *     String, number or CURLFile object can be included.
     *     Null is ignored.
     *     CURLFile is automatically Base64-encoded in non-multipart mode.
     *
     * @param bool $return_response_object
     *     Wrap content with Response object or not.
     *
     * @throws HttpExceptionInterface
     */
    public function getOut($url, array $params = [], $return_response_object = false); // : \stdClass|array|ResponseInterface
    public function postOut($url, array $params = [], $return_response_object = false); // : \stdClass|ResponseInterface
    public function postMultipartOut($url, array $params = [], $return_response_object = false); // : \stdClass|ResponseInterface
    public function getOutAsync($url, array $params = [], $return_response_object = false); // : \Generator<\stdClass|array|ResponseInterface
    public function postOutAsync($url, array $params = [], $return_response_object = false); // : \Generator<\stdClass|ResponseInterface>
    public function postMultipartOutAsync($url, array $params = [], $return_response_object = false); // : \Generator<\stdClass|ResponseInterface>

    /**
     * Send OAuth 1.0a streaming request.
     *
     * @param string $endpoint
     *     Partial or full URL.
     *       e.g. "user"
     *            "https://userstream.twitter.com/1.1/user.json"
     *
     * @param callable $event_handler
     *     Each event object is passed as the first argument.
     *     If you return false in your callback, the streaming will stop without exception occurrence.
     *     If your callback is Generator function, it is implicitly wrapped into Co::async() call.
     *     Be carefull that exceptions can be captured only by Co::wait() / Co::async() options or outer scope of Co::wait().
     *       Signature: function (\stdClass $event);
     *
     * @param array $params
     *     String, number or CURLFile object can be included.
     *     Null is ignored.
     *
     * @param callable|null $header_response_handler
     *     Receive Response object that only contains header information as the first argument.
     *     If your callback is Generator function, it is implicitly wrapped into Co::async() call.
     *     Be carefull that exceptions can be captured only by Co::wait() / Co::async() options or outer scope of Co::wait().
     *       Signature: function (ResponseInterface $response);
     *
     * @throws HttpExceptionInterface
     */
    public function streaming($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null); // : null
    public function streamingAsync($endpoint, callable $event_handler, array $params = [], callable $header_response_handler = null); // : \Generator<null>

    /**
     * Upload files using OAuth 1.0a media uploading API.
     *
     * @param \SplFileObject $file
     *     SplFileObject opened with readable mode, which MUST be rewindable.
     *
     * @param string $media_category
     *     "tweet_image", "tweet_gif" or "tweet_video"
     *
     * @param callable|null $on_uploading
     * @param callable|null $on_processing
     *     Progress functions while uploading/processing.
     *     If you return false in your callback, client aborts waiting and immediately return information stdClass object.
     *     If your callback is Generator function, it is implicitly wrapped into Co::async() call.
     *     Be carefull that exceptions can be captured only by Co::wait() / Co::async() options or outer scope of Co::wait().
     *       Signature: function (int $percent);
     *
     * @param int $chunk_size
     *     File data is split into chunks with specified size.
     *
     * @throws HttpExceptionInterface
     */
    public function uploadAsync(\SplFileObject $file, $media_category = null, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000); // : \Generator<\stdClass>
    public function uploadImageAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000); // : \Generator<\stdClass>
    public function uploadAnimeGifAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000); // : \Generator<\stdClass>
    public function uploadVideoAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000); // : \Generator<\stdClass>

    /**
     * Renew with OAuth 2.0 authorized/authenticated credential.
     *
     * @throws HttpExceptionInterface
     */
    public function oauthForBearerToken(); // : ClientInterface
    public function oauthForBearerTokenAsync(); // : \Generator<ClientInterface>

    /**
     * Invalidate OAuth 2.0 token.
     *
     * @throws HttpExceptionInterface
     */
    public function invalidateBearerToken(); // : null
    public function invalidateBearerTokenAsync(); // : \Generator<null>

    /**
     * Send OAuth 2.0 request.
     *
     * @param string $endpoint
     *     Partial or full URL.
     *       e.g. "search/tweets"
     *            "https://api.twitter.com/1.1/search/tweets.json"
     *
     * @param array $params
     *     String, number or CURLFile object can be included.
     *     Null is ignored.
     *
     * @param bool $return_response_object
     *     Wrap content with Response object or not.
     *
     * @throws HttpExceptionInterface
     */
    public function get2($endpoint, array $params = [], $return_response_object = false); // : \stdClass|array|ResponseInterface
    public function get2Async($endpoint, array $params = [], $return_response_object = false); // : \Generator<\stdClass|array|ResponseInterface>
}
