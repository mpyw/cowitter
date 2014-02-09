<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

// [TwistOAuth] Auto Authorization
try {
    
    $to = new TwistOAuth(new TwistCredential(CK, CS, '', '', SN, PW));
    $result = $to->postAuto('statuses/update', 'status=test');
    var_dump($result);
    
} catch (TwistException $e) {

    var_dump($e->__toString());
    
}

// [TwistRequest] Manual Authorizatin
try {
    
    $tc = new TwistCredential(CK, CS, '', '', SN, PW);
    TwistRequest::login($tc)->execute();
    $result = TwistRequest::postAuto('statuses/update', 'status=test', $tc)->execute();
    var_dump($result);
    
} catch (TwistException $e) {

    var_dump($e->__toString());
    
}
