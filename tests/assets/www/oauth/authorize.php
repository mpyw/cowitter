<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch (true) {
        case $_POST['authenticity_token'] !== '114514364364':
        case $_POST['session']['username_or_email'] !== 'username':
        case $_POST['session']['password'] !== 'password':
            header('Content-Type: text/plain; charset=UTF-8', true, 400);
            echo 'Failed';
            exit;
    }
    echo "<code>1919810</code>";
    exit;
}

?>
<!DOCTYPE html>
<form method="post" action="">
    <input name="session[username_or_email]" type="text">
    <input name="session[password]" type="text">
    <input name="authenticity_token" type="hidden" value="114514364364">
</form>
