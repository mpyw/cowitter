<?php

require __DIR__ . '/../../../dummy_oauth.php';
verify_oauth_1a('https://stream.twitter.com');

header('Content-Type: application/json');
header('Transfer-Encoding: chunked');

function write($msg)
{
    $len = base_convert(strlen($msg), 10, 16);
    echo "$len\r\n$msg\r\n";
}
write('{"text":"hello"}' . "\n");
write('{"text":"he');
write('llo"}' . "\n");
write("\n");
write('{"text":"hello"}');
write("\n");
write('');
