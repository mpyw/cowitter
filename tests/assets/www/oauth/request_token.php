<?php

require __DIR__ . '/../../dummy_oauth.php';
verify_oauth_1a();

header('Content-Type: application/x-www-form-urlencoded');

?>
oauth_token=t&oauth_token_secret=ts
