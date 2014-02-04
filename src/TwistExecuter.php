<?php

/**
 * Base class for TwistRequest execution.
 * All requests are executed asynchronizedly.
 * 
 * @inherited method final protected static mixed TwistBase::filter() 
 */
class TwistExecuter extends TwistUnserializable {
    
    /**
     * Used for $job::step
     *
     * @const int
     */
    // sending request headers
    const STEP_WRITE_REQUEST_HEADERS = 0;
    // receiving response headers
    const STEP_READ_RESPONSE_HEADERS = 1;
    // receiving response body with Content-Type: xx
    const STEP_READ_RESPONSE_LONGED = 2;
    // receiving response size with Transfer-Encoding: chunked
    const STEP_READ_RESPONSE_CHUNKED_SIZE = 3;
    // receiving response body with Transfer-Encoding: chunked
    const STEP_READ_RESPONSE_CHUNKED_CONTENT = 4;
    // reached EOF or not started
    const STEP_FINISHED = 5;
    
    /**
     * Array of job object stdClass.
     * Internally used only in this class.
     *
     * @var array $jobs<TwistRequest>
     */
    private $jobs = array();
    
    /**
     * Timeout parameter for stream_select()
     *
     * @var float seconds
     */
    private $timeout = 1.0;
    
    /**
     * Constructor.
     * 
     * @magic
     * @access public
     * @params mixed $args TwistRequest or array<TwistRequest>
     * @throw InvalidArgumentException(LogicException)
     * @return stdClass or array or TwistException
     */
    public function __construct($args) {
        if (!$args = func_get_args()) {
            throw new InvalidArgumentException('Required at least 1 TwistReuest instance.');
        }
        $this->jobs = array();
        array_walk_recursive($args, array($this, 'setRequest'));
    }
    
    /**
     * Set Timeout parameter for stream_select()
     *
     * @final
     * @access public
     * @param float [$sec]
     * @return TwistExecuter $this
     */
    final public function setTimeout($sec = 1.0) {
        // ensure positive float value
        $this->timeout = abs((float)$sec);
        return $this;
    }
    
    /**
     * Start asynchronized multiple requests execution.
     *
     * @final
     * @access public
     * @throw TwistException(RuntimeException)
     * @return TwistExecuter $this
     */
    final public function start() {
        foreach ($this->jobs as $job) {
            self::initialize($job);
            switch (true) {
                case !($job->request instanceof TwistRequest):
                case !($job->request->consumer instanceof TwistConsumer):
                    // skip invalid TwistRequest object
                    continue 2;
                case !$fp = fsockopen("ssl://{$job->request->host}", 443):
                case !stream_set_blocking($fp, 0):
                    throw new TwistException(
                        "Failed to connect: {$job->request->host}",
                        0,
                        $job->request
                    );
                default:
                    $job->step = self::STEP_WRITE_REQUEST_HEADERS;
                    $job->fp   = $fp;
            }
        }
        return $this;
    }
    
    /**
     * Abort all requests.
     *
     * @final
     * @access public
     * @return TwistExecuter $this
     */
    final public function abort() {
        foreach ($this->jobs as $job) {
            self::initialize($job);
        }
        return $this;
    }
    
    /**
     * Returns if responses are remained.
     *
     * @final
     * @access public
     * @return bool
     */
    final public function isRunning() {
        foreach ($this->jobs as $job) {
            if ($job->step !== self::STEP_FINISHED) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Execute available requests and fetch responses.
     *
     * @final
     * @access public
     * @throw TwistException(RuntimeException)
     * @return array<stdClass or array or TwistException>
     */
    final public function run() {
        // stream preparation
        $read = $write = $results = array();
        $except = null;
        foreach ($this->jobs as $i => $job) {
            switch ($job->step) {
                case self::STEP_FINISHED:
                    continue 2;
                case self::STEP_WRITE_REQUEST_HEADERS:
                    $write[$i] = $job->fp;
                    continue 2;
                default:
                    $read[$i] = $job->fp;
            }
        }
        if (!$read and !$write) {
            return $results;
        }
        // convert float value to a couple of int value
        $sec = (int)$this->timeout;
        $msec = (int)($this->timeout * 1000000) % 1000000;
        // stream selection (sleep a little here)
        if (false === stream_select($read, $write, $except, $sec, $msec)) {
            throw new TwistException('Failed to select stream.', 0);
        }
        foreach ($this->jobs as $job) {
            switch ($job->step) {
                case self::STEP_WRITE_REQUEST_HEADERS:
                    self::writeRequestHeaders($job);
                    break;
                case self::STEP_READ_RESPONSE_HEADERS:
                    self::readResponseHeaders($job);
                    break;
                case self::STEP_READ_RESPONSE_CHUNKED_SIZE:
                    self::readResponseChunkedSize($job);
                    break;
                case self::STEP_READ_RESPONSE_LONGED:
                    if (null !== $result = self::readResponseLonged($job)) {
                        $results[] = $job->request->setResponse($result);
                    }
                    break;
                case self::STEP_READ_RESPONSE_CHUNKED_CONTENT:
                    if (null !== $result = self::readResponseChunkedContent($job)) {
                        $results[] = $job->request->setResponse($result);
                    }
            }
        }
        return $results;
    }
    
    /**
     * Callback for constructor.
     * 
     * @access private
     * @param TwistRequest $request
     */
    private function setRequest(TwistRequest $request) {
        $job = self::initialize();
        $job->request = $request;
        $this->jobs[] = $job;
    }
    
    /**
     * Initialize job object.
     * 
     * @static
     * @access private
     * @param stdClass [$job]
     * @return stdClass $job
     */
    private static function initialize(stdClass $job = null) {
        if ($job === null) {
            $job = new stdClass;
        }
        $job->step       = self::STEP_FINISHED; // current step
        $job->fp         = false;               // connection stream resource
        $job->size       = '';                  // sub buffer for reading chunked size
        $job->buffer     = '';                  // main buffer
        $job->incomplete = '';                  // incomplete buffer
        $job->length     = 0;                   // content length
        $job->info       = array();             // response headers
        return $job;
    }
    
    /**
     * Write request headers.
     *
     * @static
     * @access private
     * @param stdClass $job
     */
    private static function writeRequestHeaders(stdClass $job) {
        if ($job->buffer === '') {
            // generate request headers at first
            $job->buffer = $job->request->buildHeaders();
        }
        if (false !== $tmp = fwrite($job->fp, $job->buffer)) {
            // reset $job::buffer with remaining bytes
            $job->buffer = (string)substr($job->buffer, $tmp);
        }
        if ($job->buffer === '') {
            // complated
            $job->step =
                $job->request->waitResponse ?
                self::STEP_READ_RESPONSE_HEADERS :
                self::STEP_FINISHED
            ; // next step
        }
        // update history
        $job->request->consumer->setHistory($job->request->endpoint);
        return;
    }
    
    /**
     * Read response headers.
     *
     * @static
     * @access private
     * @throw TwistException(RuntimeException)
     * @param stdClass $job
     */
    private static function readResponseHeaders(stdClass $job) {
        if (is_string($buffers = self::freadUntilSeparator($job->fp, $job->buffer, "\r\n\r\n"))) {
            // incomplete
            $job->buffer = $buffers;
            return;
        }
        foreach (explode("\r\n", $buffers[0]) as $i => $line) {
            // read each header
            self::readResponseHeader($job, $i, $line);
        }
        if (!empty($job->info['content-length'])) {
            // Content-Length: xx(larger than zero)
            $job->step   = self::STEP_READ_RESPONSE_LONGED; // next step
            $job->buffer = $buffers[1];
            $job->length = $job->info['content-length'];
        } elseif (isset($job->info['transfer-encoding'])) {
            // Transfer-Encoding: chunked
            $job->step   = self::STEP_READ_RESPONSE_CHUNKED_SIZE; // next step
            $job->buffer = '';
            $job->length = 0;
            $job->size   = $buffers[1];
        } else {
            throw new TwistException(
                'Detected malformed response header.',
                (int)$job->info['code'],
                $job->request
            );
        }
    }
    
    /**
     * Read response body with Content-Length: xx
     *
     * @static
     * @access private
     * @param stdClass $job
     * @return array<stdClass or array or TwistException>
     */
    private static function readResponseLonged(stdClass $job) {
        if (is_string($buffers = self::freadUntilLength($job->fp, $job->buffer, $job->length))) {
            // incomplete
            $job->buffer = $buffers;
            return;
        }
        $job->step = self::STEP_FINISHED; // next step
        $buffers[0] = gzinflate(substr($buffers[0], 10, -8));
        $buffers[0] = self::decode($job, $buffers[0]);
        self::initialize($job);
        return $buffers[0];
    }
    
    /**
     * Read response size with Transfer-Encoding: chunked
     *
     * @static
     * @access private
     * @throw TwistException(RuntimeException)
     * @param stdClass $job
     * @return array<stdClass or array or TwistException>
     */
    private static function readResponseChunkedSize(stdClass $job) {
        if (is_string($buffers = self::freadUntilSeparator($job->fp, $job->size, "\r\n"))) {
            // incomplete
            $job->size = $buffers;
            return;
        }
        switch (true) {
            case $buffers[0] === '0': // end of stream
                $job->step = self::STEP_FINISHED; // next step
                $job->buffer     = '';
                $job->size       = '';
                $job->incomplete = '';
                $job->length     = 0;
                return;
            case $buffers[0] === '': // invalid blank
            case !$tmp = hexdec($buffers[0]): // invalid size value
                throw new TwistException(
                    'Detected malformed response body.',
                    (int)$job->info['code'],
                    $job->request
                );
            default:
                $job->step   = self::STEP_READ_RESPONSE_CHUNKED_CONTENT; // next step 
                $job->buffer = $buffers[1];
                $job->size   = '';
                $job->length = $tmp;
        }
    }
    
    /**
     * Read response size with Transfer-Encoding: chunked
     *
     * @static
     * @access private
     * @param stdClass $job
     * @return array<stdClass or array or TwistException>
     */
    private static function readResponseChunkedContent(stdClass $job) {
        if (is_string($buffers = self::freadUntilLength($job->fp, $job->buffer, $job->length))) {
            // incomplete
            $job->buffer = $buffers;
            return;
        }
        $buffers[0] = substr($buffers[0], 0, -2);
        $job->buffer = '';
        $job->length = 0;
        $job->step   = self::STEP_READ_RESPONSE_CHUNKED_SIZE; // next step
        var_dump(self::decode($job, $job->incomplete . substr($buffers[0], 0, -2)));
        if (substr($buffers[0], -2) === "\r\n") {
            // end of message
            $value           = $job->incomplete . substr($buffers[0], 0, -2);
            $job->size       = $buffers[1];
            $job->incomplete = '';
            return self::decode($job, $value);
        }
        // incomplete message
        $job->size       = $buffers[1];
        $job->incomplete = $buffers[0];
        return;
    }
    
    /**
     * Read single line of response header.
     *
     * @static
     * @access private
     * @param stdClass $job
     * @param int $offset
     * @param int $line
     */
    private static function readResponseHeader(stdClass $job, $offset, $line) {
        if ($offset) {
            // "NAME: VALUE"
            list($key, $value) = explode(': ', $line, 2) + array(1 => '');
            $key = strtolower($key);
            switch ($key) {
                case 'set-cookie':
                    list($k, $v) = explode('=', $line, 2) + array(1 => '');
                    list($v) = explode(";", $v);
                    // set cookie
                    $job->request->consumer->setCookie($k, $v);
                    break;
                default:
                    // set assoc
                    $job->info[$key] = $value;
            }
        } else {
            // "HTTP/1.1 CODE MESSAGE"
            list($protocol, $code, $message) = explode(' ', $line, 3) + array(1 => '0', 2 => '');
            // set assoc
            $job->info += compact('protocol', 'code', 'memssage');
        }
    }
    
    /**
     * Read stream until specified separator.
     *
     * @static
     * @access private
     * @param stdClass $job
     * @param string $buffer
     * @param string $separator
     * @return mixed array on complention or string
     */
    private static function freadUntilSeparator($fp, $buffer, $separator) {
        while (true) {
            $items = explode($separator, $buffer, 2);
            if (isset($items[1])) {
                return $items;
            }
            if (isset($retry)) {
                return $buffer;
            }
            $buffer .= $tmp = fread($fp, 8192);
            $retry   = true;
        }
    }
    
    /**
     * Read stream until specified length.
     *
     * @static
     * @access private
     * @param stdClass $job
     * @param string $buffer
     * @param int $length
     * @return mixed array on complention or string
     */
    private static function freadUntilLength($fp, $buffer, $length) {
        while (true) {
            if ($length <= strlen($buffer)) {
                return array(
                    (string)substr($buffer, 0, $length),
                    (string)substr($buffer, $length)
                );
            }
            if (isset($retry)) {
                return $buffer;
            }
            $buffer .= $tmp = fread($fp, 8192);
            $retry   = true;
        }
    }
    
    /**
     * Decode various types of response. 
     * 
     * @static
     * @access private
     * @param stdClass $job
     * @param string $value
     * @throw TwistException(RuntimeException)
     * @return mixed stdClass or array or TwistException
     */
    private static function decode(stdClass $job, $value) {
        switch (true) {
            // try decoding as json
            case null !== $object = json_decode($value):
            // try decoding as XML
            case false !== $object = json_decode(json_encode(@simplexml_load_string($value))):
            // try decoding as Query String
            case !parse_str($value, $object)
            and $object = (object)$object
            and isset($object->oauth_token, $object->oauth_token_secret):
            // try decoding as HTML
            case preg_match("@<title>Error \d++ ([^<]++)</title>@", $value, $m)
            and $object = (object)array('error' => $m[1]):
            // try decoding as Normal String
            case !isset($job->info['content-type'])
            || $job->info['content-type'] !== 'application/json'
            and $object = (object)array('error' => trim(strip_tags($value))):
            // unknown error
            case $object = (object)array('error' => "Unknown error on parsing: {$value}"):
        }
        // update user credentials
        if (isset($object->oauth_token, $object->oauth_token_secret)) {
            if (isset($object->screen_name, $object->user_id)) {
                $job->request->consumer->screenName = $object->screen_name;
                $job->request->consumer->userId = $object->userId;
            }
            if ($job->endpoint === '/oauth/request_token') {
                $job->request->consumer->requestToken       = $object->oauth_token;
                $job->request->consumer->requestTokenSecret = $object->oauth_token_secret;
            }
            if ($job->endpoint === '/oauth/access_token') {
                $job->request->consumer->accessToken       = $object->oauth_token;
                $job->request->consumer->accessTokenSecret = $object->oauth_token_secret;
            }
        }
        // set xAuth info
        if (isset(
            $job->info['x-twitter-new-account-oauth-access-token'],
            $job->info['x-twitter-new-account-oauth-secret']
        )) {
            $object->oauth_token = $job->info['x-twitter-new-account-oauth-access-token'];
            $object->oauth_token_secret = $job->info['x-twitter-new-account-oauth-secret'];
        }
        // normalize errors as Exception
        switch (true) {
            case isset($object->errors) and is_array($object->errors):
                $object->errors = $object->errors[0]->message;
            case isset($object->errors) and is_string($object->errors):
                $object->error  = $object->errors;
            case isset($object->error):
                $object = new TwistException(
                    $object->error,
                    (int)$job->info['code'],
                    $job->request
                );
        }
        // return response
        if ($object instanceof TwistException and $job->request->throw) {
            throw $object;
        }
        return $object;
    }
    
}
