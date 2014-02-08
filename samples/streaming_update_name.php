<?php

require 'settings.php';

// for infinite foreach loop
set_time_limit(0);

try {
    
    $tc = new TwistCredential(CK, CS, AT, ATS, SN, '');
    $ust = TwistRequest::getAuto('user', '', $tc);
    $upd = TwistRequest::post('account/update_profile', '', $tc);  
    foreach (new TwistIterator($ust) as $ust) {
        if (!isset($ust->response->text)) {
            continue;
        }
        $text = htmlspecialchars_decode($ust->response->text, ENT_QUOTES);
        // [...(@screen_name)]
        if (preg_match("/^(.*?)\(@{$tc->screenName}\)$/s", $text, $matches)) {
            $upd->setParams(array('name' => $matches[1]))->execute();
        }
    }
    
} catch (TwistException $e) {
    
    var_dump($e->__toString());
    
}