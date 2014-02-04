<?php

/**
 * Traversable execution class.
 * 
 * @inherited method public TwistExecuter::__construct()
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
     * Callback function.
     *
     * @var callable 
     * @var array
     * @var float
     * @var float
     */
    private $callback;
    private $args = array();    
    private $interval = 0;
    private $timer = 0;
    
    /**
     * Set interval callback function.
     *
     * @access public
     * @param callable $callback
     * @param float [$interval] microseconds
     * @param array [$args]
     * @throw InvalidArgunemtException(LogicException)
     * @return TwistIterator $this
     */
    public function setInterval($callback, $interval = 0, array $args = array()) {
        if (!is_callable($callback)) {
            throw InvalidArgumentException('Invalid callback passed.');
        }
        $this->interval = abs((float)$sec);
        $this->args     = $args;
        return $this;
    }
    
    /**
     * Iterator implemention.
     * 
     * @access public
     * @return TwistIterator $this
     */
    public function rewind() {
        $this->responses = array();
        return $this->start();
    }
    
    /**
     * Iterator implemention.
     * 
     * @access public
     * @return bool
     */
    public function valid() {
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
     * @access public
     * @return string
     */
    public function key() {
        return ''; // dummy
    }
    
    /**
     * Iterator implemention.
     *
     * @access public
     * @return mixed TwistRequest or TwistException
     */
    public function current() {
        if ($this->callback) {
            $time = microtime(true);
            $this->timer -= $time;
            if ($this->timer <= 0) {
                $this->timer = $this->interval;
                call_user_func_array($this->callback, $this->args);
            }
        }
        return current($this->responses);
    }
    
    /**
     * Iterator implemention.
     * 
     * @access public
     * @return TwistIterator $this
     */
    public function next() {
        next($this->responses);
        return $this;
    }
    
}