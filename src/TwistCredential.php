<?php

/**
 * Consumer Keys and OAuth Tokens are bundled here.
 * 
 * @property-read string $userAgent
 * @property-read string $consumerKey
 * @property-read string $consumerSecret
 * @property-read string $requestToken
 * @property-read string $requestTokenSecret
 * @property-read string $accessToken
 * @property-read string $accessTokenSecret
 * @property-read string $userId
 * @property-read string $screenName
 * @property-read string $password
 * @property-read string $authenticityToken
 * @property-read string $verifier
 * @property-read array<string, string> $history
 * @property-read array<string, string> $cookies
 *
 * @inherited method final protected static mixed TwistBase::filter() 
 */
class TwistCredential extends TwistBase {
    
   /**
    * OAuth parameters.
    *
    * @var string 
    */
    // required.
    private $userAgent = 'TwistOAuth';
    // required.
    private $consumerKey = '';
    // required.
    private $consumerSecret = '';
    // automatically set after "oauth/request_token" calls.
    private $requestToken = '';
    // automatically set after "oauth/request_token" calls.
    private $requestTokenSecret = '';
    // automatically set after "oauth/access_token" calls.
    private $accessToken = ''; 
    // automatically set after "oauth/access_token" calls.
    private $accessTokenSecret = '';
    // automatically set after "oauth/access_token" "GET account/verify_credentials" calls.
    private $userId = ''; 
    // automatically set after "oauth/access_token" "GET account/verify_credentials" calls.
    // used for Para-xAuth authorization.
    private $screenName = '';
    // used for Para-xAuth authorization.
    private $password = '';
    // automatically set after "GET oauth/authorize" "GET oauth/authenticate" calls. used for Para-xAuth authorization.
    private $authenticityToken = '';
    // automatically set after "POST oauth/authorize" "POST oauth/authenticate" calls. used for Para-xAuth authorization.
    private $verifier = ''; 
    
   /**
    * API call history.
    *
    * @var array
    */
    private $history = array();
    
   /**
    * Cookies mainly for scraping.
    *
    * @var array 
    */
    private $cookies = array();
    
    /**
     * Constructor.
     * 
     * @magic
     * @final
     * @access public
     * @param string [$consumerKey]
     * @param string [$consumerSecret]
     * @param string [$accessToken]
     * @param string [$accessTokenSecret]
     * @param string [$screenName]
     * @param string [$password]
     */
    final public function __construct(
        $consumerKey       = '',
        $consumerSecret    = '',
        $accessToken       = '',
        $accessTokenSecret = '',
        $screenName        = '',
        $password          = ''
    ) {
        $this->setConsumer($consumerKey, $consumerSecret)
             ->setAccessToken($accessToken, $accessTokenSecret)
             ->setScreenName($screenName)
             ->setPassword($password)
        ;
    }
    
    /**
     * Stringificator.
     *
     * @magic
     * @final
     * @access public
     * @return string
     */
    final public function __toString() {
        $string = '';
        if ($this->screenName !== '') {
            $string .= "@{$this->screenName}";
        }
        if ($this->userId !== '') {
            $string .= "(#{$this->userId})";
        }
        return $string;
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
     * Checker for property existence.
     * 
     * @magic
     * @final
     * @access public
     * @param string $name
     * @throw OutOfRangeException(LogicException)
     * @return mixed
     */
    final public function __isset($name) {
        return isset($this->{self::filter($name)});
    }
    
    /**
     * Set userAgent.
     * 
     * @final
     * @access public
     * @param string [$userId]
     * @param string [$screenName]
     * @return TwistCredential $this
     */
    final public function setUserAgent($userAgent) {
        $this->userAgent = self::filter($userAgent);
        return $this;
    }
    
    /**
     * Set consumerKey and consumerSecret.
     * 
     * @final
     * @access public
     * @param string [$consumerKey]
     * @param string [$consumerSecret]
     * @return TwistCredential $this
     */
    final public function setConsumer($consumerKey = '', $consumerSecret = '') {
        $this->consumerKey    = self::filter($consumerKey);
        $this->consumerSecret = self::filter($consumerSecret);
        return $this;
    }
    
    /**
     * Set requestToken and requestTokenSecret.
     * 
     * @final
     * @access public
     * @param string [$requestToken]
     * @param string [$requestTokenSecret]
     * @return TwistCredential $this
     */
    final public function setRequestToken($requestToken = '', $requestTokenSecret = '') {
        $this->requestToken       = self::filter($requestToken);
        $this->requestTokenSecret = self::filter($requestTokenSecret);
        return $this;
    }
    
    /**
     * Set accessToken and accessTokenSecret.
     * 
     * @final
     * @access public
     * @param string [$requestToken]
     * @param string [$requestTokenSecret]
     * @return TwistCredential $this
     */
    final public function setAccessToken($accessToken = '', $accessTokenSecret = '') {
        $this->accessToken       = self::filter($accessToken);
        $this->accessTokenSecret = self::filter($accessTokenSecret);
        return $this;
    }
    
    /**
     * Set userId.
     * 
     * @final
     * @access public
     * @param string [$userId]
     * @return TwistCredential $this
     */
    final public function setUserId($userId = '') {
        $this->userId = self::filter($userId);
        return $this;
    }
    
    /**
     * Set screenName.
     * 
     * @final
     * @access public
     * @param string [$screenName]
     * @return TwistCredential $this
     */
    final public function setScreenName($screenName = '') {
        $this->screenName = self::filter($screenName);
        return $this;
    }
    
    /**
     * Set password.
     * 
     * @final
     * @access public
     * @param string [$password]
     * @return TwistCredential $this
     */
    final public function setPassword($password = '') {
        $this->password = self::filter($password);
        return $this;
    }
    
    /**
     * Set authenticityToken.
     * 
     * @final
     * @access public
     * @param string [$authenticityToken]
     * @return TwistCredential $this
     */
    final public function setAuthenticityToken($authenticityToken = '') {
        $this->authenticityToken = self::filter($authenticityToken);
        return $this;
    }
    
    /**
     * Set verifier.
     * 
     * @final
     * @access public
     * @param string [$verifier]
     * @return TwistCredential $this
     */
    final public function setVerifier($verifier = '') {
        $this->verifier = self::filter($verifier);
        return $this;
    }
    
    /**
     * Get auth URL for "GET oauth/authorize"
     * 
     * @final
     * @access public
     * @param bool $force_login
     * @return string
     */ 
    final public function getAuthorizeUrl($force_login = false) {
        return $this->getAuthUrl('authorize', $force_login);
    }
    
    /**
     * Get auth URL for "GET oauth/authenticate"
     *
     * @final
     * @access public
     * @param bool $force_login
     * @return string
     */ 
    final public function getAuthenticateUrl($force_login = false) {
        return $this->getAuthUrl('authenticate', $force_login);
    }
    
    /**
     * Set API call history.
     *
     * @final
     * @access public
     * @param string $name
     * @param int $value
     * @return TwistCredential $this
     */ 
    final public function setHistory($name, $value) {
        $this->history[self::filter($name)] = (int)$value;
        return $this;
    }
    
    /**
     * Set cookie mainly for scraping.
     *
     * @final
     * @access public
     * @param string $name
     * @return TwistCredential $this
     */ 
    final public function setCookie($name, $value) {
        $this->cookies[self::filter($name)] = self::filter($value);
        return $this;
    }
    
    /**
     * Get authorize or authenticate URL.
     *
     * @access private
     * @param string $mode "authorize" or "authenticate"
     * @param bool $force_login
     * @return string
     */ 
    private function getAuthUrl($mode, $force_login) {
        $url = "https://api.twitter.com/oauth/{$mode}";
        $params = array(
            'oauth_token' => $this->requestToken,
            'force_login' => $force_login ? '1' : null // NULL is ignored
        );
        return $url . '?' . http_build_query($params, '', '&');
    }
    
}