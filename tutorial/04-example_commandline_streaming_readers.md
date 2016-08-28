# 04: Example: Commandline streaming readers

- Read userstreams of your multiple accounts.
- Like/Favorite 🍣 tweets from your all accounts!!

1. Create the file `streaming.php` to open your editor.
2. Write down the following code to save.
3. Execute the command `php streaming.php`

```html+php
<?php

// Load libraries
require __DIR__ . '/vendor/autoload.php';

// You SHOULD apply this function when you show HTML in text contexts.
// Note that tweet texts are already escaped by Twitter.
function uh($str)
{
    return htmlspecialchars_decode($str, ENT_QUOTES);
}

// Make an aliases
use mpyw\Cowitter\Client;
use mpyw\Co\Co;

// Like/Favorite tweets those contain 🍣 in your userstreams
$keyword = '🍣';

try {

    Co::wait(function () use ($keyword) {
        // Create multiple clients
        $clients = [
            new Client([...]),
            new Client([...]),
            ...
        ];

        // Connect to userstreams
        $tasks = [];
        foreach ($clients as $i => $client) {
            $tasks[] = $client->streamingAsync('user', function ($status) use ($clients, $keyword, $i) {
                // Exclude non-tweet events
                if (!isset($status->text)) return;

                // Convert HTML into raw text
                $status->text = uh($status->text);

                // Display tweets
                printf(
                    "[TL %d] @%s - %s: %s\n",
                    $i,
                    $status->user->screen_name,
                    $status->user->name,
                    $status->text
                );

                // Like/Favorite 🍣 tweets
                if (strpos($status->text, '🍣') !== false) {
                    echo "Detected 🍣!!\n";
                    $tasks = [];
                    foreach ($clients as $client) {
                        $tasks[] = $client->postAsync('favorites/create', ['id' => $status->id_str]);
                    }
                    yield Co::SAFE => $tasks; // Ignore minor errors
                }
            });
        }
        yield $tasks;
    });

} catch (\RuntimeException $e) {

    // Jump here if a FATAL error has occurred
    echo "Error: {$e->getMessage()}\n";

}
```
