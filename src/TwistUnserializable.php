<?php

/**
 * Unserializable class.
 * 
 * @abstract
 *
 * @inherited method final protected static mixed TwistBase::filter() 
 */
abstract class TwistUnserializable extends TwistBase {
    
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