# 02: Example: Application for your own personal use

## Commandline application example

Tweet 🍣 from your terminal.

1. Create the file `tweet.php` to open your editor.
2. Write down the following code to save.
3. Execute the command `php tweet.php`

```html+php
<?php

// Load libraries
require __DIR__ . '/vendor/autoload.php';

// Make an alias "Client" instead of "mpyw\Cowitter\Client"
use mpyw\Cowitter\Client;

// Create a client object
$client = new Client([
    'YOUR CONSUMER_KEY',
    'YOUR CONSUMER_SECRET',
    'YOUR ACCESS_TOKEN',
    'YOUR ACCESS_TOKEN_SECRET',
]);

try {

    // Send a POST request to Twitter
    $status = $client->post('statuses/update', [
        'status' => 'I like 🍣',
    ]);

    // Report your tweet permalink URL
    echo "Tweeted: https://twitter.com/{$status->user->screen_name}/status/{$status->id_str}\n";

} catch (\RuntimeException $e) {

    // Jump here if an errors has occurred
    echo "Error: {$e->getMessage()}\n";

}
```

Now we used the endpoint [POST statuses/update](https://dev.twitter.com/rest/reference/post/statuses/update).
Interfaces are very intuitive like this.

## Web application example

Tweet and read timeline on your browser.

1. Create the file `index.php` to open your editor.
2. Write down the following code to save.
3. Execute the command `php -S localhost:8080`.
4. Access [http://localhost:8080/](http://localhost:8080/) using your web browser.

```html+php
<?php

// Load libraries
require __DIR__ . '/vendor/autoload.php';

// Make an alias "Client" instead of "mpyw\Cowitter\Client"
use mpyw\Cowitter\Client;

// You MUST apply this function when you show raw text in HTML contexts.
// However, tweet texts are already escaped by Twitter, therefore the option
//   $double_encode = false
// is required for them.
function h($str, $double_encode = true)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', $double_encode);
}

// Create a client object
$client = new Client([
    'YOUR CONSUMER_KEY',
    'YOUR CONSUMER_SECRET',
    'YOUR ACCESS_TOKEN',
    'YOUR ACCESS_TOKEN_SECRET',
]);

// Assign $_POST['tweet_text'] value
$tweet_text = (string)filter_input(INPUT_POST, 'tweet_text');

// Tweet only if $_POST['tweet_text'] is not empty
if ($tweet_text !== '') {
    try {
        $client->post('statuses/update', [
            'status' => $tweet_text,
        ]);
    } catch (\RuntimeException $e) {
        $errors[] = $e->getMessage();
    }
}

// Fetch tweets on your home timeline
try {
    $statuses = $client->get('statuses/home_timeline');
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
