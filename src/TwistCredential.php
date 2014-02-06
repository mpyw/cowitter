<?php

/**
 * Consumer Keys and OAuth Tokens are bundled here.
 * 
 * @property-read string $consumerKey
 * @property-read string $consumerSecret
 * @property-read string $requestToken
 * @property-read string $requestTokenSecret
 * @property-read string $accessToken
 * @property-read string $accessTokenSecret
 * @property-read string $screenName
 * @property-read string $userId
 * @property-read string $userAgent
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
    private $consumerKey        = '';
    private $consumerSecret     = '';
    private $requestToken       = ''; // automatically set after "oauth/request_token" calls.
    private $requestTokenSecret = ''; // automatically set after "oauth/request_token" calls.
    private $accessToken        = ''; // automatically set after "oauth/access_token" calls.
    private $accessTokenSecret  = ''; // automatically set after "oauth/access_token" calls.
    private $authenticityToken  = ''; // automatically set after first "oauth/authenticate" "oauth/authorize" calls.
    private $verifier           = ''; // automatically set after second "oauth/authenticate" "oauth/authorize" calls.
    private $screenName         = ''; // automatically set after "oauth/access_token" calls.
    private $userId             = ''; // automatically set after "oauth/access_token" calls.
    private $userAgent          = 'TwistOAuth';
    
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
     * @access public
     * @param string [$consumerKey]
     * @param string [$consumerSecret]
     * @param string [$accessToken]
     * @param string [$accessTokenSecret]
     */
    public function __construct(
        $consumerKey       = '',
        $consumerSecret    = '',
        $accessToken       = '',
        $accessTokenSecret = ''
    ) {
        $this->setConsumer($consumerKey, $consumerSecret)
             ->setAccessToken($accessToken, $accessTokenSecret);
    }
    
    /**
     * Stringificator.
     *
     * @magic
     * @access public
     * @return string
     */
    public function __toString() {
        $string = '';
        if ($this->screenName !== '') {
            $string .= $this->screenName;
        }
        if ($this->userId !== '') {
            $string .= "({$this->userId})";
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
     * @return string
     */
    final public function __get($name) {
        if (!property_exists($this, $name = self::filter($name))) {
            throw new OutOfRangeException("Invalid property name: {$name}");
        }
        return $this->$name;
    }
    
    /**
     * Set consumerKey and consumerSecret.
     * 
     * @access public
     * @param string [$consumerKey]
     * @param string [$consumerSecret]
     * @return TwistCredential $this
     */
    public function setConsumer($consumerKey = '', $consumerSecret = '') {
        $this->consumerKey    = self::filter($consumerKey);
        $this->consumerSecret = self::filter($consumerSecret);
        return $this;
    }
    
    /**
     * Set requestToken and requestTokenSecret.
     * 
     * @access public
     * @param string [$requestToken]
     * @param string [$requestTokenSecret]
     * @return TwistCredential $this
     */
    public function setRequestToken($requestToken = '', $requestTokenSecret = '') {
        $this->requestToken       = self::filter($requestToken);
        $this->requestTokenSecret = self::filter($requestTokenSecret);
        return $this;
    }
    
    /**
     * Set accessToken and accessTokenSecret.
     * 
     * @access public
     * @param string [$requestToken]
     * @param string [$requestTokenSecret]
     * @return TwistCredential $this
     */
    public function setAccessToken($accessToken = '', $accessTokenSecret = '') {
        $this->accessToken       = self::filter($accessToken);
        $this->accessTokenSecret = self::filter($accessTokenSecret);
        return $this;
    }
    
    /**
     * Set authenticityToken.
     * 
     * @access public
     * @param string [$authenticityToken]
     * @return TwistCredential $this
     */
    public function setAuthenticityToken($authenticityToken = '') {
        $this->authenticityToken = self::filter($authenticityToken);
        return $this;
    }
    
    /**
     * Set verifier.
     * 
     * @access public
     * @param string [$verifier]
     * @return TwistCredential $this
     */
    public function setAuthenticityToken($verifier = '') {
        $this->verifier = self::filter($verifier);
        return $this;
    }
    
    /**
     * Set userId and screenName.
     * 
     * @access public
     * @param string [$userId]
     * @param string [$screenName]
     * @return TwistCredential $this
     */
    public function setUserInfo($userId = '', $screenName = '') {
        $this->userId     = self::filter($userId);
        $this->screenName = self::filter($screenName);
        return $this;
    }
    
    /**
     * Set userAgent.
     * 
     * @access public
     * @param string [$userId]
     * @param string [$screenName]
     * @return TwistCredential $this
     */
    public function setUserAgent($userAgent) {
        $this->userAgent = self::filter($userAgent);
        return $this;
    }
    
    /**
     * Get auth URL for "GET oauth/authorize"
     * 
     * @access public
     * @param bool $force_login
     * @return string
     */ 
    public function getAuthorizeUrl($force_login = false) {
        return $this->getAuthUrl('authorize', $force_login);
    }
    
    /**
     * Get auth URL for "GET oauth/authenticate"
     *
     * @access public
     * @param bool $force_login
     * @return string
     */ 
    public function getAuthenticateUrl($force_login = false) {
        return $this->getAuthUrl('authenticate', $force_login);
    }
    
    /**
     * Set API call history.
     *
     * @access public
     * @param string $name
     * @return TwistCredential $this
     */ 
    public function setHistory($name) {
        $name = self::filter($name);
        // insert 0 on duplicate key update +1
        $this->history += array($name => 0);
        ++$this->history[$name];
        return $this;
    }
    
    /**
     * Set cookie mainly for scraping.
     *
     * @access public
     * @param string $name
     * @return TwistCredential $this
     */ 
    public function setCookie($key, $value) {
        $this->cookies[self::filter($key)] = self::filter($value);
        return $this;
    }
    
    /**
     * Get authorrize or authenticate URL.
     *
     * @access private
     * @param string $mode "authorize" or "authenticate"
     * @param bool $force_login
     * @return string
     */ 
    private function getAuthUrl($mode, $force_login) {
        $url = "https://api.twitter.com/oauth/{$mode}";
        $params = array(
            'oauth_token' => $this->request_token,
            'force_login' => $force_login ? '1' : null // NULL is ignored
        );
        return $url . '?' . http_build_query($params, '', '&');
    }
    
}