<?php

namespace mpyw\Cowitter\Helpers;

class CredentialNormalizer
{
    protected static $OAuthCredentialPatterns = [
        'consumer_key' => '/\A(?:0|ck|(oauth[ _-]?)?consumer[ _-]?key)\z/i',
        'consumer_secret' => '/\A(?:1|cs|(oauth[ _-]?)?consumer[ _-]?secret)\z/i',
        'token' => '/\A(?:2|[aor]?t|(?:(?:access|oauth|request)[ _-]?)?token)\z/i',
        'token_secret' => '/\A(?:3|[aor]?ts|[aor]s|(?:(?:access|oauth|request)[ _-]?)?token[ _-]?secret|(?:access|oauth|request)[ _-]?secret)\z/i',
    ];

    protected static function snake($letters)
    {
        return strtolower(preg_replace('/[a-z]++\K(?=[A-Z])|[A-Z]++\K(?=[A-Z][a-z])/', '_', $letters));
    }

    public static function normalizeCredentialParamName($key) {
        $snaked_key = static::snake($key);
        foreach (self::$OAuthCredentialPatterns as $name => $pattern) {
            if (preg_match($pattern, $snaked_key)) {
                return $name;
            }
        }
        throw new \DomainException('Unknown credential parameter: "' . $key . '"');
    }

    public static function normalizeCredentialParamNames(array $params)
    {
        $r = [];
        foreach ($params as $key => $value) {
            $r[static::normalizeCredentialParamName($key)] = $value;
        }
        return $r;
    }
}
