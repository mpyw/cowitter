<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

// for infinite foreach loop
set_time_limit(0);

try {
    
    $tc = new TwistCredential(CK, CS, AT, ATS);
    $ust = TwistRequest::getAuto('user', '', $tc);
    $you = TwistRequest::postAuto('statuses/filter', 'track=youtube', $tc);
    foreach (new TwistIterator($ust, $you) as $req) {
        if (!isset($req->response->text)) {
            continue;
        }
        var_dump($req->response->text);
    }
    
} catch (TwistException $e) {
    
    var_dump($e->__toString());
    
}