<?php

namespace mpyw\Cowitter\Helpers;

class RequestParamValidator
{
    public static function validateStringable($name, $value)
    {
        if ($value instanceof \CURLFile) {
            if (false === $value = @file_get_contents($value->getFilename())) {
                $error = error_get_last();
                throw new \RuntimeException($error['message']);
            }
            $value = base64_encode($value);
        } else if (false === $result = filter_var($value)) {
            $type = gettype($value);
            throw new \InvalidArgumentException("\"$name\" must be stringable, $type given.");
        }
        return (string)$value;
    }

    public static function validateParams(array $params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = static::validateStringable($key, $value);
        }
        return $params;
    }

    public static function validateMultipartParams(array $params)
    {
        foreach ($params as $key => $value) {
            if (!$value instanceof \CURLFile) {
                $params[$key] = static::validateStringable($key, $value);
            }
        }
        return $params;
    }

}
