<?php

/**
 * Media class.
 * Instances are internally genereted.
 */
final class TwistMedia {
    
    /**
     * @property-read string $type
     * @property-read string $data
     */
    private $type;
    private $data;
    
    /**
     * Constructor.
     * 
     * @param string $type Content-Type
     * @param string $data Binary data
     */
    public function __construct($type, $data) {
        $this->type = filter_var($type);
        $this->data = filter_var($data);
    }
    
    /**
     * Getter for private properties.
     *
     * @name string property name
     * @return string
     */
    public function __get($name) {
        $name = filter_var($name);
        if (!property_exists($this, $name)) {
            throw new OutOfRangeException('Invalid property: ' . $name);
        }
        return $this->$name;
    }
    
    /**
     * Issetter for private properties.
     *
     * @name string $name
     * @return bool
     */
    public function __isset($name) {
        return property_exists($this, filter_var($name));
    }
    
    /**
     * Make format of Data URI.
     *
     * @return string
     */
    public function getDataUri() {
        return sprintf('data:%s;base64,%s', $this->type, base64_encode($this->data));
    }
    
}
