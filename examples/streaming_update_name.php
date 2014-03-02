<?php

require '../settings_for_tests_and_examples.php';

// for infinite foreach loop
set_time_limit(0);

try {
    
    $tc = new TwistCredential(CK, CS, AT, ATS, SN, '');
    $upd = TwistRequest::postAuto('account/update_profile', '', $tc);  
    foreach (new TwistIterator(TwistRequest::getAuto('user', '', $tc)) as $ust) {
        // skip except normal tweet status
        if (
            !isset($ust->response->text)
            or isset($ust->response->retweeted_status
        )) {
            continue;
        }
        $text = htmlspecialchars_decode($ust->response->text, ENT_QUOTES);
        // [...(@screen_name)]
        if (preg_match("/^(.*?)\(@{$tc->screenName}\)$/s", $text, $matches)) {
            try {
                $upd->setParams(array('name' => $matches[1]))->execute();
                echo "Changed name into: {$matches[1]}\n";
            } catch (TwistException $e) {
                echo $e . "\n";
            }
        }
    }
    
} catch (TwistException $e) {
    
    echo $e . "\n";
    
}