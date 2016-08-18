<?php

require __DIR__ . '/../../dummy_oauth.php';

header('Content-Type: application/x-www-form-urlencoded');

$oauth_params = array_map('urldecode', parse_ini_string(str_replace(', ', "\n", substr(
    filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION'),
    strlen('OAuth ')
))));

if (isset($_POST['x_auth_username'], $_POST['x_auth_password'], $_POST['x_auth_mode']) && $_POST['x_auth_mode'] === 'client_auth') {
    if ($_POST['x_auth_username'] !== 'username') {
        exit('invalid username');
    }
    if ($_POST['x_auth_password'] !== 'password') {
        exit('invalid password');
    }
} elseif (!isset($oauth_params['oauth_verifier']) || $oauth_params['oauth_verifier'] !== '1919810') {
    exit('oauth_verifier verification failed');
}

verify_oauth_1a();

echo "oauth_token=t&oauth_token_secret=ts";
