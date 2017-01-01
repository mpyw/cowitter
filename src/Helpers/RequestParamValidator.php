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
            return base64_encode($value);
        }
        if (false === $result = filter_var($value)) {
            $type = gettype($value);
            throw new \InvalidArgumentException("\"$name\" must be stringable, $type given.");
        }
        return (string)$value;
    }

    public static function validateParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value === null) {
                unset($params[$key]);
                continue;
            }
            $params[$key] = static::validateStringable($key, $value);
        }
        return $params;
    }

    public static function validateMultipartParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value === null) {
                unset($params[$key]);
                continue;
            }
            if (!$value instanceof \CURLFile) {
                $params[$key] = static::validateStringable($key, $value);
            }
        }
        return $params;
    }

    public static function isGenerator(callable $callable)
    {
        if (is_string($callable) && strpos($callable, '::')) {
            $callable = explode('::', $callable);
        } elseif (!$callable instanceof \Closure && is_object($callable)) {
            $callable = [$callable, '__invoke'];
        }
        $reflector = $callable instanceof \Closure || is_string($callable)
            ? new \ReflectionFunction($callable)
            : new \ReflectionMethod($callable[0], $callable[1]);
        return $reflector->isGenerator();
    }
}
