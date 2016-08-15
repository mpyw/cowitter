<?php

namespace mpyw\TestOfCowitter;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../tests/unit/CurlStubs/autoload.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;

$client = new Client(['x', 'y', 'z', 'w']);

$statuses = [
    '{"id_str":"114514","text":"yj"}',
    ['{"id_str":"364', '364","text":"mur"}']
];
CurlFaker::set('https://userstream.twitter.com/1.1/user.json', [
    ['HTTP/1.1 200 OK'],
    $statuses
]);

var_dump($client->streaming('user', function ($status) use ($statuses) {
    var_dump($status);
    return false;
}));

exit;

CurlFaker::set('https://api.twitter.com/1.1/statuses/update.json', [
    ['HTTP/1.1 200 OK'],
    ['{"id_str":"114514","text":"homo"}']
]);

var_dump($client->post('statuses/update', [
    'status' => 'homo',
]));
