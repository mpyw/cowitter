<?php

require '../settings_for_tests_and_examples.php';
@session_start();

try {
    
    // Get request_token
    if (!isset($_SESSION['to'])) {
        $to = new TwistOAuth(new TwistCredential(CK, CS, '', ''));
        $to->postAuto('oauth/request_token');
        $_SESSION['to'] = $to;
        header('Location: ' . $_SESSION['to']->credential->getAuthorizeURL());
        exit;
    }
    
    // Get access_token
    if (!isset($_SESSION['authed'])) {
        try {
            if (isset($_GET['oauth_verifier'])) {
                $_SESSION['to']->credential->setVerifier($_GET['oauth_verifier']);
            }
            $_SESSION['to']->postAuto('oauth/access_token');
            $_SESSION['authed'] = true;
            $messages[] = "Hello, @{$_SESSION['to']->credential->screenName}!!";
        } catch (TwistException $e) {
            unset($_SESSION['to']);
            throw $e;
        }
    }
    
    // Tweet
    if (isset($_POST['tweet'])) {
        $response = $_SESSION['to']->postAuto('statuses/update', array('status' => $_POST['tweet']));
        $messages[] = "Tweeted: {$response->text}";
    }

} catch (TwistException $e) {
    
    // Set error message
    $messages[] = $e->getMessage();
    
}

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
  <title>TEST</title>
</head>
<body>
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