<?php

switch (true) {
    case !isset($_SERVER['HTTP_X_AUTH_SERVICE_PROVIDER']):
    case $_SERVER['HTTP_X_AUTH_SERVICE_PROVIDER'] !== 'https://api.twitter.com/1.1/account/verify_credentials.json':
    case !isset($_SERVER['HTTP_X_VERIFY_CREDENTIALS_AUTHORIZATION']):
        header('Content-Type: application/json', true, 400);
        exit('{"errors":[{"message":"Bad Authentication data","code":215}]}');
}

$headers = ['Authorization: OAuth ' . substr(
    filter_input(INPUT_SERVER, 'HTTP_X_VERIFY_CREDENTIALS_AUTHORIZATION'),
    strlen('OAuth realm="http://api.twitter.com/", ')
)];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://localhost:8081/1.1/account/verify_credentials.json.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_FORBID_REUSE => true,
    CURLOPT_FRESH_CONNECT => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$response = curl_exec($ch);

header('Content-Type: ' . curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
http_response_code(curl_getinfo($ch, CURLINFO_HTTP_CODE));
echo $response;
