<?php

require '../settings_for_tests_and_examples.php';

// for infinite foreach loop
set_time_limit(0);

$tc      = new TwistCredential(CK, CS, AT, ATS, SN, '');
$profile = TwistRequest::postAuto('account/update_profile', '', $tc);
$tweet   = TwistRequest::post('statuses/update', '', $tc);

$previous_name  = null;

try {
    
    foreach (new TwistIterator(TwistRequest::getAuto('user', '', $tc)) as $ust) {
        
        try {
            
            // skip except normal tweet status
            if (
                !isset($ust->response->text)
                or isset($ust->response->retweeted_status
            )) {
                continue;
            }
            
            $text = htmlspecialchars_decode($ust->response->text, ENT_QUOTES);
            $sn = $ust->response->user->screen_name;
            $me = $ust->credential->screenName;
            
            if (preg_match("/^(.*?)[(（]@{$me}[)）]$/u", $text, $matches)) {
                $matches[1] = trim($matches[1]);
                if ($matches[1] === '' or $matches[1] === $previous_name) {
                    continue 2;
                }
                $profile->setParams(array('name' => $matches[1]))->execute();
                $previous_name = $matches[1];
                if ($sn !== $me) {
                    $str = ". @{$sn}のせいで{$matches[1]}になりました\n";
                    $tweet->setParams(array('status' => $str))->execute();
                    echo $str;
                } else {
                    echo "{$matches[1]}になりました\n";
                }
            }
            
        } catch (TwistException $e) {
            
            echo $e . "\n";
            
        }
        
    }
    
} catch (TwistException $e) {
    
    echo $e . "\n";
    
}