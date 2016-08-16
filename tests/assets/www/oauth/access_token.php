<?php

require __DIR__ . '/../../dummy_oauth.php';

session_start();

header('Content-Type: application/x-www-form-urlencoded');

$oauth_params = array_map('urldecode', parse_ini_string(str_replace(', ', "\n", substr(
    filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION'),
    strlen('OAuth ')
))));

if (!isset($oauth_params['oauth_verifier'])) {
    echo 'oauth_verifier required for this test';
    exit;
}

verify_oauth_1a();

if ($oauth_params['oauth_verifier'] !== $_SESSION['oauth_verifier']) {
    echo 'oauth_verifier verification failed';
    exit;
}

?>
oauth_token=t&oauth_token_secret=ts
