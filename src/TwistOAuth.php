<?php

/* 
 * TwistOAuth Version 2.5.4
 * 
 * @author  CertaiN
 * @github  https://github.com/mpyw/TwistOAuth
 * @license BSD 2-Clause
 */

/**
 * Main class.
 */
final class TwistOAuth {
    
    /**
     * cURL execution limit.
     * 
     * @const CURLOPT_CONNECTTIMEOUT
     * @const CURLOPT_TIMEOUT
     * @const CURLOPT_MAXCONNECTS
     */
    const CURLOPT_CONNECTTIMEOUT = 10;
    const CURLOPT_TIMEOUT        = 20;
    const CURLOPT_MAXCONNECTS    = 10;
    
    /**
     * Request options.
     * 
     * @const MODE_DEFAULT       for various endpoints.
     * @const MODE_REQUEST_TOKEN for "oauth/request_token".
     * @const MODE_ACCESS_TOKEN  for "oauth/access_token".
     */
    const MODE_DEFAULT        = 0;
    const MODE_REQUEST_TOKEN  = 1;
    const MODE_ACCESS_TOKEN   = 2;
    
    /**
     * OAuth parameters.
     * 
     * @property-read string $ck consumer_key.
     * @property-read string $cs consumer_secret.
     * @property-read string $ot oauth_token. (request_token or access_token)
     * @property-read string $os oauth_token_secret. (request_token_secret or access_token_secret)
     */
    private $ck = ''; 
    private $cs = '';
    private $ot = '';
    private $os = '';
    
    /**
     * Generate a new account via abusing Mobile Web API.
     * 
     * @param string $fullname
     * @param string $screen_name
     * @param string $email
     * @param string $password
     * @param string [$proxy]     full proxy URL.
     *                            e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public static function androidSignUp($fullname, $screen_name, $email, $password, $proxy = '') {
        // abusing API key (unknown) 
        $to = new self('m9QsrrmJoANGROAiNKaC8g', 'udnsc1IAyTQnkj0KPfZffb9usZ6ZqVoXcdD3oxIVo');
        // abusing endpoint url
        $url = 'https://mobile.twitter.com/mobile_client_api/signup';
        $fullname    = self::validateString('$fullname', $fullname);
        $screen_name = self::validateString('$screen_name', $screen_name);
        $email       = self::validateString('$email', $email);
        $password    = self::validateString('$password', $password);
        $proxy       = self::validateString('$proxy', $proxy);
        $params = compact('fullname', 'screen_name', 'email', 'password');
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $to->getAuthorization($url, 'POST', $params, self::MODE_REQUEST_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        $response = self::decode($ch, curl_exec($ch));
        // this endpoint returns special JSON format on errors
        if (!isset($response->oauth_token, $response->oauth_token_secret)) {
            $info = curl_getinfo($ch);
            // each property is an array, so needs to be flatten
            $it = new RecursiveArrayIterator((array)$response);
            $it = new RecursiveIteratorIterator($it);
            throw new TwistException(implode("\n", iterator_to_array($it, false)), $info['http_code']);
        }
        // abusing API key (Twitter for Android)
        return new self(
            '3nVuSoBZnx6U4vzUxf5w',
            'Bcs59EFbbsdF6Sl9Ng71smgStWEGwXXKSjYvPVt7qys',
            $response->oauth_token,
            $response->oauth_token_secret
        );
    }
    
    /**
     * Execute direct OAuth login.
     * 
     * @param string $ck       consumer_key.
     * @param string $cs       consumer_secret.
     * @param string $username screen_name or email.
     * @param string $password 
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public static function login($ck, $cs, $username, $password, $proxy = '') {
        $to = new self($ck, $cs);
        $username = self::validateString('$username', $username);
        $password = self::validateString('$password', $password);
        $proxy    = self::validateString('$proxy', $proxy);
        $ch = self::curlInit($proxy);
        $to = $to->renewWithRequestToken('oob');
        self::curlSetOptForAuthenticityToken($ch, $to);
        $token = self::parseAuthenticityToken($ch, curl_exec($ch));
        self::curlSetOptForVerifier($ch, $to, $token, $username, $password);
        $verifier = self::parseVerifier($ch, curl_exec($ch));
        return $to->renewWithAccessToken($verifier);
    }
    
    /**
     * Execute multiple direct OAuth logins.
     * If $throw_in_process is false...
     *    TwistException is thrown only in case all requests fatally failed.
     *    In non-fatal case, return value contains TwistException as its elements.
     * If $throw_in_process is true...
     *    TwistException is thrown in all failure cases.
     * 
     * @param array $credentials
     *     e.g.
     *     array(
     *         'foo' => array('CONSUMER_KEY_foo', 'CONSUMER_SECRET_foo', 'USERNAME_foo', 'PASSWORD_foo'),
     *         'bar' => array('CONSUMER_KEY_bar', 'CONSUMER_SECRET_bar', 'USERNAME_bar', 'PASSWORD_bar'),
     *         'baz' => array('CONSUMER_KEY_baz', 'CONSUMER_SECRET_baz', 'USERNAME_baz', 'PASSWORD_baz'),
     *         ...
     *     )
     * @param bool   [$throw_in_process] 
     * @param string [$proxy]            full proxy URL.
     *                                   e.g. https://111.222.333.444:8080
     * @return array
     *     e.g.
     *     array(
     *         'foo' => TwistOAuth or TwistException,
     *         'bar' => TwistOAuth or TwistException,
     *         'baz' => TwistOAuth or TwistException,
     *         ...
     *     )
     * @throws TwistException
     */
    public static function multiLogin(array $credentials, $throw_in_process = false, $proxy = '') {
        static $names = array('consumer_key', 'consumer_secret', 'username', 'password');
        $mh = curl_multi_init();
        $tos    = array(); // TwistOAuth objects
        $states = array(); // states
        $chs    = array(); // cURL resources for API connection
        $schs   = array(); // cURL resources for scraping connection
        $wchs   = array(); // cURL resources waiting other connection closed
        if (!$credentials) {
            return array();
        }
        $c = 0;
        foreach ($credentials as $i => &$credential) {
            if (!is_array($credential)) {
                throw new InvalidArgumentException(sprintf(
                    '$credentials[%s] must be an array.',
                    $i
                ));
            }
            foreach ($names as $j => $name) {
                switch (true) {
                    case !isset($credential[$j]):
                    case false === $credential[$j] = filter_var($credential[$j]):
                        throw new InvalidArgumentException(sprintf(
                            'The value of $credentials[%s][%s] must be stringable.',
                            $i,
                            $j + 1,
                            $names[$j]
                        ));
                }
            }
            $res[$i]    = new TwistException('Failed to reach the final step.');
            $tos[$i]    = new self($credential[0], $credential[1]);
            $states[$i] = 4;
            $chs[$i]    = $tos[$i]->curlPostRequestToken('oob', $proxy);
            $schs[$i]   = null;
            if (++$c <= self::CURLOPT_MAXCONNECTS) {
                curl_multi_add_handle($mh, $chs[$i]);
            } else {
                $wchs[] = $chs[$i];
            }
        }
        unset($credential);
        // start requests 
        while (CURLM_CALL_MULTI_PERFORM === $stat = curl_multi_exec($mh, $running));
        if (!$running || $stat !== CURLM_OK) {
            throw new TwistException('Failed to start multiple requests.');
        }
        // wait cURL events
        do switch (curl_multi_select($mh, self::CURLOPT_TIMEOUT)) {
            case -1: // failed to select for various reason
                $add = false;
                // wait a bit, update $running flag, retry and continue
                usleep(10);
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
                continue 2;
            case 0:
                // timeout!
                if ($throw_in_process) {
                    throw new TwistException('Timeout.');
                }
                break 2;
            default:
                $add = false;
                // update $running flag
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
                // dequeue array of cURL which finished receiving data
                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    // search offset corresponds to the resource, in $chs or $schs
                    if (false === $i = array_search($raised['handle'], $chs, true)) {
                        $i = array_search($raised['handle'], $schs, true);
                    }
                    try {
                        // step to the next state
                        switch (--$states[$i]) {
                            case 3:
                                $obj = self::decode($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $tos[$i] = new self($tos[$i]->ck, $tos[$i]->cs, $obj->oauth_token, $obj->oauth_token_secret);
                                $schs[$i] = self::curlInit($proxy);
                                self::curlSetOptForAuthenticityToken($schs[$i], $tos[$i]);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                curl_multi_add_handle($mh, $schs[$i]);
                                $add = true;
                                break;
                            case 2:
                                $token = self::parseAuthenticityToken($raised['handle'], curl_multi_getcontent($raised['handle']));
                                self::curlSetOptForVerifier($raised['handle'], $tos[$i], $token, $credentials[$i][2], $credentials[$i][3]);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                curl_multi_add_handle($mh, $schs[$i]);
                                $add = true;
                                break;
                            case 1:
                                $verifier = self::parseVerifier($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $chs[$i] = $tos[$i]->curlPostAccessToken($verifier, $proxy);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                curl_multi_add_handle($mh, $chs[$i]);
                                $add = true;
                                break;
                            case 0:
                                $obj = self::decode($raised['handle'], curl_multi_getcontent($raised['handle']));
                                $res[$i] = new self($tos[$i]->ck, $tos[$i]->cs, $obj->oauth_token, $obj->oauth_token_secret);
                                curl_multi_remove_handle($mh, $raised['handle']);
                                if ($wch = array_shift($wchs)) {
                                    curl_multi_add_handle($mh, $wch);
                                    $add = true;
                                }
                        }
                    } catch (TwistException $e) {
                        if ($throw_in_process) {
                            $e->__construct('(' . $i . ') ' . $e->getMessage(), $e->getCode());
                            throw $e;
                        }
                        $res[$i] = $e;
                        curl_multi_remove_handle($mh, $raised['handle']);
                        if ($wch = array_shift($wchs)) {
                            curl_multi_add_handle($mh, $wch);
                            $add = true;
                        }
                    }
                } while ($remains);
        } while ($running || $add); // continue if still running or added new cURL resources
        return $res;
    }
    
    /**
     * Execute multiple cURL requests.
     * If $throw_in_process is false...
     *    TwistException is thrown only in case all requests fatally failed.
     *    In non-fatal case, return value contains TwistException as its elements.
     * If $throw_in_process is true...
     *    TwistException is thrown in all failure cases.
     * 
     * @param array $curls
     *     e.g.
     *     array(
     *         'foo' => cURL resource of foo
     *         'bar' => cURL resource of bar
     *         'baz' => cURL resource of baz
     *         ...
     *     )
     * @param bool [$throw_in_process]
     * @return array
     *     e.g.
     *     array(
     *         'foo' => stdClass or array or TwistImage or TwistException,
     *         'bar' => stdClass or array or TwistImage or TwistException,
     *         'baz' => stdClass or array or TwistImage or TwistException,
     *         ...
     *     )
     * @throws TwistException
     */
    public static function curlMultiExec(array $curls, $throw_in_process = false) {
        return self::curlMultiExecAction($curls, false, $throw_in_process);
    }
    
    /**
     * Execute multiple cURL streaming requests.
     * TwistException is thrown in all failure cases.
     * 
     * @param array $curls
     *     e.g.
     *     array(
     *         'foo' => cURL resource of foo
     *         'bar' => cURL resource of bar
     *         'baz' => cURL resource of baz
     *         ...
     *     )
     * @throws TwistException
     */
    public static function curlMultiStreaming(array $curls) {
        self::curlMultiExecAction($curls, true, true);
    }
    
    /**
     * Constructor.
     *
     * @param string $ck   consumer_key.
     * @param string $cs   consumer_secret.
     * @param string [$ot] oauth_token. (request_token or access_token)
     * @param string [$os] oauth_token_secret. (request_token_secret or access_token_secret)
     */
    public function __construct($ck, $cs, $ot = '', $os = '') {
        $this->ck = self::validateString('$ck', $ck);
        $this->cs = self::validateString('$cs', $cs);
        $this->ot = self::validateString('$ot', $ot);
        $this->os = self::validateString('$os', $os);
    }
    
    /**
     * Getter for private properties.
     *
     * @name string $name
     * @return string
     */
    public function __get($name) {
        $name = filter_var($name);
        if (!property_exists($this, $name)) {
            throw new OutOfRangeException('Invalid property: ' . $name);
        }
        return $this->$name;
    }
    
    /**
     * Issetter for private properties.
     *
     * @name string $name
     * @return bool
     */
    public function __isset($name) {
        return property_exists($this, filter_var($name));
    }
    
    /**
     * Get URL for authentication.
     *
     * @param bool [$force_login]
     * @return string URL
     */
    public function getAuthenticateUrl($force_login = false) {
        $params = http_build_query(array(
            'oauth_token' => $this->ot,
            'force_login' => $force_login ? 1 : null,
        ), '', '&');
        return 'https://api.twitter.com/oauth/authenticate?' . $params;
    }
    
    /**
     * Get URL for authorization.
     *
     * @param bool [$force_login]
     * @return string URL
     */
    public function getAuthorizeUrl($force_login = false) {
        $params = http_build_query(array(
            'oauth_token' => $this->ot,
            'force_login' => $force_login ? 1 : null,
        ), '', '&');
        return 'https://api.twitter.com/oauth/authorize?' . $params;
    }
    
    /**
     * Execute GET request.
     *
     * @param string $url      full or partial endpoint URL.
     *                         e.g. "statuses/show", "https://api.twitter.com/1.1/statuses/show.json"
     * @param mixed  [$params] 1-demensional array or query string.
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function get($url, $params = array(), $proxy = '') {
        $ch = $this->curlGet($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute GET OAuth Echo request.
     *
     * @param string $url      full URL.
     * @param mixed  [$params] 1-demensional array or query string.
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function getOut($url, $params = array(), $proxy = '') {
        $ch = $this->curlGetOut($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute streaming GET request.
     *
     * @param string   $url      full or partial endpoint URL.
     *                           e.g. "statuses/sample", "https://stream.twitter.com/1.1/statuses/sample.json"
     * @param callable $callback function for processing each message.
     *                           if it returns true, your connection will be aborted.
     * @param mixed    [$params] 1-demensional array or query string.
     * @param string   [$proxy]  full proxy URL.
     *                           e.g. https://111.222.333.444:8080
     * @throws TwistException
     */
    public function streaming($url, $callback, $params = array(), $proxy = '') {
        curl_exec($ch = $this->curlStreaming($url, $callback, $params, $proxy));
        // throw exception unless $callback returned true
        if (!self::isWriteFailure($ch)) {
            $info = curl_getinfo($ch);
            throw new TwistException('Streaming stopped unexpectedly.', $info['http_code']);
        }
    }
    
    /**
     * Execute POST request.
     *
     * @param string $url     full or partial endpoint URL.
     *                        e.g. "statuses/update", "https://api.twitter.com/1.1/statuses/update.json"
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function post($url, $params = array(), $proxy = '') {
        $ch = $this->curlPost($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute POST OAuth Echo request.
     *
     * @param string $url     full URL.
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function postOut($url, $params = array(), $proxy = '') {
        $ch = $this->curlPostOut($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute POST request for "oauth/request_token".
     *
     * @param string [$oauth_callback] 
     * @param string [$proxy]          full proxy URL.
     *                                 e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public function renewWithRequestToken($oauth_callback = '', $proxy = '') {
        $ch = $this->curlPostRequestToken($oauth_callback, $proxy);
        $response = self::decode($ch, curl_exec($ch));
        return new self($this->ck, $this->cs, $response->oauth_token, $response->oauth_token_secret);
    }
    
    /**
     * Execute POST request for "oauth/access_token".
     *
     * @param string $oauth_verifier
     * @param string [$proxy]        full proxy URL.
     *                               e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public function renewWithAccessToken($oauth_verifier, $proxy = '') {
        $ch = $this->curlPostAccessToken($oauth_verifier, $proxy);
        $response = self::decode($ch, curl_exec($ch));
        return new self($this->ck, $this->cs, $response->oauth_token, $response->oauth_token_secret);
    }
    
    /**
     * Execute POST request for "oauth/access_token" using xAuth.
     *
     * @param string $username screen_name or email.
     * @param string $password 
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public function renewWithAccessTokenX($username, $password, $proxy = '') {
        $ch = $this->curlPostAccessTokenX($username, $password, $proxy);
        $response = self::decode($ch, curl_exec($ch));
        return new self($this->ck, $this->cs, $response->oauth_token, $response->oauth_token_secret);
    }
        
    /**
     * Execute multipart POST request.
     *
     * @param string $url     full or partial endpoint URL.
     *                        e.g. "statuses/update_with_media", "https://api.twitter.com/1.1/statuses/update_with_media.json"
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function postMultipart($url, $params = array(), $proxy = '') {
        $ch = $this->curlPostMultipart($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Execute multipart POST OAuth Echo request.
     *
     * @param string $url     full URL.
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return mixed
     * @throws TwistException
     */
    public function postMultipartOut($url, $params = array(), $proxy = '') {
        $ch = $this->curlPostMultipartOut($url, $params, $proxy);
        $response = curl_exec($ch);
        return self::decode($ch, $response);
    }
    
    /**
     * Prepare cURL resource for GET request.
     *
     * @param string $url      full or partial endpoint URL.
     *                         e.g. "statuses/show", "https://api.twitter.com/1.1/statuses/show.json"
     * @param mixed  [$params] 1-demensional array or query string.
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlGet($url, $params = array(), $proxy = '') {
        return self::curlGetAction($url, $params, false, $proxy);
    }
    
    /**
     * Prepare cURL resource for GET OAuth Echo request.
     *
     * @param string $url      full URL.
     * @param mixed  [$params] 1-demensional array or query string.
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlGetOut($url, $params = array(), $proxy = '') {
        return self::curlGetAction($url, $params, true, $proxy);
    }
    
    /**
     * Prepare cURL resource for streaming GET request.
     *
     * @param string   $url      full or partial endpoint URL.
     *                           e.g. "statuses/sample", "https://stream.twitter.com/1.1/statuses/sample.json"
     * @param callable $callback function for processing each message.
     *                           if it returns true, your connection will be aborted.
     * @param mixed    [$params] 1-demensional array or query string.
     * @param string   [$proxy]  full proxy URL.
     *                           e.g. https://111.222.333.444:8080
     * @throws TwistException
     */
    public function curlStreaming($url, $callback, $params = array(), $proxy = '') {
        static $decode;
        if (!$decode) {
            if (version_compare(PHP_VERSION, '5.4.0') < 0) {
                $decode = function ($ch, $response) {
                    static $rm;
                    if (!$rm) {
                        $rm = new ReflectionMethod(__CLASS__, 'decode');
                        $rm->setAccessible(true);
                    }
                    return $rm->invoke(null, $ch, $response);
                };
            } else {
                $decode = array(__CLASS__, 'decode');
            }
        }
        $url      = self::url(self::validateString('$url', $url));
        $callback = self::validateCallback('$callback', $callback);
        $obj      = self::getParamObject(self::validateParams('$params', $params));
        $proxy    = self::validateString('$proxy', $proxy);
        $params   = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER     => $this->getAuthorization($url, 'GET', $params, 0),
            CURLOPT_URL            => $url . '?' . http_build_query($params, '', '&'),
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_WRITEFUNCTION  => function ($ch, $str) use ($callback, $decode) {
                static $first = true;
                static $buffer = '';
                $buffer .= $str;
                // skip empty line
                if (trim($buffer) === '') {
                    return strlen($str);
                }
                switch (true) {
                    // decodable first response
                    case $first and strpos($buffer, '{') !== 0 || json_decode($buffer):
                        $first = false;
                    // decodable line
                    case $buffer[strlen($buffer) - 1] === "\n":
                        if ($callback($decode($ch, $buffer))) {
                            return 0;
                        }
                        $buffer = '';
                    default:
                        return strlen($str);
                }
            }
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request.
     *
     * @param string $url     full or partial endpoint URL.
     *                        e.g. "statuses/update_with_media", "https://api.twitter.com/1.1/statuses/update_with_media.json"
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPost($url, $params = array(), $proxy = '') {
        return self::curlPostAction($url, $params, false, $proxy);
    }
    
    /**
     * Prepare cURL resource for POST OAuth Echo request.
     *
     * @param string $url     full URL.
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostOut($url, $params = array(), $proxy = '') {
        return self::curlPostAction($url, $params, true, $proxy);
    }
    
    /**
     * Prepare cURL resource for POST request "oauth/request_token".
     *
     * @param string [$oauth_callback] 
     * @param string [$proxy]          full proxy URL.
     *                                 e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostRequestToken($oauth_callback = '', $proxy = '') {
        $oauth_callback = self::validateString('$oauth_callback', $oauth_callback);
        $proxy          = self::validateString('$proxy', $proxy);        
        $url    = 'https://api.twitter.com/oauth/request_token';
        $params = compact('oauth_callback');
        $ch     = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $this->getAuthorization($url, 'POST', $params, self::MODE_REQUEST_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request "oauth/access_token".
     *
     * @param string $oauth_verifier
     * @param string [$proxy]        full proxy URL.
     *                               e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostAccessToken($oauth_verifier, $proxy = '') {
        $oauth_verifier = self::validateString('$oauth_verifier', $oauth_verifier);
        $proxy          = self::validateString('$proxy', $proxy);
        $url    = 'https://api.twitter.com/oauth/access_token';
        $params = compact('oauth_verifier');
        $ch     = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $this->getAuthorization($url, 'POST', $params, self::MODE_ACCESS_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request "oauth/access_token" using xAuth.
     *
     * @param string $username screen_name or email.
     * @param string $password 
     * @param string [$proxy]  full proxy URL.
     *                         e.g. https://111.222.333.444:8080
     * @return resource cURL
     * @throws TwistException
     */
    public function curlPostAccessTokenX($username, $password, $proxy = '') {
        $username = self::validateString('$username', $username);
        $password = self::validateString('$password', $password);
        $proxy    = self::validateString('$proxy', $proxy);
        $url = 'https://api.twitter.com/oauth/access_token';
        $params = array(
            'x_auth_mode'     => 'client_auth',
            'x_auth_username' => $username,
            'x_auth_password' => $password,
        );
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $this->getAuthorization($url, 'POST', $params, self::MODE_REQUEST_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for multipart POST request.
     *
     * @param string $url     full or partial endpoint URL.
     *                        e.g. "statuses/update_with_media", "https://api.twitter.com/1.1/statuses/update_with_media.json"
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return resource cURL
     */
    public function curlPostMultipart($url, $params = array(), $proxy = '') {
        return self::curlPostMultipartAction($url, $params, false, $proxy);
    }
    
    /**
     * Prepare cURL resource for multipart POST OAuth Echo request.
     *
     * @param string $url     full URL.
     * @param mixed  $params  1-demensional array or query string.
     * @param string [$proxy] full proxy URL.
     *                        e.g. https://111.222.333.444:8080
     * @return resource cURL
     */
    public function curlPostMultipartOut($url, $params = array(), $proxy = '') {
        return self::curlPostMultipartAction($url, $params, true, $proxy);
    }
    
    /**
     * Initialize cURL resource.
     * 
     * @proxy string $proxy
     * @return resource cURL
     */
    private static function curlInit($proxy) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_FOLLOWLOCATION => !ini_get('safe_mode'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => 'gzip',
            CURLOPT_COOKIEJAR      => '',
            CURLOPT_CONNECTTIMEOUT => self::CURLOPT_CONNECTTIMEOUT,
            CURLOPT_TIMEOUT        => self::CURLOPT_TIMEOUT,
        ));
        if ($proxy !== '') {
            if (false === $p = parse_url($proxy)) {
                throw new TwistException('The value of $proxy is invalid: Failed to parse.');
            }
            if (!isset($p['scheme'])) {
                throw new TwistException('The value of $proxy is invalid: Has no scheme.');
            }
            if (!isset($p['host'])) {
                throw new TwistException('The value of $proxy is invalid: Has no host.');
            }
            if (isset($p['path'])) {
                throw new TwistException('The value of $proxy is invalid: Has path.');
            }
            if (isset($p['query'])) {
                throw new TwistException('The value of $proxy is invalid: Has query.');
            }
            if (isset($p['fragment'])) {
                throw new TwistException('The value of $proxy is invalid: Has fragment.');
            }
            curl_setopt_array($ch, array(
                CURLOPT_HTTPPROXYTUNNEL => $p['scheme'] === 'https',
                CURLOPT_PROXY           => $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : ''),
                CURLOPT_PROXYUSERPWD    => (isset($p['user']) ? $p['user'] : 'anonymous') . ':' . (isset($p['pass']) ? $p['pass'] : ''),
            ));
            if (isset($p['port'])) {
                curl_setopt($ch, CURLOPT_PROXYPORT, $p['port']);
            }
        }
        return $ch;
    }
    
    /**
     * Decode response.
     * 
     * @param resource $ch
     * @param string   $response
     * @return mixed
     * @throws TwistException
     */
    private static function decode($ch, $response) {
        $ch       = self::validateCurl('$ch', $ch);
        $response = self::validateString('$response', $response);
        $info = curl_getinfo($ch);
        if (curl_errno($ch)) {
            throw new TwistException(curl_error($ch), $info['http_code']);
        }
        if ($response === '') {
            throw new TwistException('Empty response.', $info['http_code']);
        }
        if ($response === null) {
            throw new TwistException('Failed to receive response.', $info['http_code']);
        }
        if (stripos($info['content_type'], 'image/') === 0) {
            return new TwistImage($info['content_type'], $response);
        }
        if (
            null  !== $obj = json_decode($response) or
            false !== $obj = json_decode(json_encode(@simplexml_load_string($response)))
        ) {
            if (isset($obj->error)) {       
                throw new TwistException($obj->error, $info['http_code']);
            }
            if (isset($obj->errors)) {
                if (is_string($obj->errors)) {
                    throw new TwistException($obj->errors, $info['http_code']);
                } else {
                    $messages = array();
                    foreach ($obj->errors as $error) {
                        $messages[] = $error->message;
                    }
                    throw new TwistException(implode("\n", $messages), $info['http_code']);
                }
            }
            return $obj;
        }
        parse_str($response, $obj);
        $obj = (object)$obj;
        if (isset($obj->oauth_token, $obj->oauth_token_secret)) {
            return $obj;
        }
        if (preg_match("@Reason:\n<pre>([^<]++)</pre>@", $response, $matches)) {
            throw new TwistException(trim($matches[1]), $info['http_code']);
        }
        if (strip_tags($response) === $response) {
            throw new TwistException(trim($response), $info['http_code']);
        }
        throw new TwistException('Malformed response detected: ' . $response, $info['http_code']);
    }
    
    /**
     * Parse endpoint url.
     * 
     * @param string $endpoint
     * @return string URL
     */
    private static function url($endpoint) {
        static $regex;
        static $callback;
        static $list;
        if (!$regex) {
            $regex = implode('|', array(
                'conversation/show/(\d++)',
                'geo/id/(\d++)',
                'saved_searches/destroy/(\d++)',
                'saved_searches/show/(\d++)',
                'scheduled/show/(\d++)',
                'statuses/(\d++)/activity/summary',
                'statuses/destroy/(\d++)',
                'statuses/retweet/(\d++)',
                'statuses/retweets/(\d++)',
                'statuses/show/(\d++)',
                'users/suggestions/([\w-]++)',
                'users/suggestions/([\w-]++)/members',
                'i/statuses/(\d++)/activity/summary',
            ));
            $regex = '@\A(?:' . $regex . ')\z@';
            $callback = function ($matches) {
                static $list = array(
                    'https://api.twitter.com/1.1/conversation/show/$1.json',
                    'https://api.twitter.com/1.1/geo/id/$1.json',
                    'https://api.twitter.com/1.1/saved_searches/destroy/$1.json',
                    'https://api.twitter.com/1.1/saved_searches/show/$1.json',
                    'https://api.twitter.com/1.1/scheduled/show/$1.json',
                    'https://api.twitter.com/1.1/statuses/$1/activity/summary.json',
                    'https://api.twitter.com/1.1/statuses/destroy/$1.json',
                    'https://api.twitter.com/1.1/statuses/retweet/$1.json',
                    'https://api.twitter.com/1.1/statuses/retweets/$1.json',
                    'https://api.twitter.com/1.1/statuses/show/$1.json',
                    'https://api.twitter.com/1.1/users/suggestions/$1.json',
                    'https://api.twitter.com/1.1/users/suggestions/$1/members.json',
                    'https://api.twitter.com/i/statuses/$1/activity/summary.json',
                );
                end($matches);
                return str_replace('$1', current($matches), $list[key($matches) - 1]);
            };
            $list = array(
                'urls/count' =>
                    'http://urls.api.twitter.com/1/urls/count.json',
                'account/login_verification_request' =>
                    'https://api.twitter.com/1.1/account/login_verification_request.json',
                'account/login_verification_enrollment__post' =>
                    'https://api.twitter.com/1.1/account/login_verification_request__post.json',
                'account/remove_profile_banner' =>
                    'https://api.twitter.com/1.1/account/remove_profile_banner.json',
                'account/settings' =>
                    'https://api.twitter.com/1.1/account/settings.json',
                'account/update_delivery_device' =>
                    'https://api.twitter.com/1.1/account/update_delivery_device.json',
                'account/update_profile' =>
                    'https://api.twitter.com/1.1/account/update_profile.json',
                'account/update_profile_background_image' =>
                    'https://api.twitter.com/1.1/account/update_profile_background_image.json',
                'account/update_profile_banner' =>
                    'https://api.twitter.com/1.1/account/update_profile_banner.json',
                'account/update_profile_colors' =>
                    'https://api.twitter.com/1.1/account/update_profile_colors.json',
                'account/update_profile_image' =>
                    'https://api.twitter.com/1.1/account/update_profile_image.json',
                'account/verification' =>
                    'https://api.twitter.com/1.1/account/verification.json',
                'account/verify_credentials' =>
                    'https://api.twitter.com/1.1/account/verify_credentials.json',
                'activity/about_me' =>
                    'https://api.twitter.com/1.1/activity/about_me.json',
                'activity/by_friends' =>
                    'https://api.twitter.com/1.1/activity/by_friends.json',
                'application/rate_limit_status' =>
                    'https://api.twitter.com/1.1/application/rate_limit_status.json',
                'beta/timelines/custom/add' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/add.json',
                'beta/timelines/custom/create' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/create.json',
                'beta/timelines/custom/destroy' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/destroy.json',
                'beta/timelines/custom/list' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/list.json',
                'beta/timelines/custom/remove' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/remove.json',
                'beta/timelines/custom/show' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/show.json',
                'beta/timelines/custom/update' =>
                    'https://api.twitter.com/1.1/beta/timelines/custom/update.json',
                'beta/timelines/timeline' =>
                    'https://api.twitter.com/1.1/beta/timelines/timeline.json',
                'blocks/create' =>
                    'https://api.twitter.com/1.1/blocks/create.json',
                'blocks/destroy' =>
                    'https://api.twitter.com/1.1/blocks/destroy.json',
                'blocks/ids' =>
                    'https://api.twitter.com/1.1/blocks/ids.json',
                'blocks/list' =>
                    'https://api.twitter.com/1.1/blocks/list.json',
                'device/token' =>
                    'https://api.twitter.com/1.1/device/token.json',
                'device_following/ids' =>
                    'https://api.twitter.com/1.1/device_following/ids.json',
                'device_following/list' =>
                    'https://api.twitter.com/1.1/device_following/list.json',
                'direct_messages' =>
                    'https://api.twitter.com/1.1/direct_messages.json',
                'direct_messages/destroy' =>
                    'https://api.twitter.com/1.1/direct_messages/destroy.json',
                'direct_messages/new' =>
                    'https://api.twitter.com/1.1/direct_messages/new.json',
                'direct_messages/read' =>
                    'https://api.twitter.com/1.1/direct_messages/read.json',
                'direct_messages/sent' =>
                    'https://api.twitter.com/1.1/direct_messages/sent.json',
                'direct_messages/show' =>
                    'https://api.twitter.com/1.1/direct_messages/show.json',
                'discover/highlight' =>
                    'https://api.twitter.com/1.1/discover/highlight.json',
                'discover/home' =>
                    'https://api.twitter.com/1.1/discover/home.json',
                'discover/nearby' =>
                    'https://api.twitter.com/1.1/discover/nearby.json',
                'discover/universal' =>
                    'https://api.twitter.com/1.1/discover/universal.json',
                'favorites/create' =>
                    'https://api.twitter.com/1.1/favorites/create.json',
                'favorites/destroy' =>
                    'https://api.twitter.com/1.1/favorites/destroy.json',
                'favorites/list' =>
                    'https://api.twitter.com/1.1/favorites/list.json',
                'followers/ids' =>
                    'https://api.twitter.com/1.1/followers/ids.json',
                'followers/list' =>
                    'https://api.twitter.com/1.1/followers/list.json',
                'friends/ids' =>
                    'https://api.twitter.com/1.1/friends/ids.json',
                'friends/list' =>
                    'https://api.twitter.com/1.1/friends/list.json',
                'friendships/create' =>
                    'https://api.twitter.com/1.1/friendships/create.json',
                'friendships/destroy' =>
                    'https://api.twitter.com/1.1/friendships/destroy.json',
                'friendships/incoming' =>
                    'https://api.twitter.com/1.1/friendships/incoming.json',
                'friendships/lookup' =>
                    'https://api.twitter.com/1.1/friendships/lookup.json',
                'friendships/no_retweets/ids' =>
                    'https://api.twitter.com/1.1/friendships/no_retweets/ids.json',
                'friendships/outgoing' =>
                    'https://api.twitter.com/1.1/friendships/outgoing.json',
                'friendships/show' =>
                    'https://api.twitter.com/1.1/friendships/show.json',
                'friendships/update' =>
                    'https://api.twitter.com/1.1/friendships/update.json',
                'geo/place' =>
                    'https://api.twitter.com/1.1/geo/place.json',
                'geo/reverse_geocode' =>
                    'https://api.twitter.com/1.1/geo/reverse_geocode.json',
                'geo/search' =>
                    'https://api.twitter.com/1.1/geo/search.json',
                'geo/similar_places' =>
                    'https://api.twitter.com/1.1/geo/similar_places.json',
                'help/configuration' =>
                    'https://api.twitter.com/1.1/help/configuration.json',
                'help/experiments' =>
                    'https://api.twitter.com/1.1/help/experiments.json',
                'help/languages' =>
                    'https://api.twitter.com/1.1/help/languages.json',
                'help/privacy' =>
                    'https://api.twitter.com/1.1/help/privacy.json',
                'help/tos' =>
                    'https://api.twitter.com/1.1/help/tos.json',
                'lists/create' =>
                    'https://api.twitter.com/1.1/lists/create.json',
                'lists/destroy' =>
                    'https://api.twitter.com/1.1/lists/destroy.json',
                'lists/list' =>
                    'https://api.twitter.com/1.1/lists/list.json',
                'lists/members' =>
                    'https://api.twitter.com/1.1/lists/members.json',
                'lists/members/create' =>
                    'https://api.twitter.com/1.1/lists/members/create.json',
                'lists/members/create_all' =>
                    'https://api.twitter.com/1.1/lists/members/create_all.json',
                'lists/members/destroy' =>
                    'https://api.twitter.com/1.1/lists/members/destroy.json',
                'lists/members/destroy_all' =>
                    'https://api.twitter.com/1.1/lists/members/destroy_all.json',
                'lists/members/show' =>
                    'https://api.twitter.com/1.1/lists/members/show.json',
                'lists/memberships' =>
                    'https://api.twitter.com/1.1/lists/memberships.json',
                'lists/ownerships' =>
                    'https://api.twitter.com/1.1/lists/ownerships.json',
                'lists/show' =>
                    'https://api.twitter.com/1.1/lists/show.json',
                'lists/statuses' =>
                    'https://api.twitter.com/1.1/lists/statuses.json',
                'lists/subscribers' =>
                    'https://api.twitter.com/1.1/lists/subscribers.json',
                'lists/subscribers/create' =>
                    'https://api.twitter.com/1.1/lists/subscribers/create.json',
                'lists/subscribers/destroy' =>
                    'https://api.twitter.com/1.1/lists/subscribers/destroy.json',
                'lists/subscribers/show' =>
                    'https://api.twitter.com/1.1/lists/subscribers/show.json',
                'lists/subscriptions' =>
                    'https://api.twitter.com/1.1/lists/subscriptions.json',
                'lists/update' =>
                    'https://api.twitter.com/1.1/lists/update.json',
                'mutes/users/create' =>
                    'https://api.twitter.com/1.1/mutes/users/create.json',
                'mutes/users/destroy' =>
                    'https://api.twitter.com/1.1/mutes/users/destroy.json',
                'mutes/users/ids' =>
                    'https://api.twitter.com/1.1/mutes/users/ids.json',
                'mutes/users/list' =>
                    'https://api.twitter.com/1.1/mutes/users/list.json',
                'prompts/suggest' =>
                    'https://api.twitter.com/1.1/prompts/suggest.json',
                'account/push_destinations/device' =>
                    'https://api.twitter.com/1.1/push_destinations/device.json',
                'push_destinations/enable_login_verification' =>
                    'https://api.twitter.com/1.1/push_destinations/enable_login_verification.json',
                'saved_searches/create' =>
                    'https://api.twitter.com/1.1/saved_searches/create.json',
                'saved_searches/list' =>
                    'https://api.twitter.com/1.1/saved_searches/list.json',
                'scheduled/list' =>
                    'https://api.twitter.com/1.1/scheduled/list.json',
                'scheduled/lookup' =>
                    'https://api.twitter.com/1.1/scheduled/lookup.json',
                'search/tweets' =>
                    'https://api.twitter.com/1.1/search/tweets.json',
                'search/typeahead' =>
                    'https://api.twitter.com/1.1/search/typeahead.json',
                'search/universal' =>
                    'https://api.twitter.com/1.1/search/universal.json',
                'statuses/home_timeline' =>
                    'https://api.twitter.com/1.1/statuses/home_timeline.json',
                'statuses/lookup' =>
                    'https://api.twitter.com/1.1/statuses/lookup.json',
                'statuses/media_timeline' =>
                    'https://api.twitter.com/1.1/statuses/media_timeline.json',
                'statuses/mentions_timeline' =>
                    'https://api.twitter.com/1.1/statuses/mentions_timeline.json',
                'statuses/oembed' =>
                    'https://api.twitter.com/1.1/statuses/oembed.json',
                'statuses/retweeters/ids' =>
                    'https://api.twitter.com/1.1/statuses/retweeters/ids.json',
                'statuses/retweets_of_me' =>
                    'https://api.twitter.com/1.1/statuses/retweets_of_me.json',
                'statuses/update' =>
                    'https://api.twitter.com/1.1/statuses/update.json',
                'statuses/update_with_media' =>
                    'https://api.twitter.com/1.1/statuses/update_with_media.json',
                'statuses/user_timeline' =>
                    'https://api.twitter.com/1.1/statuses/user_timeline.json',
                'timeline/home' =>
                    'https://api.twitter.com/1.1/timeline/home.json',
                'translations/show' =>
                    'https://api.twitter.com/1.1/translations/show.json',
                'trends/available' =>
                    'https://api.twitter.com/1.1/trends/available.json',
                'trends/closest' =>
                    'https://api.twitter.com/1.1/trends/closest.json',
                'trends/personalized' =>
                    'https://api.twitter.com/1.1/trends/personalized.json',
                'trends/place' =>
                    'https://api.twitter.com/1.1/trends/place.json',
                'trends/timeline' =>
                    'https://api.twitter.com/1.1/trends/timeline.json',
                'users/contributees' =>
                    'https://api.twitter.com/1.1/users/contributees.json',
                'users/contributors' =>
                    'https://api.twitter.com/1.1/users/contributors.json',
                'users/lookup' =>
                    'https://api.twitter.com/1.1/users/lookup.json',
                'users/profile_banner' =>
                    'https://api.twitter.com/1.1/users/profile_banner.json',
                'users/recommendations' =>
                    'https://api.twitter.com/1.1/users/recommendations.json',
                'users/report_spam' =>
                    'https://api.twitter.com/1.1/users/report_spam.json',
                'users/reverse_lookup' =>
                    'https://api.twitter.com/1.1/users/reverse_lookup.json',
                'users/search' =>
                    'https://api.twitter.com/1.1/users/search.json',
                'users/show' =>
                    'https://api.twitter.com/1.1/users/show.json',
                'users/suggestions' =>
                    'https://api.twitter.com/1.1/users/suggestions.json',
                'users/wipe_addressbook' =>
                    'https://api.twitter.com/1.1/users/wipe_addressbook.json',
                'i/activity/about_me' =>
                    'https://api.twitter.com/i/activity/about_me.json',
                'i/activity/by_friends' =>
                    'https://api.twitter.com/i/activity/by_friends.json',
                'site' =>
                    'https://sitestream.twitter.com/1.1/site.json',
                'statuses/filter' =>
                    'https://stream.twitter.com/1.1/statuses/filter.json',
                'statuses/firehose' =>
                    'https://stream.twitter.com/1.1/statuses/firehose.json',
                'statuses/sample' =>
                    'https://stream.twitter.com/1.1/statuses/sample.json',
                'media/upload' =>
                    'https://upload.twitter.com/1.1/media/upload.json',
                'user' =>
                    'https://userstream.twitter.com/1.1/user.json',
            );
        }
        if (isset($list[$endpoint])) {
            return $list[$endpoint];
        }
        return preg_replace_callback($regex, $callback, $endpoint, 1, $count);
    }
    
    /**
     * Return whether the cURL connection is aborted by ourselves.
     * Errno will always be 0 on curl_multi SAPI, so we have to judge by Error.
     * 
     * @param resource $ch
     * @return bool
     */
    private static function isWriteFailure($ch) {
        return stripos(curl_error($ch), 'Failed writing body') !== false;
    }
    
    /**
     * Set cURL options for authenticity_token.
     *
     * @param resource $ch
     * @param TwistOAuth $to
     */
    private static function curlSetOptForAuthenticityToken($ch, $to) {
        curl_setopt($ch, CURLOPT_URL, $to->getAuthorizeUrl(true));
    }
    
    /**
     * Set cURL options for oauth_verifier.
     *
     * @param resource   $ch
     * @param TwistOAuth $to
     * @param string     $authenticity_token
     * @param string     $username
     * @param string     $password
     */
    private static function curlSetOptForVerifier($ch, $to, $authenticity_token, $username, $password) {
        $params = array(
            'session[username_or_email]' => $username,
            'session[password]'          => $password,
            'authenticity_token'         => $authenticity_token,
        );
        curl_setopt_array($ch, array(
            CURLOPT_URL        => $to->getAuthorizeUrl(true),
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
    }
    
    /**
     * Execute multiple cURL requests actually.
     * 
     * @param array $curls
     * @param bool $is_streaming
     * @param bool $throw_in_process
     * @return array
     * @throws TwistException
     */
    private static function curlMultiExecAction(array $curls, $is_streaming, $throw_in_process) {
        $mh = curl_multi_init();
        $chs       = array(); // cURL resources for API connection
        $wchs      = array(); // cURL resources waiting other connection closed
        $responses = array(); // responses
        if (!$curls) {
            return array();
        }
        $c = 0;
        foreach ($curls as $i => $ch) {
            $chs[$i]       = self::validateCurl("\$curls[$i]", $ch);
            $responses[$i] = new TwistException('Failed to receive event.');
            if (++$c <= self::CURLOPT_MAXCONNECTS) {
                curl_multi_add_handle($mh, $chs[$i]);
            } else {
                $wchs[] = $chs[$i];
            }
        }
        // start requests
        while (CURLM_CALL_MULTI_PERFORM === $stat = curl_multi_exec($mh, $running));
        if (!$running || $stat !== CURLM_OK) {
            throw new TwistException('Failed to start multiple requests.');
        }
        // wait cURL events
        do switch (curl_multi_select($mh, self::CURLOPT_TIMEOUT)) {
            case -1: // failed to select for various reason
                $add = false;
                // wait a bit, update $running flag, retry and continue
                usleep(10);
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
                continue 2;
            case 0:
                // timeout!
                if ($throw_in_process) {
                    throw new TwistException('Timeout.');
                }
                break 2;
            default:
                $add = false;
                // update $running flag
                while (curl_multi_exec($mh, $running) === CURLM_CALL_MULTI_PERFORM);
                // dequeue array of cURL which finished receiving data
                do if ($raised = curl_multi_info_read($mh, $remains)) {
                    // search offset corresponds to the resource
                    $i = array_search($raised['handle'], $chs, true);
                    if ($is_streaming) {
                        if (!self::isWriteFailure($raised['handle'])) {
                            $info = curl_getinfo($raised['handle']);
                            throw new TwistException('(' . $i . ') Streaming stopped unexpectedly.', $info['http_code']);
                        }
                    } else {
                        try {
                            $info = curl_getinfo($raised['handle']);
                            $responses[$i] = self::decode($raised['handle'], curl_multi_getcontent($raised['handle']));
                        } catch (TwistException $e) {
                            $responses[$i] = $e;
                            if ($throw_in_process) {
                                $responses[$i]->__construct('(' . $i . ') ' . $responses[$i]->getMessage(), $responses[$i]->getCode());
                                throw $responses[$i];
                            }
                        }
                    }
                    curl_multi_remove_handle($mh, $raised['handle']);
                    if ($wch = array_shift($wchs)) {
                        curl_multi_add_handle($mh, $wch);
                        $add = true;
                    }
                } while ($remains);
        } while ($running || $add); // continue if still running
        return $responses;
    }
    
    /**
     * Force callable function.
     * 
     * @param string $name
     * @param mixed  $callback
     * @return callable filtered callback
     */
    private static function validateCallback($name, $callback) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException("The value of $name must be a valid callback.");
        }
        return $callback;
    }
    
    /**
     * Force parameters 1-demensional array or query string.
     * 
     * @param string $name
     * @param mixed  $params
     * @return array filterd parameters
     */
    private static function validateParams($name, $params) {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if ($value === null) {
                    unset($params[$key]);
                    continue;
                }
                $params[$key] = self::validateString("\$name[$key]", $value);
            }
            return $params;
        }
        if (false === $params = filter_var($params)) {
            throw new InvalidArgumentException("The value of $name must be a 1-demensional array or query string.");
        }
        $tmp = array();
        if ('' !== $params = trim($params)) {
            foreach (explode('&', $params) as $pair) {
                list($key, $value) = explode('=', $pair, 2) + array(1 => '');
                $tmp[urldecode($key)] = urldecode($value);
            }
        }
        return $tmp;
    }
    
    /**
     * Force valid cURL resource.
     * 
     * @param string $name
     * @param mixed  $ch
     * @return resource cURL
     */
    private static function validateCurl($name, $ch) {
        switch (true) {
            case !is_resource($ch):
            case stripos($type = get_resource_type($ch), 'curl') === false:
            case stripos($type, 'multi') !== false:
                throw new InvalidArgumentException("The value of $name must be a valid cURL resource.");
        }
        return $ch;
    }
    
    /**
     * Force string.
     * 
     * @param mixed $name
     * @param mixed $str
     * @return string filtered string
     */
    private static function validateString($name, $str) {
        if (false === $str = filter_var($str)) {
            throw new InvalidArgumentException("The value of $name must be stringable.");
        }
        return $str;
    }
    
    /**
     * Safe file_get_contents().
     * 
     * @param mixed  $name
     * @param string $path
     */
    private static function safeGetContents($name, $path) {
        if (false === $data = @file_get_contents($path)) {
            throw new InvalidArgumentException("The file path of $name must be valid.");
        }
        return $data;
    }
    
    /**
     * Solve parameters with prefix "@" "#".
     * 
     * @param array $params
     * @return stdClass an object contains "paramData", "paramIsFile"
     */
    private static function getParamObject(array $params) {
        $obj              = new stdClass;
        $obj->paramData   = array();
        $obj->paramIsFile = array();
        foreach ($params as $key => $value) {
            if (strpos($key, '@') === 0) {
                $obj->paramData[substr($key, 1)]   = self::safeGetContents($key, $value);
                $obj->paramIsFile[substr($key, 1)] = true;
            } elseif (strpos($key, '#') === 0) {
                $obj->paramData[substr($key, 1)]   = $value;
                $obj->paramIsFile[substr($key, 1)] = true;
            } else {
                $obj->paramData[$key]   = $value;
                $obj->paramIsFile[$key] = false;
            }
        }
        return $obj;
    }
    
    /**
     * Parse authenticity_token.
     * 
     * @param resource $ch
     * @param string $response
     * @return string authenticity_token
     * @throws TwistException
     */
    private static function parseAuthenticityToken($ch, $response) {
        static $pattern = '@<input name="authenticity_token" type="hidden" value="([^"]++)" />@';
        if (!preg_match($pattern, $response, $matches)) {
            $info = curl_getinfo($ch);
            throw new TwistException('Failed to get authenticity_token.', $info['http_code']);
        }
        return $matches[1];
    }
    
    /**
     * Parse oauth_verifier.
     * 
     * @param resource $ch cURL resource
     * @param string $response
     * @return string oauth_verifier
     * @throws TwistException
     */
    private static function parseVerifier($ch, $response) {
        static $pattern = '@<code>([^<]++)</code>@';
        if (!preg_match($pattern, $response, $matches)) {
            $info = curl_getinfo($ch);
            throw new TwistException('Wrong username or password.', $info['http_code']);
        }
        return $matches[1];
    }
    
    /**
     * Prepare headers for authorization.
     *
     * @param string $url endpoint URL
     * @param string $method GET or POST
     * @param array &$params 1-demensional array
     * @param int $flags self::MODE_*
     * @return array headers
     */
    private function getAuthorization($url, $method, &$params, $flags) {
        $oauth = array(
            'oauth_consumer_key'     => $this->ck,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0a',
            'oauth_nonce'            => md5(mt_rand()),
            'oauth_token'            => $this->ot,
        );
        $key = array($this->cs, $this->os);
        if ($flags & self::MODE_REQUEST_TOKEN) {
            $key[1] = '';
            if (isset($params['oauth_callback'])) {
                $oauth['oauth_callback'] = $params['oauth_callback'];
            }
            unset($oauth['oauth_token'], $oauth['oauth_callback']);
        }
        if ($flags & self::MODE_ACCESS_TOKEN) {
            $oauth['oauth_verifier'] = $params['oauth_verifier'];
            unset($params['oauth_verifier']);
        }
        $base = $oauth + $params;
        uksort($base, 'strnatcmp');
        $oauth['oauth_signature'] = base64_encode(hash_hmac(
            'sha1',
            implode('&', array_map('rawurlencode', array(
                $method,
                $url,
                str_replace(
                    array('+', '%7E'), 
                    array('%20', '~'), 
                    http_build_query($base, '', '&')
                ),
            ))),
            implode('&', array_map('rawurlencode', $key)),
            true
        ));
        $tmp = array();
        foreach ($oauth as $key => $value) {
            $tmp[] = urlencode($key) . '="' . urlencode($value) . '"'; 
        }
        return array(
            'Authorization: OAuth ' . implode(', ', $tmp)
        );
    }
    
    /**
     * Prepare headers for OAuth Echo.
     *
     * @return array headers
     */
    private function getOAuthEcho() {
        $url     = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $params  = array();
        $headers = $this->getAuthorization($url, 'GET', $params, 0);
        return array(
            'X-Auth-Service-Provider: ' . $url,
            'X-Verify-Credentials-Authorization: OAuth realm="http://api.twitter.com/", ' . substr($headers[0], 21),
        );
    }
    
    /**
     * Prepare cURL resource for GET request actually.
     *
     * @param string $url
     * @param mixed  $params
     * @param bool   $out
     * @param string $proxy
     * @return resource cURL
     * @throws TwistException
     */
    private function curlGetAction($url, $params, $out, $proxy) {
        $url    = self::url(self::validateString('$url', $url));
        $obj    = self::getParamObject(self::validateParams('$params', $params));
        $proxy  = self::validateString('$proxy', $proxy);
        $params = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'GET', $params, 0),
            CURLOPT_URL        => $url . '?' . http_build_query($params, '', '&'),
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for POST request actually.
     *
     * @param string $url
     * @param mixed  $params
     * @param bool   $out
     * @param string $proxy
     * @return resource cURL
     * @throws TwistException
     */
    private function curlPostAction($url, $params, $out, $proxy) {
        $url    = self::url(self::validateString('$url', $url));
        $obj    = self::getParamObject(self::validateParams('$params', $params));
        $proxy  = self::validateString('$proxy', $proxy);
        $params = array();
        foreach ($obj->paramData as $key => $value) {
            $params[$key] =
                $obj->paramIsFile[$key] ?
                base64_encode($value) :
                $value
            ;
        }
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'POST', $params, 0),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
    /**
     * Prepare cURL resource for GET request actually.
     *
     * @param string $url
     * @param mixed  $params
     * @param bool   $out
     * @param string $proxy
     * @return resource cURL
     */
    private function curlPostMultipartAction($url, $params, $out, $proxy) {
        static $disallow = array("\0", "\"", "\r", "\n");
        $url    = self::url(self::validateString('$url', $url));
        $obj    = self::getParamObject(self::validateParams('$params', $params));
        $proxy  = self::validateString('$proxy', $proxy);
        $body = array();
        foreach ($obj->paramData as $key => $value) {
            if ($obj->paramIsFile[$key]) {
                $body[] = implode("\r\n", array(
                    sprintf(
                        'Content-Disposition: form-data; name="%s"; filename="%s"',
                        str_replace($disallow, '_', $key),
                        md5(mt_rand() . microtime())
                    ),
                    'Content-Type: application/octet-stream',
                    '',
                    $value,
                ));
            } else {
                $body[] = implode("\r\n", array(
                    sprintf(
                        'Content-Disposition: form-data; name="%s"',
                        str_replace($disallow, '_', $key)
                    ),
                    '',
                    $value,
                ));
            }
        }
        do {
            $boundary = '---------------------' . md5(mt_rand() . microtime());
        } while (preg_grep('/' . $boundary . '/', $body));
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });
        $body[] = '--' . $boundary . '--';
        $body[] = '';
        $params = array();
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => array_merge(
                $out ? $this->getOAuthEcho() : $this->getAuthorization($url, 'POST', $params, 0),
                array('Content-Type: multipart/form-data; boundary=' . $boundary)
            ),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_POST       => true,
        ));
        return $ch;
    }
    
}

/**
 * Image class.
 * Instances are internally genereted.
 */
final class TwistImage {
    
    /**
     * @property-read string $type
     * @property-read string $data
     */
    private $type;
    private $data;
    
    /**
     * Constructor.
     * 
     * @param string $type Content-Type
     * @param string $data Binary data
     */
    public function __construct($type, $data) {
        $this->type = filter_var($type);
        $this->data = filter_var($data);
    }
    
    /**
     * Getter for private properties.
     *
     * @name string property name
     * @return string
     */
    public function __get($name) {
        $name = filter_var($name);
        if (!property_exists($this, $name)) {
            throw new OutOfRangeException('Invalid property: ' . $name);
        }
        return $this->$name;
    }
    
    /**
     * Issetter for private properties.
     *
     * @name string $name
     * @return bool
     */
    public function __isset($name) {
        return property_exists($this, filter_var($name));
    }
    
    /**
     * Make format of Data URI.
     *
     * @return string
     */
    public function getDataUri() {
        return sprintf('data:%s;base64,%s', $this->type, base64_encode($this->data));
    }
    
}

/**
 * Exception related to Twitter.
 */
final class TwistException extends RuntimeException { }