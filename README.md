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
    printf("%s(@%s) - %s\n",
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

### Twitter Account Activity API (Webhook)

The Account Activity API is a webhook-based API that sends account events to a web app you develop, deploy and host.

This is a short instruction, you can read more [here](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/guides/getting-started-with-webhooks):

1) Get developer account (if you don't have one [apply](https://developer.twitter.com/en/apply)
2) Setup dev envoriment [here](https://developer.twitter.com/en/account/environments)
3) Prepare application owner access token and access token secret.

#### Create new webhook

Registers a webhook URL for all event types. The URL will be validated via CRC request before saving. In case the validation failed, returns comprehensive error message to the requester. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#post-account-activity-all-env-name-webhooks)

```php
// at and ats are application owner access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Your webhook url
$url = 'https://example.com/webhook';

// Post to twitter
$client->post('account_activity/all/' . $env . '/webhooks', ['url' => $url]);

```

#### Retrieve app's current webhook configuration

Returns all environments, webhook URLs and their statuses for the authenticating app. Currently, only one webhook URL can be registered to each environment. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-webhooks)

```php
// prepare client
$client = new Client(['CK', 'CS']);

// Your envoriment
$env = 'dev';

// Get webhooks
$client->get('account_activity/all/' . $env . '/webhooks');

```

#### Delete app’s current webhook configuration

Removes the webhook from the provided application’s all activities configuration. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#delete-account-activity-all-env-name-webhooks-webhook-id)

```php
// at and ats are application owner access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Your webhook id
$webhook_id = '1234';

// Send a delete request to twitter
$client->delete('account_activity/all/' . $env . '/webhooks/' . $webhook_id);

```

#### Manually trigger a CRC request

Triggers the challenge response check (CRC) for the given enviroments webhook for all activites. If the check is successful, returns 204 and reenables the webhook by setting its status to valid. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#put-account-activity-all-env-name-webhooks-webhook-id)

```php
// at and ats are application owner access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Your webhook id
$webhook_id = '1234';

// Send a put request to twitter
$client->put('account_activity/all/' . $env . '/webhooks/' . $webhook_id);

```
#### Add new user subscription

Subscribes the provided application to all events for the provided environment for all message types. After activation, all events for the requesting user will be sent to the application’s webhook via POST request. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#post-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
$client->post('account_activity/all/' . $env . '/subscriptions');

```


#### Retrieve a user subscription

Provides a way to determine if a webhook configuration is subscribed to the provided user’s events. If the provided user context has an active subscription with provided application, returns 204 OK. If the response code is not 204, then the user does not have an active subscription. See HTTP Response code and error messages below for details. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
 $client->get('account_activity/all/' . $env . '/subscriptions');

```

#### Remove a user subscription

Deactivates subscription(s) for the provided user context and application for all activities. After deactivation, all events for the requesting user will no longer be sent to the webhook URL. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#delete-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
 $client->delete('account_activity/all/' . $env . '/subscriptions');

```
#### Get subscripted user list

Returns a list of the current All Activity type subscriptions. Note that the /list endpoint requires application-only OAuth, so requests should be made using a bearer token instead of user context. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-subscriptions-list)

```php
// prepare client
$client = new Client(['CK', 'CS']);

// Your envoriment
$env = 'dev';

// Send a get request to twitter
$client->get('account_activity/all/' . $env . '/subscriptions/list);

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
2. Please choose either of the following solutions.

#### 2-A: Configure globally

Specify the path as **`curl.cainfo`** in your `php.ini`.

```ini
curl.cainfo="C:\foo\bar\baz\cacert.pem"
```

DO NOT forget restarting Apache.

#### 2-B: Configure locally

Specify the path as **`CURLOPT_CAINFO`**. Using the magic constant `__DIR__` is recommended.

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
