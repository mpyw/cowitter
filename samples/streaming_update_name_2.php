<?php

require 'settings.php';

// for infinite foreach loop
set_time_limit(0);

$tc = new TwistCredential(CK, CS, AT, ATS, SN, '');

$user    = TwistRequest::getAuto('user', '', $tc);
$profile = TwistRequest::postAuto('account/update_profile', '', $tc);
$image   = TwistRequest::postAuto('account/update_profile_image', '', $tc);
$tweet   = TwistRequest::post('statuses/update', '', $tc);

$previous_name  = null;
$previous_media = null;

try {
    
    foreach (new TwistIterator($user) as $req) {
        
        try {
            
            // skip except normal tweet status
            if (
                !isset($req->response->text)
                or isset($req->response->retweeted_status
            )) {
                continue;
            }
            
            $text = htmlspecialchars_decode($req->response->text, ENT_QUOTES);
            $sn = $req->response->user->screen_name;
            $me = $req->credential->screenName;
            $url = '';
            
            if (
                preg_match("/[(（]@{$me}[)）]/u", $text)
                and isset($req->response->entities->media[0]
            )) {
                $url = preg_quote(' ' . $req->response->entities->media[0]->url, '/');
                if (
                    $media = @file_get_contents($req->response->entities->media[0]->media_url)
                    and $media !== $previous_media
                ) {
                    $image->setParams(array('image' => base64_encode($media)))->execute();
                    $previous_media = $media;
                    if ($sn !== $me) {
                        $str = ". @{$sn}によって画像が変更されました {$req->response->entities->media[0]->expanded_url}";
                        $tweet->setParams(array('status' => $str))->execute();
                    }
                }
            }
            
            if (preg_match("/^(.*?)[(（]@mpyw[)）]{$url}$/u", $text, $matches)) {
                $matches[1] = trim($matches[1]);
                if ($matches[1] === '' or $matches[1] === $previous_name) {
                    continue 2;
                }
                $prof->setParams(array('name' => $matches[1]))->execute();
                $previous_name = $matches[1];
                if ($sn !== $me) {
                    $str = ". @{$sn}のせいで{$matches[1]}になりました";
                    $tweet->setParams(array('status' => $str))->execute();
                }
            }
            
        } catch (TwistException $e) { }
        
    }
    
} catch (TwistException $e) {
    
    var_dump($e->__toString());
    
}