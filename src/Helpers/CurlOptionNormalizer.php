<?php

namespace mpyw\Cowitter\Helpers;

class CurlOptionNormalizer
{
    protected static $strToInt = [];
    protected static $intToStr = [];

    public static function stringify($key)
    {
        static::initialize();
        if (is_int($key)) {
            if (!isset(static::$intToStr[$key])) {
                throw new \DomainException('Invalid cURL option number: ' . $key);
            }
            return static::$intToStr[$key];
        };
        if (!isset(static::$strToInt[$key])) {
            throw new \DomainException('Invalid cURL option name: ' . $key);
        }
        return $key;
    }

    public static function numerify($key)
    {
        static::initialize();
        if (is_string($key)) {
            if (!isset(static::$strToInt[$key])) {
                throw new \DomainException('Invalid cURL option name: ' . $key);
            }
            return static::$strToInt[$key];
        };
        if (!isset(static::$intToStr[$key])) {
            throw new \DomainException('Invalid cURL option number: ' . $key);
        }
        return $key;
    }

    public static function stringifyAll(array $options)
    {
        $r = [];
        foreach ($options as $key => $value) {
            $r[static::stringify($key)] = $value;
        }
        return $r;
    }

    public static function numerifyAll(array $options)
    {
        $r = [];
        foreach ($options as $key => $value) {
            $r[static::numerify($key)] = $value;
        }
        return $r;
    }

    protected static function initialize()
    {
        if (!static::$strToInt) {
            static::$strToInt = get_defined_constants(true)['curl'];
            static::$intToStr = array_flip(static::$strToInt);
        }
    }
}
