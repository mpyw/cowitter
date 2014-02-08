<?php

require 'settings.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    
    $tc = new TwistCredential(CK, CS);
    $tc->setScreenName(SN)->setPassword(PW);
    TwistRequest::login($tc)->execute();
    $result = TwistRequest::post('statuses/update', 'status=test', $tc)->execute()->response;
    var_dump($result);
    
} catch (TwistException $e) {

    var_dump($e->__toString());
    
}
