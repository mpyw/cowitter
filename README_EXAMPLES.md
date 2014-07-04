Examples
========

- `CK` means *consumer\_key*.
- `CS` means *consumer\_secret*.
- `AT` means *access\_token*.
- `AS` means *access\_token\_secret*.

## Level-1: Simple GUI application for your own account

### Display home timeline

```html+php
<?php

// Load this library.
require 'TwistOAuth.php';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Set your timezone.
date_default_timezone_set('Asia/Tokyo');

try {
    
    // Generate your TwistOAuth object.
    $to = new TwistOAuth('CK', 'CS', 'AT', 'AS');
    
    // Get tweets on your home timeline within 5.
    // This method may throw TwistException.
    $statuses = $to->get('statuses/home_timeline', array('count' => 5));
    
} catch (TwistException $e) {
    
    // Catch exception and set error message.
    $error = $e->getMessage();
    
}

// Send charset to your browser.
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Your home timeline</h1>
<?php if (isset($error)): ?>
  <p style="color:red;"><?=h($error)?></p>
<?php endif; ?>
<?php if (!empty($statuses)): ?>
<?php foreach ($statues as $status): ?>
  <p>
    user_id: <?=$status->user->id_str?><br>
    screen_name: @<?=$status->user->screen_name?><br>
    name: <?=h($status->user->name)?><br>
    text: <?=$status->text?><br>
    time: <?=strtotime($status->created_at)?><br>
  </p>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>
```

### Update tweets

```html+php
<?php

// Load this library.
require 'TwistOAuth.php';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Get user input.
// (I recommend you not to use $_POST.)
$text = filter_input(INPUT_POST, 'text');

if ($text !== null) {
    
    try {
        
        // Generate your TwistOAuth object.
        $to = new TwistOAuth('CK', 'CS', 'AT', 'AS');
        
        // Update your status.
        // This method may throw TwistException.
        $to->post('statuses/update', array('status' => $text));
        
        // Set message.
        $message = array('green', 'Successfully tweeted.');
        
        // Clear text.
        $text = '';
        
    } catch (TwistException $e) {
        
        // Catch exception and set error message.
        $message = array('red', $e->getMessage());
        
    }
    
}

// Send charset to your browser.
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Update your status</h1>
  <form action="" method="post">
    <input type="text" value="<?=h($text)?>">
    <input type="submit" value="Tweet">
  </form>
<?php if (isset($message)): ?>
  <p style="color:<?=$message[0]?>;"><?=h($message[1])?></p>
<?php endif; ?>
</body>
</html>
```

## Level-2: Authentication for general users

### OAuth

Basic authentication flow.

#### prepare.php

```html+php
<?php

// Load this library.
require 'TwistOAuth.php';

// Start session.
@session_start();

// If user is already authenticated, redirect to the main page.
if (isset($_SESSION['authed'])) {
    header('Location: http://127.0.0.1/my_twitter_app/main.php');
    exit;
}

try {
    
    // Generate a TwistOAuth object and apply it request_token.
    $to = new TwistOAuth('CK', 'CS');
    $to = $to->renewWithRequestToken();
    
    // Set it into session.
    $_SESSION['to'] = $to;
    
    // Redirect to Twitter.
    header('Location: ' . $to->getAuthenticateUrl());
    
} catch (TwistException $e) {
    
    // Output an error message as plain text.
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
    
}
```

#### callback.php

```html+php
<?php

// Load this library.
require 'TwistOAuth.php';

// Start session.
@session_start();

// If user is already authenticated, redirect to the main page.
if (isset($_SESSION['authed'])) {
    header('Location: http://127.0.0.1/my_twitter_app/main.php');
    exit;
}

try {
        
    // If a TwistOAuth object is not already prepared, throw exception.
    if (!isset($_SESSION['to'])) {
        throw new RuntimeException('Access to prepare.php at first.');
    }
    
    // Apply access_token with oauth_verifier.
    $_SESSION['to'] = $_SESSION['to']->renewWithAccessToken(filter_input(INPUT_GET, 'oauth_verifier'));
    
    // Set authenticated flag.
    $_SESSION['authed'] = true;
    
    // Regenerate session id for security reasons.
    session_regenerate_id(true); /* IMPORTANT */
    
    // Redirect to the main page.
    header('Location: http://127.0.0.1/my_twitter_app/main.php');
    
} catch (RuntimeException $e) { /* TwistException is extended from RuntimeException */
    
    // Refresh session.
    $_SESSION = array();
    
    // Output an error message as plain text.
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
    
}
```

#### main.php

```html+php
<?php

// Load this library.
require 'TwistOAuth.php';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Start session.
@session_start();

// If user is not already authenticated, redirect to the preparation page.
if (isset($_SESSION['authed'])) {
    header('Location: http://127.0.0.1/my_twitter_app/prepare.php');
    exit;
}

// Get user input.
// (I recommend you not to use $_POST.)
$text = filter_input(INPUT_POST, 'text');

if ($text !== null) {
    
    try {
               
        // Update status.
        $_SESSION['to']->post('statuses/update', array('status' => $text));
        
        // Set message.
        $message = array('green', 'Successfully tweeted.');
        
        // Clear text.
        $text = '';
        
    } catch (TwistException $e) {
        
        // Catch exception and set error message.
        $message = array('red', $e->getMessage());
        
    }
    
}

// Send charset to your browser.
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Update your status</h1>
  <form action="" method="post">
    <input type="text" value="<?=h($text)?>">
    <input type="submit" value="Tweet">
  </form>
<?php if (isset($message)): ?>
  <p style="color:<?=$message[0]?>;"><?=h($message[1])?></p>
<?php endif; ?>
</body>
</html>
```

### xAuth

This requires an official API key, such as `Twitter for Android` or `Twitter for iPhone`. **Abusing.**

```php
$to = new TwistOAuth('CK', 'CS');
$to = $to->renewWithAccessTokenX('screen_name', 'password');
```

### Direct OAuth

This requires heavy traffic. **Abusing**.

```php
$to = self::login('CK', 'CS', 'screen_name', 'password');
```

## Level-3: Advanced usage

### Access images in direct messages


#### Raw output

```php
$img = $to->get($url);
header('Content-Type: ' . $img->type);
echo $img->data;
```

#### Data URI output

```php
printf('<img src="%s" alt="">', $to->get($url)->getDataUri());
```

### Update tweets with an image

```php
$to->postMultipart('statuses/update_with_media', array(
    'status' => 'test',
    '@media[]' => 'test.jpg'
));
```

### Update tweets with multiple images

```php
foreach (array('foo.jpg', 'bar.jpg', 'baz.jpg') as $path) {
    $media_ids[] = $to->postMultipart('media/upload', array('@media' => $path))
                      ->media_id_string;
}
$to->post('statuses/update', array(
    'status' => 'test',
    'media_ids' => implode(',', $media_ids)
));
```

### Upload an image into Twitpic

```php
$to->postOutMultipart('http://api.twitpic.com/2/upload.json', array(
    'key' => 'Your Twitpic API key',
    'message' => 'test',
    '@media' => 'test.jpg',
));
```

### Simple CUI application for your own streaming

```php
// Finish all buffering.
while (ob_get_level()) {
    ob_end_clean();
}
// Start streaming.
$to->streaming('user', function($status) {
    // Treat only tweets.
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        flush();
    }
});
```