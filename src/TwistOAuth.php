<?php

/**
 * Main class.
 *
 * @inherited method final public void TwistUnserializable::__sleep()
 * @inherited method final public void TwistUnserializable::__wakeup()
 * @inherited method final protected static mixed TwistBase::filter() 
 */
class TwistOAuth extends TwistUnserializable {
    
    /**
     * Credentials.
     * 
     * @var array<TwistCredential>
     */
    private $credentials = array();
    
    /**
     * Constructor.
     * Only first credential is used for POST requests.
     * Other credentials are rotationally used for GET requests.
     * Unauthorized credentials are automatically tried
     * to be authorized by Para-xAuth authorization.
     * 
     * @magic
     * @final
     * @access public
     * @params mixed $args TwistRequest or array<TwistRequest>
     * @throw InvalidArgumentException(LogicException)
     */
    final public function __construct($args) {
        if (!$args = func_get_args()) {
            throw new InvalidArgumentException('Required at least 1 TwistCredential instance.');
        }
        array_walk_recursive($args, array($this, 'setCredential'));
        foreach ($this->credentials as $credential) {
            if ($credential->accessToken === '' or $credential->accessTokenSecret === '') {
                $logins[] = TwistRequest::login($credential);
            }
        }
        if (isset($logins)) {
            // Para-xAuth authorization
            foreach (new TwistIterator($logins) as $dummy) { }
        }
    }
    
    /**
     * Call "GET" endpoints.
     * 
     * @final
     * @static
     * @access public
     * @param string [$endpoint]
     *   e.g. "statuses/home_timeline"
     *        "1/account/generete"
     *        "user"
     * @param mixed [$params]
     *   e.g. "count=1"
     *        array("count" => 1)
     * @return stdClass or array or TwistException
     */
    // Normal.
    final public function get($endpoint, $params = array()) {
        $tc = $this->autoSelect(self::filter($endpoint));
        return TwistRequest::get($endpoint, $params, $tc)->execute();
    }
    // Automatically throw TwistException.
    final public function getAuto($endpoint, $params = array()) {
        $tc = $this->autoSelect(self::filter($endpoint));
        return TwistRequest::getAuto($endpoint, $params, $tc)->execute();
    }
    
    /**
     * Create instance for "POST" endpoints.
     * Filenames are specified with putting "@" on its KEY.
     * 
     * @final
     * @static
     * @access public
     * @param string $endpoint
     *   e.g. "account/update_profile_image"
     * @param mixed [$params]
     *   e.g. "@image=me.jpg"
     *        array("@image" => "me.jpg")
     * @return stdClass or array or TwistException or NULL
     */
    // Normal.
    final public function post($endpoint, $params = array()) {
        return TwistRequest::post($endpoint, $params, $this->credentials[0])->execute();
    }
    // Automatically throw TwistException.
    final public function postAuto($endpoint, $params = array()) {
        return TwistRequest::postAuto($endpoint, $params, $this->credentials[0])->execute();
    }
    // Receive no response.
    final public function send($endpoint, $params = array()) {
        return TwistRequest::send($endpoint, $params, $this->credentials[0])->execute();
    }
    
    /**
     * Set TwistCredential instance.
     * 
     * @access private
     * @param TwistCredential $credential
     */
    private function setCredential(TwistCredential $credential) {     
        $this->credentials[] = $credential;
    }
    
    /**
     * Select TwistCredential instance for GET requests.
     * 
     * @access private
     * @param string $endpoint
     */
    private function autoSelect($endpoint) {
        $tmp = TwistRequest::get($endpoint);
        $endpoint = $tmp->endpoint;
        foreach ($this->credentials as $i => $credential) {
            switch (true) {
                case !isset($credential->history[$endpoint]):
                    $credential->setHistory($endpoint, 0);
                case !isset($tc):
                case $min > $credential->history[$endpoint]:
                    $tc  = $credential;
                    $min = $credential->history[$endpoint];
            }
        }
        return $tc;
    }
    
}
