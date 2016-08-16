<?php

session_start();

if (isset($_SESSION['authenticity_token']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    fwrite(fopen('php://stderr', 'wb'), print_r([$_SESSION, $_POST], true));
    switch (true) {
        case $_POST['authenticity_token'] !== $_SESSION['authenticity_token']:
        case $_POST['session']['username_or_email'] !== 'username':
        case $_POST['session']['password'] !== 'password':
            header('Content-Type: text/plain; charset=UTF-8', true, 400);
            echo 'Failed';
            exit;
    }
    $_SESSION['oauth_verifier'] = bin2hex(openssl_random_pseudo_bytes(32));
    echo "<code>{$_SESSION['oauth_verifier']}</code>";
    exit;
} elseif (!isset($_SESSION['authenticity_token'])) {
    $_SESSION['authenticity_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}

?>
<!DOCTYPE html>
<form method="post" action="">
    <input name="session[username_or_email]" type="text">
    <input name="session[password]" type="text">
    <input name="authenticity_token" type="hidden" value="<?=$_SESSION['authenticity_token']?>">
</form>
