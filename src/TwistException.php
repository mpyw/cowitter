<?php

/**
 * Hanldes Exceptions ocurred due to Twitter.
 * 
 * @final
 * @property-read TwistRequest $request
 * 
 * @inherited var protected string Exception::$message
 * @inherited var protected int Exception::$code
 * @inherited var protected string Exception::$file
 * @inherited var protedted int Exception::$line
 * @inherited method final protected static mixed TwistBase::filter()
 * @inherited method final public string Exception::getMessage()
 * @inherited method final public int Exception::getCode()
 * @inherited method final public string Exception::getFile()
 * @inherited method final public int Exception::getLine()
 * @inherited method final public int Exception::getTrace()
 * @inherited method final public string Exception::getTraceAsString()
 */
final class TwistException extends RuntimeException {
    
    /**
     * TwistRequest instance where this TwistException generated.
     * 
     * @var TwistRequest
     */
    private $request = null;
    
    /**
     * Constructor.
     * 
     * @override
     * @magic
     * @access public
     * @param string $message
     * @param int $code
     * @param TwistRequest [$request]
     */
    public function __construct($message, $code, TwistRequest $request = null) {
        $this->request = $request;
        parent::__construct($message, $code);
    }
    
    /**
     * Stringificator.
     *
     * @override
     * @magic
     * @access public
     * @return string
     */
    public function __toString() {
        return sprintf('[%d] %s', $this->getCode(), $this->getMessage());
    }
    
    /**
     * Getter for property TwistRequest.
     * 
     * @access public
     * @return TwistRequest or NULL
     */
    public function getRequest() {
        return $this->request;
    }
    
}