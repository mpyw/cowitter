<?php

/**
 * Provides basic static filter methods.
 * 
 * @abstract
 */
abstract class TwistBase {
    
    /**
     * Filter var to force string or specified dementional array.
     * 
     * @final
     * @static
     * @access protected
     * @param mixed $input
     * @param int [$array_demention]
     * @return mixed
     */
    final protected static function filter($input, $array_demention = 0) {
        $array_demention = (int)$array_demention;
        if ($array_demention < 1) {
            // 0 demention
            switch (true) {
                case is_array($input):
                case is_object($input) and !method_exists($input, '__toString'):
                    // apply empty string for which causes error
                    return ''; 
            }
            // stringify
            return (string)$input;
        }
        // X demention (X > 0)
        $output = array();
        foreach ((array)$input as $key => $value) {
            // force (X - 1) demention
            $output[self::filter($key)] = self::filter($value, $array_demention - 1);
        }
        return $output;
    }
    
}