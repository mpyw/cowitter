<?php

const CONSUMER_SECRET = 'cs';
const TOKEN_SECRET = 'ts';

function verify_oauth_1a($origin = 'https://api.twitter.com', $send_content_type = true)
{
    if ($send_content_type) {
        header('Content-Type: application/json');
    }

    $oauth_params = array_map('urldecode', parse_ini_string(str_replace(', ', "\n", substr(
        filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION'),
        strlen('OAuth ')
    ))));

    if (!isset($oauth_params['oauth_signature'])) {
        exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
    }

    $actual_signature = $oauth_params['oauth_signature'];
    unset($oauth_params['oauth_signature']);

    if (strpos(filter_input(INPUT_SERVER, 'CONTENT_TYPE'), 'multipart/form-data') !== 0) {
        $additional_params = $_POST + $_GET;
    } else {
        $additional_params = [];
    }

    $base = $oauth_params + $additional_params;
    $key = [CONSUMER_SECRET, TOKEN_SECRET];

    uksort($base, 'strnatcmp');
    $expected_signature = base64_encode(hash_hmac(
        'sha1',
        implode('&', array_map('rawurlencode', [
            filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
            $origin . substr(parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH), 0, -strlen('.php')),
            http_build_query($base, '', '&', PHP_QUERY_RFC3986)
        ])),
        implode('&', array_map('rawurlencode', $key)),
        true
    ));
    
    if ($expected_signature !== $actual_signature) {
        exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
    }
}
