<?php

require __DIR__ . '/../../dummy_oauth.php';

header('Content-Type: application/json');

verify_oauth_2_bearer();

?>
{"id_str":"114514"}
