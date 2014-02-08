<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

$tc = new TwistCredential(CK, CS, AT, ATS);

// Auto Exception Throw Mode
try {
    $req = TwistRequest::getAuto(
        'statuses/home_timeline',
        array('count' => 3),
        $tc
    );
    foreach ($req->execute()->response as $status) {
        var_dump($status->text);
    }
} catch (TwistException $e) {
    var_dump($e->__toString());
}

echo "\n";

// Manual Mode
$req = TwistRequest::get(
    'statuses/home_timeline',
    'count=3',
    $tc
);
$result = $req->execute()->response;
if (!($result instanceof TwistException)) {
    foreach ($result as $status) {
        var_dump($status->text);
    }
} else {
    var_dump($result->__toString());
}
