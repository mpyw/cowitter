<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

// for infinite foreach loop
set_time_limit(30);

try {
    
    $tc = new TwistCredential(CK, CS, AT, ATS);
    $ust = TwistRequest::getAuto('user', '', $tc);
    $spl = TwistRequest::postAuto('statuses/filter', 'track=youtube', $tc);
    foreach (new TwistIterator($ust, $spl) as $req) {
        if (!isset($req->response->text)) {
            continue;
        }
        var_dump($req->response->text);
    }
    
} catch (TwistException $e) {
    
    var_dump($e->__toString());
    
}