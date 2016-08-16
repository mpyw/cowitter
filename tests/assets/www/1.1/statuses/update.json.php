<?php

require __DIR__ . '/../../../dummy_oauth.php';
verify_oauth_1a();

?>
{"text":<?=json_encode(filter_input(INPUT_POST, 'status'))?>}
