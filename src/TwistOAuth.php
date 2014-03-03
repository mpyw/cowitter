<?php

/**
 * Main class.
 *
 * @property-read TwistCredential $credential
 * @property-read array<TwistCredential> $subCredentials
 * 
 * @inherited method final protected static mixed TwistBase::filter() 
 */
class TwistOAuth extends TwistBase {
    
    /**
     * Credentials.
     * 
     * @var TwistCredential
     * @var array<TwistCredential>
     */
    private $credential;
    private $subCredentials = array();
    
    /**
     * Constructor.
     * 
     * @magic
     * @final
     * @access public
     * @params TwistCredential $credential
     */
    final public function __construct(TwistCredential $credential) {
        $this->credential = $credential;
    }
    
    /**
     * Getter for properties.
     * 
     * @magic
     * @final
     * @access public
     * @param string $name
     * @throw OutOfRangeException(LogicException)
     * @return mixed
     */
    final public function __get($name) {
        if (!property_exists($this, $name = self::filter($name))) {
            throw new OutOfRangeException("Invalid property name: {$name}");
        }
        return $this->$name;
    }
    
    /**
     * Call "GET" endpoints.
     * 
     * @final
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
        return TwistRequest::get($endpoint, $params, $this->autoSelect($endpoint))->execute();
    }
    // Automatically throw TwistException.
    final public function getAuto($endpoint, $params = array()) {
        return TwistRequest::getAuto($endpoint, $params, $this->autoSelect($endpoint))->execute();
    }
    
    /**
     * Create instance for "POST" endpoints.
     * Filenames are specified with putting "@" on its KEY.
     * 
     * @final
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
        return TwistRequest::post($endpoint, $params, $this->credential)->execute();
    }
    // Automatically throw TwistException.
    final public function postAuto($endpoint, $params = array()) {
        return TwistRequest::postAuto($endpoint, $params, $this->credential)->execute();
    }
    // Receive no response.
    final public function send($endpoint, $params = array()) {
        return TwistRequest::send($endpoint, $params, $this->credential)->execute();
    }
    
    /**
     * Execute Para-xAuth authorization.
     *
     * @final
     * @access public
     * @param TwistCredential $credential
     * @return stdClass
     */
    final public function login() {
        return TwistRequest::login($this->credential)->execute();
    }
    
    /**
     * Register or unregister sub TwistCredential instances.
     * 
     * @final
     * @access public
     * @throw InvalidArgumentException
     * @return TwistOAuth $this
     */
    final public function setSub() {
        $args = func_get_args();
        $this->subCredentials = array();
        array_walk_recursive($args, array($this, 'setSubCallback'));
    }
    
    /**
     * Callback for TwistOAuth::setSub()
     * 
     * @access private
     * @param TwistCredential $credential
     */
    private function setSubCallback(TwistCredential $credential) {
        $hash = spl_object_hash($credential);
        $main_hash = spl_object_hash($this->credential);
        if ($hash === $main_hash) {
            throw new InvalidArgumentException(
                'Specified credential is already registered as main credential.'
            );
        }
        if (isset($this->subCredentials[$hash])) {
            throw new InvalidArgumentException(
                'Specified credential is already registered as sub credential.'
            );
        }
        $this->subCredentials[$hash] = $credential;
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
        $credentials = array_merge($this->subCredentials, array($this->credential));
        foreach ($credentials as $i => $credential) {
            switch (true) {
                case !isset($credential->history[$endpoint]):
                    $credential->setHistory($endpoint, 0);
                case !isset($selected):
                case $min > $credential->history[$endpoint]:
                    $selected = $credential;
                    $min      = $credential->history[$endpoint];
            }
        }
        return $selected;
    }
    
}
