<?php

header('Content-Type: application/json; charset=UTF-8', true, 401);

?>
{
    "request" : "\/1.1\/statuses\/user_timeline.json?screen_name=******",
    "error"   : "Not authorized."
}
