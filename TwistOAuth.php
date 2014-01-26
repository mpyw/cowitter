<?php

class TwistException extends RuntimeException {
    
    private $request;
    private $consumer;
    
    public function __construct(
        $message, TwistRequest $request,
        TwistConsumer $consumer, $code = 0
    ) {
        $this->code     = $code;
        $this->message  = $message;
        $this->request  = $request;
        $this->consumer = $consumer;
    }
    
    public function getRequest() {
        return $this->request;
    }
    
    public function getConsuemr() {
        return $this->consumer;
    }
    
}

abstract class TwistBase {
    
    protected static function asString($input) {
        switch (true) {
            case is_array($input):
            case is_object($input) and !method_exists($input, '__toString'):
                return '';
            default:
                return (string)$input;
        }
    }
    
    protected static function asArray($input) {
        $output = array();
        foreach ((array)$input as $key => $value) {
            $output[$key] = self::asString($value);
        }
        return $output;
    }
    
}

class TwistConsumer extends TwistBase {
    
    private $consumerKey        = '';
    private $consumerSecret     = '';
    private $requestToken       = '';
    private $requestTokenSecret = '';
    private $accessToken        = '';
    private $accessTokenSecret  = '';
    private $screenName         = '';
    private $userId             = '';
    private $history            = array();
    private $cookies            = array();
    
    public function __construct(
        $consumerKey = '', $consumerSecret    = '',
        $accessToken = '', $accessTokenSecret = ''
    ) {
        foreach (get_defined_vars() as $name => $value) {
            $this->__set($name, $value);
        }
    }
    
    final public function __get($name) {
        $this->checkExistence($name);
        return $this->$name;
    }
    
    final public function __set($name, $value) {
        $this->checkExistence($name);
        return $this->$name = self::asString($value);
    }
    
    final public function setHistory($name) {
        $name = self::asString($name);
        $this->history += array($name => 0);
        ++$this->history[$name];
    }
    
    final public function setCookie($key, $value) {
        $this->cookies[self::asString($key)] = self::asString($value);
    }
    
    final public function getAuthorizeUrl($force_login = false) {
        return $this->getAuthUrl('authorize', $force_login);
    }
    
    final public function getAuthenticateUrl($force_login = false) {
        return $this->getAuthUrl('authenticate', $force_login);
    }
    
    private function checkExistence($name) {
        switch (true) {
            case !property_exists($this, $name = self::asString($name)):
            case is_array($this->$name):
                throw new OutOfRangeException("invalid property name \"{$name}\"");
        }
    }
    
    private function getAuthUrl($mode, $force_login) {
        $url = "https://api.twitter.com/oauth/{$mode}";
        $params['oauth_token'] = $this->request_token;
        if ($force_login) {
            $params['force_login'] = '1';
        }
        return $url . '?' . http_build_query($params, '', '&');
    }
    
}

class TwistRequest extends TwistBase {
    
    private $host      = 'api.twitter.com';
    private $endpoint  = '/1.1/account/verify_credentials';
    private $method    = 'GET';
    private $params    = array();
    private $info      = array();
    private $multipart = false;
    private $streaming = false;
    private $fp        = false;
    private $consumer  = false;
    
    final public static function get($endpoint, $params = array()) {
        return new self($endpoint, 'GET', $params, false);
    }
    
    final public static function post($endpoint, $params = array()) {
        return new self($endpoint, 'POST', $params, false);
    }
    
    final public static function postMedia($params = array()) {
        return new self('statuses/update_with_media', 'POST', $params, true);
    }
    
    final public function __clone() {
        $this->fp = $this->consumer = false;
        $this->info = array();
    }
    
    final public function close() {
        fclose($this->getConnection());
        $this->fp = $this->consumer = false;
        $this->info = array();
    }
    
    final public function isEof() {
        return feof($this->getConnection());
    }
    
    final public function isStreaming() {
        return $this->streaming;
    }
    
    final public function getConnection() {
        if (!is_resource($this->fp)) {
            throw new BadMethodCallException('connection is not established');
        }
        return $this->fp;
    }
    
    final public function start(TwistConsumer $consumer) {
        if (is_resource($this->fp)) {
            throw new BadMethodCallException('request is already started');
        }
        $fp   = false;
        $info = array();
        if (!$fp = @fsockopen("ssl://{$this->host}", 443)) {
            throw new TwistException("failed to connect to {$this->host}", $this, $this->consumer);
        } elseif (!fwrite($fp, $this->buildHeaders($consumer))) {
            throw new TwistException('failed to send request', $this, $this->consumer);
        }
        list(, $info['code']) = explode(' ', fgets($fp), 3) + array(1 => '0');
        while (false !== $line = fgets($fp) and $line !== "\r\n") {
            $info += $this->parseHeader($consumer, $line);
        }
        $this->fp       = $fp;
        $this->info     = $info;
        $this->consumer = $consumer;
        $consumer->setHistory($this->endpoint);
        return $this;
    }
    
    final public function getContent($throw = false) {
        $fp = $this->getConnection();
        switch (true) {
            case isset($this->info['content-length']):
                $content = self::fread($fp, $this->info['content-length']);
                break;
            case !isset($this->info['transfer-encoding']):
            case $this->info['transfer-encoding'] !== 'chunked':
                $content = (string)stream_get_contents($fp);
                break;
            default:
                $content = ($bytes = hexdec(fgets($fp))) ? self::fread($fp, $bytes) : '';
                fgets($fp);
        }
        switch (true) {
            case $content === '':
            case !isset($this->info['content-encoding']):
            case $this->info['content-encoding'] !== 'gzip':
                break;
            default:
                $content = @gzinflate(substr($content, 10, -8)); 
        }
        switch (true) {
            case null !== $object = json_decode($content):
            case false !== $object = json_decode(json_encode(@simplexml_load_string($content))):
            case parse_str($content, $object):
            case !$object = (object)$object:
            case isset($object->oauth_token, $object->oauth_token_secret):
                break;
            default:
                $object = (object)array('errors' =>
                    array(
                        (object)array(
                            'message' => 'failed to parse response',
                            'code'    => 0,
                        )
                    )
                );
        }
        if (isset($object->screen_name)) {
            $this->consumer->screenName = $object->screen_name;
        }
        if (isset($object->user_id)) {
            $this->consumer->userId = $object->userId;
        }
        if (isset($object->oauth_token)) {
            if ($this->endpoint === '/oauth/request_token') {
                $this->consumer->requestToken = $object->oauth_token;
            }
            if ($this->endpoint === '/oauth/access_token') {
                $this->consumer->accessToken = $object->oauth_token;
            }
        }
        if (isset($object->oauth_token_secret)) {
            if ($this->endpoint === '/oauth/request_token') {
                $this->consumer->requestTokenSecret = $object->oauth_token_secret;
            }
            if ($this->endpoint === '/oauth/access_token') {
                $this->consumer->accessTokenSecret = $object->oauth_token_secret;
            }
        }
        if (isset($this->info['x-twitter-new-account-oauth-access-token'])) {
            $object->oauth_token = $this->info['x-twitter-new-account-oauth-access-token'];
        }
        if (isset($this->info['x-twitter-new-account-oauth-secret'])) {
            $object->oauth_token_secret = $this->info['x-twitter-new-account-oauth-secret'];
        }
        if (isset($object->errors)) {
            if (is_string($object->errors)) {
                $object->errors = array((object)array(
                    'message' => $object->errors,
                    'code' => (int)$this->info['code'],
                ));
            } else {
                foreach ($object->errors as $key => $value) {
                    $object->errors[$key]->code = (int)$this->info['code'];
                }
            }
        } elseif (isset($object->error)) {
            $object->errors = array((object)array(
                'message' => $object->error,
                'code' => (int)$this->info['code'],
            ));
        }
        if ($throw and isset($object->errors[0])) {
            throw new TwistException(
                $object->errors[0]->message,
                $this,
                $this->consumer,
                $object->errors[0]->code
            );
        }
        return $object;
    }
    
    private function solveParams(array $params, $base64 = true) {
        $new = array();
        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === false) {
                $value = '0';
            }
            $value = self::asString($value);
            if (strpos($key, '@') === 0) {
                if (!is_readable($value) or !is_file($value)) {
                    throw new InvalidArgumentException("file \"{$value}\" not found");
                }
                $key = substr($key, 1);
                $value = file_get_contents($value);
                if ($base64) {
                    $value = base64_encode($value);
                }
            }
            $new[$key] = $value;
        }
        return $new;
    }
    
    private static function fread($fp, $length) {
        $buffer = '';
        do {
            $buffer .= $tmp = fread($fp, (int)$length);
            $length -= strlen($tmp);
        } while ($length and $tmp !== false and !feof($fp));
        return $buffer;
    }
    
    private static function buildMultipartContent(array $params, $boundary) {
        $lines = array();
        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === false) {
                $value = '0';
            }
            if ($key === 'media[]') {
                $filename = md5(mt_rand() . microtime());
                $disposition = "form-data; name=\"{$key}\"; filename=\"{$filename}\"";
            } else {
                $disposition = "form-data; name=\"{$key}\"";
            }
            array_push(
                $lines,
                "--{$boundary}",
                "Content-Disposition: {$disposition}",
                "Content-Type: application/octet-stream",
                "",
                $value
            );
        }
        $lines[] = "--{$boundary}--";
        return implode("\r\n", $lines);
    }
    
    private static function buildQuery(array $params, $pair = true) {
        $new = array();
        foreach ($params as $key => $value) {
            $value = str_replace('%7E', '~', rawurlencode($value));
            $new[$key] = $pair ? "{$key}={$value}" : $value;
        }
        uksort($new, 'strnatcmp');
        return implode('&', $new);
    }
    
    private static function parseQuery($query) {
        foreach (explode('&', $query) as $pair) {
            foreach (explode('=', $pair, 2) + array(1 => '') as $key => $value) {
                $params[$key] = $value;
            }
        }
        if ($params === array('' => '')) {
            $params = array();
        }
        return $params;
    }
    
    private static function parseEndPoint($endpoint) {
        switch (true) {
            case !$p = parse_url($endpoint):
            case !isset($p['path']):
            case !$parts = preg_split('@/@', $p['path'], -1, PREG_SPLIT_NO_EMPTY):
                throw new InvalidArgumentException("invalid endpoint \"{$endpoint}\"");
        }
        if (!is_numeric($parts[0])) {
            if ($parts[0] === 'oauth2') {
                throw new InvalidArgumentException("this library does not support OAuth 2.0 authentication");
            } elseif ($parts[0] === 'account' and isset($parts[1]) and $parts[1] === 'generate') {
                array_splice($parts, 0, 0, '1');
            } elseif ($parts[0] !== 'oauth') {
                array_splice($parts, 0, 0, '1.1');
            } else {
                $oauth = true;
            }
        }
        if (!isset($oauth)) {
            if ($parts[1] === 'user') {
                $host = 'userstream.twitter.com';
                $streaming = true;
            } elseif ($parts[1] === 'site') {
                $host = 'sitestream.twitter.com';
                $streaming = true;
            } elseif (
                $parts[1] === 'statuses' and
                isset($parts[2]) and
                in_array($parts[2], array('filter', 'sample', 'firehose'))
            ) {
                $host = 'stream.twitter.com';
                $streaming = true;
            } else {
                $host = 'api.twitter.com';
            }
            $parts[] = basename(array_pop($parts), '.json') . '.json';
        } else {
            $host = 'api.twitter.com';
            $parts[] = basename(array_pop($parts), '.json');
        }
        return array(
            'host'      => $host,
            'endpoint'  => '/' . implode('/', $parts),
            'params'    => isset($p['query']) ? self::parseQuery($p['query']) : array(),
            'streaming' => isset($streaming),
        );
    }
    
    private function __construct($endpoint, $method, $params, $multipart) {
        $p = self::parseEndPoint(self::asString($endpoint));
        if (!is_array($params)) {
            $params = $params === '' ? array() : self::parseQuery($params);
        }
        if (!in_array($method = strtoupper(self::asString($method)), array('GET', 'POST'), true)) {
            throw new InvalidArgumentException("Unsupported method \"{$method}\"");
        }
        $this->host      = $p['host'];
        $this->endpoint  = $p['endpoint'];
        $this->method    = self::asString($method);
        $this->params    = self::solveParams($params + $p['params'], !$multipart);
        $this->multipart = (bool)$multipart;
        $this->streaming = $p['streaming'];
    }
    
    private function parseHeader(TwistConsumer $consumer, $line) {
        list($key, $value) = explode(': ', $line, 2) + array(1 => '');
        $key = strtolower($key);
        switch ($key) {
            case 'x-twitter-new-account-oauth-access-token':
            case 'x-twitter-new-account-oauth-secret':
            case 'transfer-encoding':
            case 'content-encoding':
            case 'content-length':
                return array($key => substr($value, 0, -2));
            case 'set-cookie':
                list($k, $v) = explode('=', $line, 2) + array(1 => '');
                if (false === $p = strpos($v, ';')) {
                    $p = -2;
                }
                $consumer->setCookie($k, substr($v, 0, $p));
                break;
        }
        return array();
    }
    
    private function buildHeaders(TwistConsumer $consumer) {
        $lines = array();
        $content = $this->buildOAuthPart($consumer);
        $connection = $this->streaming ? 'keep-alive' : 'close';
        if ($this->method === 'GET') {
            if ('' !== $query = self::buildQuery($this->params)) {
                $content .= "&{$query}";
            }
            $lines = array(
                "{$this->method} {$this->endpoint}?{$content} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: TwistOAuth",
                "Connection: {$connection}",
                "",
                "",
            );
        } elseif (!$this->multipart) {
            if ('' !== $query = self::buildQuery($this->params)) {
                $content .= "&{$query}";
            }
            $length = strlen($content);
            $lines = array(
                "{$this->method} {$this->endpoint} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: TwistOAuth",
                "Connection: {$connection}",
                "Content-Type: application/x-www-form-urlencoded",
                "Content-Length: {$length}",
                "",
                $content,
            );
        } else {
            $boundary = '--------------------' . sha1(mt_rand() . microtime());
            $authorization = implode(', ', explode('&', $content));
            $content = self::buildMultipartContent($this->params, $boundary);
            $length = strlen($content);
            $lines = array(
                "{$this->method} {$this->endpoint} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: TwistOAuth",
                "Connection: {$connection}",
                "Authorization: {$authorization}",
                "Content-Type: multipart/form-data; boundary={$boundary}",
                "Content-Length: {$length}",
                "",
                $content,
            );
        }
        if (!$this->streaming) {
            array_splice($lines, 3, 0, "Accept-Encoding: gzip");
        }
        return implode("\r\n", $lines);
    }
    
    private function buildOAuthPart(TwistConsumer $consumer) {
        $bodies = array(
            'oauth_consumer_key'     => $consumer->consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0a',
            'oauth_nonce'            => sha1(mt_rand() . microtime()),
        );
        $keys = array($consumer->consumerSecret, '');
        if ($this->endpoint === '/oauth/access_token') {
            $bodies['oauth_token'] = $consumer->requestToken;
            if (isset($this->params['oauth_verifier'])) {
                $bodies['oauth_verifier'] = $this->params['oauth_verifier'];
                unset($this->params['oauth_verifier']);
            }
            $keys[1] = $consumer->requestTokenSecret;
        } elseif ($this->endpoint !== '/oauth/request_token') {
            $bodies['oauth_token'] = $consumer->accessToken;
            $keys[1] = $consumer->accessTokenSecret;
        }
        $params = $bodies;
        if (!$this->multipart) {
            $params += $this->params;
        }
        $url = "https://{$this->host}{$this->endpoint}";
        $params = self::buildQuery(array($this->method, $url, self::buildQuery($params)), false);
        $keys   = self::buildQuery($keys, false);
        $bodies['oauth_signature'] = base64_encode(hash_hmac('sha1', $params, $keys, true));
        return self::buildQuery($bodies);
    }
    
}