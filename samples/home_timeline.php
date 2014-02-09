<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

$to = new TwistOAuth(new TwistCredential(CK, CS, AT, ATS));

// [TwistOAuth] Auto Exception Throw Mode
try {
    $response = $to->getAuto('statuses/home_timeline', array('count' => 3));
    foreach ($response as $status) {
        var_dump($status->text);
    }
} catch (TwistException $e) {
    var_dump($e->__toString());
}

echo "\n";

// [TwistOAuth] Manual Mode
$response = $to->get('statuses/home_timeline', 'count=3');
if (!($response instanceof TwistException)) {
    foreach ($response as $status) {
        var_dump($status->text);
    }
} else {
    var_dump($result->__toString());
}

echo "\n";

$tc = new TwistCredential(CK, CS, AT, ATS);

// [TwistRequest] Auto Exception Throw Mode
try {
    $response = TwistRequest::getAuto('statuses/home_timeline', 'count=3', $tc)->execute();
    foreach ($response as $status) {
        var_dump($status->text);
    }
} catch (TwistException $e) {
    var_dump($e->__toString());
}

echo "\n";

// [TwistRequest] Manual Mode
$response = TwistRequest::get('statuses/home_timeline?count=3', '', $tc)->execute();
if (!($response instanceof TwistException)) {
    foreach ($response as $status) {
        var_dump($status->text);
    }
} else {
    var_dump($result->__toString());
}
