<?php

require __DIR__ . '/../../../oauth_verify.php';

header('Content-Type: application/json');

?>
{"text":<?=json_encode(filter_input(INPUT_POST, 'status'))?>}
