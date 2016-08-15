<?php

namespace mpyw\Cowitter;
function curl_init($url = null) {
    return (object)[
        'options' => [CURLOPT_URL => $url],
        'content' => null,
        'errno' => 0,
        'error' => '',
        'info' => [
            'http_code' => 0,
            'content_type' => null,
            'header_size' => 0,
            'url' => '',
        ],
    ];
}
function curl_exec(\stdClass $ch) {
    return \mpyw\TestOfCowitter\curl_flush_content($ch);
}
function curl_setopt(\stdClass $ch, int $key, $value) {
    $ch->options[$key] = $value;
}
function curl_setopt_array(\stdClass $ch, array $values) {
    foreach ($values as $key => $value) {
        $ch->options[$key] = $value;
    }
}
function curl_getinfo(\stdClass $ch, int $type = null) {
    if ($type === null) {
        return $ch->info;
    }
    static $map = [
        CURLINFO_HTTP_CODE => 'http_code',
        CURLINFO_CONTENT_TYPE => 'content_type',
        CURLINFO_HEADER_SIZE => 'header_size',
        CURLINFO_EFFECTIVE_URL => 'url',
    ];
    return $ch->info[$map[$type]];
}
function curl_errno(\stdClass $ch) {
    return $ch->errno;
}
function curl_error(\stdClass $ch) {
    return $ch->error;
}

namespace mpyw\Cowitter\Traits;
function curl_init($url = null) {
    return (object)[
        'options' => [CURLOPT_URL => $url],
        'content' => null,
        'errno' => 0,
        'error' => '',
        'info' => [
            'http_code' => 0,
            'content_type' => null,
            'header_size' => 0,
            'url' => '',
        ],
    ];
}
function curl_exec(\stdClass $ch) {
    return \mpyw\TestOfCowitter\curl_flush_content($ch);
}
function curl_setopt(\stdClass $ch, int $key, $value) {
    $ch->options[$key] = $value;
}
function curl_setopt_array(\stdClass $ch, array $values) {
    foreach ($values as $key => $value) {
        $ch->options[$key] = $value;
    }
}
function curl_getinfo(\stdClass $ch, int $type = null) {
    if ($type === null) {
        return $ch->info;
    }
    static $map = [
        CURLINFO_HTTP_CODE => 'http_code',
        CURLINFO_CONTENT_TYPE => 'content_type',
        CURLINFO_HEADER_SIZE => 'header_size',
        CURLINFO_EFFECTIVE_URL => 'url',
    ];
    return $ch->info[$map[$type]];
}
function curl_errno(\stdClass $ch) {
    return $ch->errno;
}
function curl_error(\stdClass $ch) {
    return $ch->error;
}

namespace mpyw\Cowitter\Components;
function curl_init($url = null) {
    return (object)[
        'options' => [CURLOPT_URL => $url],
        'content' => null,
        'errno' => 0,
        'error' => '',
        'info' => [
            'http_code' => 0,
            'content_type' => null,
            'header_size' => 0,
            'url' => '',
        ],
    ];
}
function curl_exec(\stdClass $ch) {
    return \mpyw\TestOfCowitter\curl_flush_content($ch);
}
function curl_setopt(\stdClass $ch, int $key, $value) {
    $ch->options[$key] = $value;
}
function curl_setopt_array(\stdClass $ch, array $values) {
    foreach ($values as $key => $value) {
        $ch->options[$key] = $value;
    }
}
function curl_getinfo(\stdClass $ch, int $type = null) {
    if ($type === null) {
        return $ch->info;
    }
    static $map = [
        CURLINFO_HTTP_CODE => 'http_code',
        CURLINFO_CONTENT_TYPE => 'content_type',
        CURLINFO_HEADER_SIZE => 'header_size',
        CURLINFO_EFFECTIVE_URL => 'url',
    ];
    return $ch->info[$map[$type]];
}
function curl_errno(\stdClass $ch) {
    return $ch->errno;
}
function curl_error(\stdClass $ch) {
    return $ch->error;
}

namespace mpyw\Cowitter\Helpers;
function curl_init($url = null) {
    return (object)[
        'options' => [CURLOPT_URL => $url],
        'content' => null,
        'errno' => 0,
        'error' => '',
        'info' => [
            'http_code' => 0,
            'content_type' => null,
            'header_size' => 0,
            'url' => '',
        ],
    ];
}
function curl_exec(\stdClass $ch) {
    return \mpyw\TestOfCowitter\curl_flush_content($ch);
}
function curl_setopt(\stdClass $ch, int $key, $value) {
    $ch->options[$key] = $value;
}
function curl_setopt_array(\stdClass $ch, array $values) {
    foreach ($values as $key => $value) {
        $ch->options[$key] = $value;
    }
}
function curl_getinfo(\stdClass $ch, int $type = null) {
    if ($type === null) {
        return $ch->info;
    }
    static $map = [
        CURLINFO_HTTP_CODE => 'http_code',
        CURLINFO_CONTENT_TYPE => 'content_type',
        CURLINFO_HEADER_SIZE => 'header_size',
        CURLINFO_EFFECTIVE_URL => 'url',
    ];
    return $ch->info[$map[$type]];
}
function curl_errno(\stdClass $ch) {
    return $ch->errno;
}
function curl_error(\stdClass $ch) {
    return $ch->error;
}
