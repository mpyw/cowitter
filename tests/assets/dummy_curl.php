<?php

namespace mpyw\f
{
    function replace_url($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        if (!preg_match('@twitter\.com\z@', $host)) {
            return $url;
        }
        return 'http://localhost:8080' . $path . '.php'
                . (null !== $query ? "?$query" : '');
    }
}

namespace mpyw\Cowitter
{
    function curl_init($url = null) {
        return $url === null ? \curl_init() : \curl_init(\mpyw\f\replace_url($url));
    }
    function curl_setopt($ch, int $key, $value) {
        \curl_setopt($ch, $key, $key === CURLOPT_URL ? \mpyw\f\replace_url($value) : $value);
    }
    function curl_setopt_array($ch, array $values) {
        foreach ($values as $k => $v) curl_setopt($ch, $k, $v);
    }
}

namespace mpyw\Cowitter\Traits
{
    function curl_init($url = null) {
        return $url === null ? \curl_init() : \curl_init(\mpyw\f\replace_url($url));
    }
    function curl_setopt($ch, int $key, $value) {
        \curl_setopt($ch, $key, $key === CURLOPT_URL ? \mpyw\f\replace_url($value) : $value);
    }
    function curl_setopt_array($ch, array $values) {
        foreach ($values as $k => $v) curl_setopt($ch, $k, $v);
    }
}

namespace mpyw\Cowitter\Helpers
{
    function curl_init($url = null) {
        return $url === null ? \curl_init() : \curl_init(\mpyw\f\replace_url($url));
    }
    function curl_setopt($ch, int $key, $value) {
        \curl_setopt($ch, $key, $key === CURLOPT_URL ? \mpyw\f\replace_url($value) : $value);
    }
    function curl_setopt_array($ch, array $values) {
        foreach ($values as $k => $v) curl_setopt($ch, $k, $v);
    }
}

namespace mpyw\Cowitter\Components
{
    function curl_init($url = null) {
        return $url === null ? \curl_init() : \curl_init(\mpyw\f\replace_url($url));
    }
    function curl_setopt($ch, int $key, $value) {
        \curl_setopt($ch, $key, $key === CURLOPT_URL ? \mpyw\f\replace_url($value) : $value);
    }
    function curl_setopt_array($ch, array $values) {
        foreach ($values as $k => $v) curl_setopt($ch, $k, $v);
    }
}
