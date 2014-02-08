<?php

require 'settings.php';

session_start();

try {
    
    // get request_token
    if (!isset($_SESSION['tc'])) {
        $tc = new TwistCredential(CK, CS, '', '');
        TwistRequest::postAuto('oauth/request_token', '', $tc)->execute();
        $_SESSION['tc'] = $tc;
        header('Location: ' . $tc->getAuthorizeURL());
        exit;
    }
    
    $tc = $_SESSION['tc'];
    
    // get access_token
    if (!isset($_SESSION['authed'])) {
        try {
            if (isset($_GET['oauth_verifier'])) {
                $tc->setVerifier($_GET['oauth_verifier']);
            }
            TwistRequest::getAuto('oauth/access_token', '', $tc)->execute();
            $_SESSION['authed'] = true;
            $messages[] = "Hello, @{$tc->screenName}!!";
        } catch (TwistException $e) {
            unset($_SESSION['tc']);
            throw $e;
        }
    }
    
    // tweet
    if (isset($_POST['tweet'])) {
        $params = array('status' => $_POST['tweet']);
        $result = TwistRequest::postAuto('statuses/update', $params, $tc)->execute()->response;
        $messages[] = "Tweeted: {$result->text}";
    }

} catch (TwistException $e) { }

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
<title>TEST</title>
</head>
<body>
<?php if (isset($e)): ?>
<p style="color:red;"><?php echo $e; ?></p>
<?php endif; ?>
<?php if (!empty($messages)): ?>
<ul>
<?php foreach ($messages as $msg): ?>
<li><?php echo $msg; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php if (isset($_SESSION['authed'])): ?>
<p>
<form method="post" action="">
<input type="text" name="tweet" value="">
<input type="submit" value="TWEET">
</form>
</p>
<?php endif; ?>
</body>
</html>