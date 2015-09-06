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
require 'TwistOAuth.phar';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Set default HTTP status code.
$code = 200;

// Set your timezone.
date_default_timezone_set('Asia/Tokyo');

try {

    // Generate your TwistOAuth object.
    $to = new TwistOAuth('CK', 'CS', 'AT', 'AS');

    // Get tweets on your home timeline within 5.
    // This method may throw TwistException.
    $statuses = $to->get('statuses/home_timeline', array('count' => 5));

} catch (TwistException $e) {

    // Set error message.
    $error = $e->getMessage();

    // Overwrite HTTP status code.
    // The exception code will be zero when it thrown before accessing Twitter, we need to change it into 500.
    $code = $e->getCode() ?: 500;

}

// Send charset and HTTP status code to your browser.
header('Content-Type: text/html; charset=utf-8', true, $code);

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Your home timeline</h1>
<?php if (isset($error)): ?>
  <p style="color:red;"><?=h($error)?></p>
<?php endif; ?>
<?php if (!empty($statuses)): ?>
<?php foreach ($statuses as $status): ?>
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
require 'TwistOAuth.phar';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Set default HTTP status code.
$code = 200;

// Get user input.
// (I recommend you not to use $_POST. Use filter_input instead.)
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

        // Set error message.
        $message = array('red', $e->getMessage());

        // Overwrite HTTP status code.
        // The exception code will be zero when it thrown before accessing Twitter, we need to change it into 500.
        $code = $e->getCode() ?: 500;

    }

}

// Send charset and HTTP status code to your browser.
header('Content-Type: text/html; charset=utf-8', true, $code);

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Update your status</h1>
  <form action="" method="post">
    <input type="text" name="text" value="<?=h($text)?>">
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

#### login.php

```html+php
<?php

// Load this library.
require 'TwistOAuth.phar';

// Start session.
@session_start();

function redirect_to_main_page() {
    $url = 'http://127.0.0.1/my_twitter_app/main.php';
    header("Location: $url");
    header('Content-Type: text/plain; charset=utf-8');
    exit("Redirecting to $url ...");
}

// If user is already logined, redirect to the main page.
if (isset($_SESSION['logined'])) {
    redirect_to_main_page();
}

try {

    if (!isset($_SESSION['to'])) { /* First Access */

        // Initialize a TwistOAuth object, then reinitialize with request_token.
        $_SESSION['to'] = new TwistOAuth('CK', 'CS');
        $_SESSION['to'] = $_SESSION['to']->renewWithRequestToken('http://127.0.0.1/my_twitter_app/login.php');

        // Redirect to Twitter.
        header("Location: {$_SESSION['to']->getAuthenticateUrl()}");
        header('Content-Type: text/plain; charset=utf-8');
        exit("Redirecting to {$_SESSION['to']->getAuthenticateUrl()} ...");

    } else { /* Redirected From Twitter */

        // Reinitialize with access_token using oauth_verifier, then set login flag.
        $_SESSION['to'] = $_SESSION['to']->renewWithAccessToken(filter_input(INPUT_GET, 'oauth_verifier'));
        $_SESSION['logined'] = true;

        // Regenerate session id for security reasons.
        session_regenerate_id(true); /* IMPORTANT */

        // Redirect to the main page.
        redirect_to_main_page();

    }

} catch (TwistException $e) { /* Error */

    // Clear session.
    $_SESSION = array();

    // Send HTTP status code and display error message as text. (not HTML)
    // The exception code will be zero when it thrown before accessing Twitter, we need to change it into 500.
    header('Content-Type: text/plain; charset=utf-8', true, $e->getCode() ?: 500);
    exit($e->getMessage());

}
```

#### main.php

```html+php
<?php

// Load this library.
require 'TwistOAuth.phar';

// Prepare simple wrapper function for htmlspecialchars.
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Start session.
@session_start();

// If user is not logined, redirect to the login page.
if (!isset($_SESSION['logined'])) {
    $url = 'http://127.0.0.1/my_twitter_app/login.php';
    header("Location: $url");
    header('Content-Type: text/plain; charset=utf-8');
    exit("Redirecting to $url ...");
}

// Set default HTTP status code.
$code = 200;

// Get user input.
// (I recommend you not to use $_POST. Use filter_input instead.)
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

        // Set error message.
        $message = array('red', $e->getMessage());

        // Overwrite HTTP status code.
        // The exception code will be zero when it thrown before accessing Twitter, we need to change it into 500.
        $code = $e->getCode() ?: 500;

    }

}

// Send charset and HTTP status code to your browser.
header('Content-Type: text/html; charset=utf-8', true, $code);

?>
<!DOCTYPE html>
<html>
<body>
  <h1>Update your status</h1>
  <form action="" method="post">
    <input type="text" name="text" value="<?=h($text)?>">
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
$to = TwistOAuth::login('CK', 'CS', 'screen_name', 'password');
```

## Level-3: Advanced usage

### Walking search results and cursor

#### Get all search results

```php
$statuses = array();
$params = array('q' => 'foobarbaz');
while ($params) {
    $result = $to->get('search/tweets', $params);
    $statuses = array_merge($statuses, $result->statuses);
    $params =
        isset($result->search_metadata->next_results) ?
        substr($result->search_metadata->next_results, 1) :
        null
    ;
}
```

**ATTENTION:**  
You may be going to be over API limit.

**WARNING:**  
Do not directly pass `$_GET`. The following snippet has a serious **vulnerability**.

```html+php
<?php
$result = $to->get('search/tweets', $_GET);
?>
...
<a href="<?=h($result->search_metadata->next_results)?>">Next</a>
```

Query string on attacks will be like:

```text
?@q=/etc/passwd
```

Then, your `/etc/passwd` will be stolen...

#### Get all friend ids

```php
$ids = array();
$cursor = '-1';
do {
    $result = $to->get('friends/ids', array(
        'cursor' => $cursor,
        'stringify_ids' => '1',
    ));
    $ids = array_merge($ids, $result->ids);
} while ($cursor = $result->next_cusror_str);
```

#### Get all friend and following to calculate "friend only" ids to unfollow them

Basic implemention (Slow):

```php
$get_all_ids = function ($endpoint, array $params = array()) use ($to) {
    $ids = array();
    $params['cursor'] = '-1';
    $params['stringify_ids'] = '1';
    do {
        $result = $to->get($endpoint, $params);
        $ids = array_merge($ids, $result->ids);
    } while ($cursor = $result->next_cusror_str);
    return $ids;
};
$friends = $get_all_ids('friends/ids');
$followers = $get_all_ids('followers/ids');
$friends_only = array_diff($friends, $followers);
foreach ($friends_only as $id) {
    $to->post('friendships/destroy', array('user_id' => $id));
}
```

**Advanced implemention (Faster):**

```php
$get_all_ids_flipped = function ($endpoint, array $params = array()) use ($to) {
    $ids = array();
    $params['cursor'] = '-1';
    $params['stringify_ids'] = '1';
    do {
        $result = $to->get($endpoint, $params);
        $ids += array_flip($result->ids);
    } while ($cursor = $result->next_cusror_str);
    return $ids;
};
$friends = $get_all_ids('friends/ids');
$followers = $get_all_ids('followers/ids');
$friends_only = array_diff_key($friends, $followers);
foreach ($friends_only as $id => $_) {
    $to->post('friendships/destroy', array('user_id' => $id));
}
```

### Access media in direct messages

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

#### Recommended

```php
$media_id = $to->postMultipart('media/upload', array('@media' => 'test.jpg'))
               ->media_id_string;
$to->post('statuses/update', array(
    'status' => 'test',
    'media_ids' => $media_id,
));
```

#### Deprecated

```php
$to->postMultipart('statuses/update_with_media', array(
    'status' => 'test',
    '@media[]' => 'test.jpg',
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
    'media_ids' => implode(',', $media_ids),
));
```

### Update tweets with an small mp4 video

```php
$filename = 'test.mp4';
$info = $to->post('media/upload', array(
    'command' => 'INIT',
    'media_type' => 'video/mp4',
    'total_bytes' => filesize($filename),
));
$to->postMultipart('media/upload', array(
    'command' => 'APPEND',
    'media_id' => $info->media_id_string,
    'segment_index' => 0,
    '@media' => $filename,
));
$to->post('media/upload', array(
    'command' => 'FINALIZE',
    'media_id' => $info->media_id_string,
));
$to->post('statuses/update', array(
    'status' => 'test',
    'media_ids' => $info->media_id_string,
));
```

### Update tweets with a large mp4 video that is divided into some segments

```php
$file = new SplFileObject('test.mp4', 'rb');
$whole_size = $file->getSize();
$chunk_size = (int)($whole_size / 3); // divided into 3 or 4 segments
$info = $to->post('media/upload', array(
    'command' => 'INIT',
    'media_type' => 'video/mp4',
    'total_bytes' => $whole_size,
));
$curls = array();
for ($i = 0; '' !== $buffer = $file->fread($chunk_size); ++$i) {
    $curls[] = $to->curlPostMultipart('media/upload', array(
        'command' => 'APPEND',
        'media_id' => $info->media_id_string,
        'segment_index' => $i,
        '#media' => $buffer,
    ));
}
$to->curlMultiExec($curls, true);
$to->post('media/upload', array(
    'command' => 'FINALIZE',
    'media_id' => $info->media_id_string,
));
$to->post('statuses/update', array(
    'status' => 'test',
    'media_ids' => $info->media_id_string,
));
```

### Upload an image into [img.ly](http://img.ly)

```php
$to->postMultipartOut('http://img.ly/api/2/upload.json', array(
    'message' => 'test',
    '@media' => 'test.png',
));
```

### Simple CLI application for your own streaming

#### Display tweets on user streaming

```php
// Disable timeout.
set_time_limit(0);

// Finish all buffering.
while (ob_get_level()) {
    ob_end_clean();
}

// Start streaming.
$to->streaming('user', function ($status) {
    // Treat only tweets.
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        flush(); // Required if running not on Command Line but on Apache
    }
});
```

#### At once favorite and retweet tweets containing specified keywords

```php
// Disable timeout.
set_time_limit(0);

// Finish all buffering.
while (ob_get_level()) {
    ob_end_clean();
}

// Start streaming.
$to->streaming(
    'statuses/filter',
    function ($status) use ($to) { // Import $to.
        // Treat only tweets by other users.
        if (isset($status->text) && $status->user->screen_name !== 'YOUR SCREEN_NAME') {
            foreach ($to->curlMultiExec(array(
                'Favorite' => $to->curlPost('favorites/create', array('id' => $status->id_str)),
                'Retweet'  => $to->curlPost("statuses/retweet/{$status->id_str}"),
            )) as $action => $e) {
                printf("%s(@%s - %s): %s\n",
                    $action,
                    $status->user->screen_name,
                    $status->id_str,
                    $e instanceof TwistException ? $e->getMessage() : 'Success'
                );
            }
            flush(); // Required if running not on Command Line but on Apache
        }
    },
    array('track' => 'foo,bar,baz')
);
```
