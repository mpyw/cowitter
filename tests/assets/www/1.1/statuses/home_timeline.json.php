<?php

require __DIR__ . '/../../../dummy_oauth.php';
verify_oauth_1a();

header('Content-Type: application/json');

$statuses = array_fill(0, max(1, filter_input(INPUT_GET, 'count')), ['text' => 'a']);
echo json_encode($statuses);
