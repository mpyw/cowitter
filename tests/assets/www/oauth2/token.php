<?php

require __DIR__ . '/../../dummy_oauth.php';

header('Content-Type: application/json');

verify_oauth_2_basic();

?>
{"access_token":"t"}
