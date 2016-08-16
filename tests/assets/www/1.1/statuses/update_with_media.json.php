<?php

require __DIR__ . '/../../../dummy_oauth.php';
verify_oauth_1a();

header('Content-Type: application/json');

?>
{
    "status":<?=json_encode(filter_input(INPUT_POST, 'status'))?>,
    "media[]":<?=json_encode(sha1_file($_FILES['media']['tmp_name'][0]))?>
}
