# 03: Example: Sign in with Twitter

Unfortunately, you CANNOT use `localhost` for `oauth_callback` URL...  
Use `127.0.0.1` instead.

1. Create and write down the following codes in your editor.
2. Execute the command `php -S 127.0.0.1:8080`.
3. Access [http://127.0.0.1:8080/](http://127.0.0.1:8080/) using your web browser.

## bootstrap.php

```html+php
<?php

// Load libraries
require __DIR__ . '/vendor/autoload.php';

// Start session
session_start();

// You MUST apply this function when you show raw text in HTML contexts.
// However, tweet texts are already escaped by Twitter, therefore the option
//   $double_encode = false
// is required for them.
function h($str, $double_encode = true)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', $double_encode);
}

// Redirect unlogined user to login page
function require_logined_session()
{
    if (!isset($_SESSION['state']) || $_SESSION['state'] !== 'logined') {
        header('Location: /login.php');
        exit;
    }
}

// Redirect logined user to index page
function require_unlogined_session()
{
    if (isset($_SESSION['state']) && $_SESSION['state'] === 'logined') {
        header('Location: /');
        exit;
    }
}
```

## login.php

```html+php
<?php

// Booting
require __DIR__ . '/bootstrap.php';

// Redirect logined user to index page
require_unlogined_session();

// Make an alias "Client" instead of "mpyw\Cowitter\Client"
use mpyw\Cowitter\Client;

try {

    if (!isset($_SESSION['state'])) {

        /* User is completely unlogined */

        // Create a client object
        $_SESSION['client'] = new Client([
            'YOUR CONSUMER_KEY',
            'YOUR CONSUMER_SECRET',
        ]);

        // Update it with request_token (oauth_callback is http://127.0.0.1:8080/login.php)
        $_SESSION['client'] = $_SESSION['client']->oauthForRequestToken('http://127.0.0.1:8080/login.php');

        // Change state
        $_SESSION['state'] = 'pending';

        // Redirect to Twitter
        header("Location: {$_SESSION['client']->getAuthorizeUrl()}");
        exit;

    } else {

        /* User is unlogined, but pending access_token */

        // Update it with access_token (Using $_GET['oauth_verifier'] returned from Twitter)
        $_SESSION['client'] = $_SESSION['client']->oauthForAccessToken(filter_input(INPUT_GET, 'oauth_verifier'));

        // Change state
        $_SESSION['state'] = 'logined';

        // Redirect to index page
        header('Location: /');
        exit;

    }

} catch (\RuntimeException $e) {

    // Destroy session
    session_destroy();

    // "500 Internal Server Error"
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    exit($e->getMessage());

}
```

## index.php

```html+php
<?php

// Booting
require __DIR__ . '/bootstrap.php';

// Redirect unlogined user to login page
require_logined_session();

// Make an alias "Client" instead of "mpyw\Cowitter\Client"
use mpyw\Cowitter\Client;

// Assign $_POST['tweet_text'] value
$tweet_text = (string)filter_input(INPUT_POST, 'tweet_text');

// Tweet only if $_POST['tweet_text'] is not empty
if ($tweet_text !== '') {
    try {
        $_SESSION['client']->post('statuses/update', [
            'status' => $tweet_text,
        ]);
    } catch (\RuntimeException $e) {
        $errors[] = $e->getMessage();
    }
}

// Fetch tweets on your home timeline
try {
    $statuses = $_SESSION['client']->get('statuses/home_timeline');
} catch (\RuntimeException $e) {
    $errors[] = $e->getMessage();
}

?>
<!DOCTYPE html>
<meta charset="UTF-8">
<title>Example</title>

<?php if (!empty($errors)): ?>
<section>
    <h1 style="color: red;">Error ocurred!!</h1>
    <ul>
<?php foreach ($errors as $error): ?>
        <li><?=h($error)?></li>
<?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section>
    <h1>Tweet Form</h1>
    <form method="post" action="">
        <input type="text" name="tweet_text">
        <input type="submit" value="Tweet!">
    </form>
</section>

<?php if (!empty($statuses)): ?>
<section>
    <h1>Your Home Timeline</h1>
<?php foreach ($statuses as $status): ?>
    <ul>
        <li>
            <div style="font-weight: bold;">@<?=h($status->user->screen_name)?> - <?=h($status->user->name)?>:</div>
            <div style="color: #848484"><?=h($status->text, false)?></div>
        </li>
    </ul>
<?php endforeach; ?>
</section>
<?php endif; ?>
```
