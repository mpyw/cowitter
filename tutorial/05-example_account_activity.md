# Twitter Account Activity API (Webhook)

The Account Activity API is a webhook-based API that sends account events to a web app you develop, deploy and host.

This is a short instruction, you can read more [here](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/guides/getting-started-with-webhooks):

1) Get developer account (if you don't have one [apply](https://developer.twitter.com/en/apply))
2) Setup dev envoriment [here](https://developer.twitter.com/en/account/environments)
3) Prepare application owner access token and access token secret.

## Create new webhook

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

## Retrieve app's current webhook configuration

Returns all environments, webhook URLs and their statuses for the authenticating app. Currently, only one webhook URL can be registered to each environment. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-webhooks)

```php
// prepare client
$client = new Client(['CK', 'CS']);

// Your envoriment
$env = 'dev';

// Get webhooks
$client->get('account_activity/all/' . $env . '/webhooks');

```

## Delete app’s current webhook configuration

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

## Manually trigger a CRC request

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
## Add new user subscription

Subscribes the provided application to all events for the provided environment for all message types. After activation, all events for the requesting user will be sent to the application’s webhook via POST request. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#post-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
$client->post('account_activity/all/' . $env . '/subscriptions');

```


## Retrieve a user subscription

Provides a way to determine if a webhook configuration is subscribed to the provided user’s events. If the provided user context has an active subscription with provided application, returns 204 OK. If the response code is not 204, then the user does not have an active subscription. See HTTP Response code and error messages below for details. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
 $client->get('account_activity/all/' . $env . '/subscriptions');

```

## Remove a user subscription

Deactivates subscription(s) for the provided user context and application for all activities. After deactivation, all events for the requesting user will no longer be sent to the webhook URL. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#delete-account-activity-all-env-name-subscriptions)

```php
// at and ats are customers access token and access token secret
$client = new Client(['CK', 'CS', 'AT', 'ATS']);

// Your envoriment
$env = 'dev';

// Send a post request to twitter
 $client->delete('account_activity/all/' . $env . '/subscriptions');

```
## Get subscripted user list

Returns a list of the current All Activity type subscriptions. Note that the /list endpoint requires application-only OAuth, so requests should be made using a bearer token instead of user context. [Documentation](https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/api-reference/aaa-premium#get-account-activity-all-env-name-subscriptions-list)

```php
// prepare client
$client = new Client(['CK', 'CS']);

// Your envoriment
$env = 'dev';

// Send a get request to twitter
$client->get('account_activity/all/' . $env . '/subscriptions/list);

```
