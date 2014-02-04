<?php

/**
 * Unserializable class.
 * 
 * @abstract
 */
abstract class TwistUnserializable {
    
    /**
     * @magic
     * @final
     * @access public
     * @throw BadMethodCallException
     */
    final public function __sleep() {
        throw new BadMethodCallException('This object cannot be serialized.');
    }
    
    /**
     * @magic
     * @final
     * @access public
     * @throw BadMethodCallException
     */
    final public function __wakeup() {
        throw new BadMethodCallException('This serial cannot be unserialized.');
    }
    
}