# cowitter [![Build Status](https://scrutinizer-ci.com/g/mpyw/cowitter/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mpyw/cowitter/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/mpyw/cowitter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mpyw/cowitter/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mpyw/cowitter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mpyw/cowitter/?branch=master)

Asynchronous Twitter client compatible with mpyw/co Generator-based flows.

| PHP | :question: | Feature Restriction |
|:---:|:---:|:---:|
| 7.0~ | :smile: | Full Support |
| 5.5~5.6 | :anguished: | Generator is not so cool |
| ~5.4 | :boom: | Incompatible |

## Installing

```
composer require mpyw/cowitter:^1.0
```

## Tutorial

1. [Preparation](tutorial/01-preparation.md)
2. [Example: Application for your own personal use](tutorial/02-example_application_for_your_own_personal_use.md)
3. [Example: Sign in with Twitter](tutorial/03-example_sign_in_with_twitter.md)
4. [Example: Commandline streaming readers](tutorial/04-example_commandline_streaming_readers.md)

## Quick examples

### Prepare requirements

```php
require __DIR__ . '/vendor/autoload.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
```

### Create client

```php
$client = new Client(['CK', 'CS', 'AT', 'ATS']);
```

### Synchronous requests

```php
// Search tweets
$statuses = $client->get('search/tweets', ['q' => 'cowitter'])->statuses;
var_dump($statuses);
```

```php
// Update tweet
$client->post('statuses/update', ['status' => 'Cowitter is the best twitter library for PHP!']);
```

```php
// Update tweet with multiple images
$ids = [
    $client->postMultipart('media/upload', ['media' => new \CURLFile('photo01.png')])->media_id_string,
    $client->postMultipart('media/upload', ['media' => new \CURLFile('photo02.jpg')])->media_id_string,
];
$client->post('statuses/update', [
    'status' => 'My photos',
    'media_ids' => implode(',', $ids),
]);
```

```php
// Listen user streaming
$client->streaming('user', function ($status) {
    if (!isset($status->text)) return;
    printf("%s(@s) - %s\n",
        $status->user->name,
        $status->user->screen_name,
        htmlspecialchars_decode($status->text, ENT_NOQUOTES)
    );
});
```

### Asynchronous requests

```php
// Search tweets
Co::wait(function () use ($client) {
    $statuses = (yield $client->getAsync('search/tweets', ['q' => 'cowitter']))->statuses;
    var_dump($statuses);
});
```

```php
// Rapidly update tweets for 10 times
$tasks = [];
for ($i = 0; $i < 20; ++$i) {
    $tasks[] = $client->postAsync('statuses/update', [
        'status' => str_repeat('!', $i + 1),
    ]);
}
Co::wait($tasks);
```

```php
// Rapidly update tweet with multiple images
Co::wait(function () use ($client) {
    $info = yield [
        $client->postMultipartAsync('media/upload', ['media' => new \CURLFile('photo01.png')]),
        $client->postMultipartAsync('media/upload', ['media' => new \CURLFile('photo02.png')]),
    ];
    yield $client->postAsync('statuses/update', [
        'status' => 'My photos',
        'media_ids' => implode(',', array_column($info, 'media_id_string')),
    ]);
});
```

```php
// Listen filtered streaming to favorite/retweet at once each tweet
Co::wait($client->streamingAsync('statuses/filter', function ($status) use ($client) {
    if (!isset($status->text)) return;
    printf("%s(@s) - %s\n",
        $status->user->name,
        $status->user->screen_name,
        htmlspecialchars_decode($status->text, ENT_NOQUOTES)
    );
    yield Co::SAFE => [ // ignore errors
        $client->postAsync('favorites/create', ['id' => $status->id_str]),
        $client->postAsync("statuses/retweet/{$status->id_str}"),
    ];
}, ['track' => 'PHP']));
```

```php
// Rapidly update with MP4 video
Co::wait(function () use ($client) {
    $file = new \SplFileObject('video.mp4', 'rb');
    $on_uploading = function ($percent) {
        echo "Uploading ... ({$percent}%)\n";
    };
    $on_processing = function ($percent) {
        echo "Processing ... ({$percent}%)\n";
    };
    yield $client->postAsync('statuses/update', [
        'status' => 'My video',
        'media_ids' => (yield $client->uploadVideoAsync($file, $on_uploading, $on_processing))->media_id_string,
    ]);
    echo "Done\n";
});
```

### Handle exceptions

```php
try {

    // do stuff here
    $client->get(...);
    $client->post(...);

} catch (HttpException $e) {

    // cURL communication successful but something went wrong with Twitter APIs.
    $message = $e->getMessage();    // Message
    $code    = $e->getCode();       // Error code (-1 if not available)
    $status  = $e->getStatusCode(); // HTTP status code

} catch (CURLException $e) {

    // cURL communication failed.
    $message = $e->getMessage();    // Message    (equivalent to curl_error())
    $code    = $e->getCode();       // Error code (equivalent to curl_errno())

}
```

or

```php
try {

    // do stuff here
    $client->get(...);
    $client->post(...);

} catch (\RuntimeException $e) {

    // Something failed.
    $message = $e->getMessage();

}
```

### Avoiding SSL errors due to the old libcurl version

If you encountered `SSL certificate problem` error...

1. Download the latest `cacert.pem` from official libcurl site.<br />https://curl.haxx.se/docs/caextract.html
2. Specify the path as **`CURLOPT_CAINFO`**. Using the magic constant `__DIR__` is recommended.

```php
$client = new Client(['CK', 'CS', 'AT', 'ATS'], [CURLOPT_CAINFO => __DIR__ . '/cacert.pem']);
```

or

```php
$client = new Client(['CK', 'CS', 'AT', 'ATS']);
$client = $client->withOptions([CURLOPT_CAINFO => __DIR__ . '/cacert.pem']);
```

## Details

Read interfaces.

- [Client](src/ClientInterface.php)
- [Response](src/ResponseInterface.php)
- [Media](src/MediaInterface.php)
- [HttpException](src/HttpExceptionInterface.php)

## Todos

- Documentation
- Improving codes
