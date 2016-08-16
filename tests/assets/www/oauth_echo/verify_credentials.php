<?php

switch (true) {
    case !isset($_SERVER['HTTP_X_AUTH_SERVICE_PROVIDER']):
    case $_SERVER['HTTP_X_AUTH_SERVICE_PROVIDER'] !== 'https://api.twitter.com/1.1/account/verify_credentials.json':
    case !isset($_SERVER['HTTP_X_VERIFY_CREDENTIALS_AUTHORIZATION']):
        header('Content-Type: application/json', true, 400);
        exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
}

$oauth_params = array_map('urldecode', parse_ini_string(str_replace(', ', "\n", substr(
    filter_input(INPUT_SERVER, 'HTTP_X_VERIFY_CREDENTIALS_AUTHORIZATION'),
    strlen('OAuth ')
))));
unset($oauth_params['realm']);

if (!isset($oauth_params['oauth_signature'])) {
    header('Content-Type: application/json', true, 400);
    exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
}
$actual_signature = $oauth_params['oauth_signature'];
unset($oauth_params['oauth_signature']);

$base = $oauth_params;
$key = ['cs', isset($oauth_params['oauth_token']) ? 'ts' : ''];

uksort($base, 'strnatcmp');
$expected_signature = base64_encode(hash_hmac(
    'sha1',
    implode('&', array_map('rawurlencode', [
        'GET',
        'https://api.twitter.com/1.1/account/verify_credentials.json',
        http_build_query($base, '', '&', PHP_QUERY_RFC3986)
    ])),
    implode('&', array_map('rawurlencode', $key)),
    true
));

if ($expected_signature !== $actual_signature) {
    header('Content-Type: application/json', true, 400);
    exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
}

echo '{"id_str":"114514"}';
