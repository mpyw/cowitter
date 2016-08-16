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
        exit;
    }
    if ($_POST['x_auth_password'] !== 'password') {
        exit('invalid password');
        exit;
    }
} elseif (!isset($oauth_params['oauth_verifier']) || $oauth_params['oauth_verifier'] !== '1919810') {
    echo 'oauth_verifier verification failed';
    exit;
}

verify_oauth_1a();
?>
oauth_token=t&oauth_token_secret=ts
