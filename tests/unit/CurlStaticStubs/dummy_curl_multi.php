<?php

namespace mpyw\Co\Internal;

function curl_multi_init() {
    return (object)[
        'handles' => [],
    ];
}
function curl_multi_setopt(\stdClass $mh) { }
function curl_multi_add_handle(\stdClass $mh, \stdClass $ch) {
    $mh->handles[spl_object_hash($ch)] = $ch;
}
function curl_multi_remove_handle(\stdClass $mh, \stdClass $ch) {
    unset($mh->handles[spl_object_hash($ch)]);
}
function curl_multi_select(\stdClass $mh) {
    return 0;
}
function curl_multi_exec(\stdClass $mh, &$active) {
    foreach ($mh->handles as $ch) {
        \mpyw\TestOfCowitter\curl_flush_content($ch);
    }
}
function curl_multi_info_read(\stdClass $mh) {
    if (!$mh->handles) {
        return false;
    }
    $ch = array_shift($mh->handles);
    return [
        'msg' => CURLMSG_DONE,
        'result' => CURLE_OK,
        'handle' => $ch,
    ];
}
function curl_multi_getcontent(\stdClass $ch) {
    return $ch->content;
}
