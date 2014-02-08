<?php

/**
 * Traversable execution class.
 * 
 * @inherited method final public TwistExecuter::__construct()
 * @inherited method final public TwistExecuter TwistExecuter::setInterval()
 * @inherited method final public TwistExecuter TwistExcecuter::setTimeout()
 * @inherited method final public TwistExecuter TwistExcecuter::start()
 * @inherited method final public bool TwistExcecuter::isRunning()
 * @inherited method final public array<stdClass or array or TwistException> run()
 * @inherited method final protected static mixed TwistBase::filter() 
 */
class TwistIterator extends TwistExecuter implements Iterator {
    
    /**
     * Responses.
     *
     * @var array<stdClass or array or TwistException>
     */
    private $responses = array();
    
    /**
     * Iterator implemention.
     * 
     * @final
     * @access public
     * @return TwistIterator $this
     */
    final public function rewind() {
        $this->responses = array();
        return $this->start();
    }
    
    /**
     * Iterator implemention.
     * 
     * @final
     * @access public
     * @return bool
     */
    final public function valid() {
        if (false !== $tmp = current($this->responses)) {
            return true;
        }
        $this->responses = array();
        while (!$this->responses and $this->isRunning()) {
            // request more
            $this->responses = $this->run();
        }
        return (bool)$this->responses;
    }
    
    /**
     * Iterator implemention.
     *
     * @final
     * @access public
     * @return string
     */
    final public function key() {
        return ''; // dummy
    }
    
    /**
     * Iterator implemention.
     *
     * @final
     * @access public
     * @return mixed TwistRequest or TwistException
     */
    final public function current() {
        return current($this->responses);
    }
    
    /**
     * Iterator implemention.
     * 
     * @final
     * @access public
     * @return TwistIterator $this
     */
    final public function next() {
        next($this->responses);
        return $this;
    }
    
}