TwistOAuth (Beta)
=================

Highly object-oriented PHP Twitter library for REST APIs and Streaming APIs.  
All `src` files are compressed into `build` file.

Comparison With Other Libraries
===============================

| Item   | tmhOAuth | twitteroauth | codebird | twitter-async | UltimateOAuth | TwistOAuth |
| :----: | :------: | :----------: | :------: | :-----------: | :-----------: | :--------: |
| **Supported PHP version (as far back)** | 5.1.2 | 5.2.0 | 5.3.0 | 5.2.0 | 5.2.0 | 5.2.0 |
| **Connection** | cURL | cURL | cURL | cURL | Socket | Socket |
| **Automatically decode responses** | No | Yes | Yes | Yes | Yes | Yes |
| **Automatically fix weird responses** | No | No | Yes | Partial | Yes | Yes |
| **Exception handling** | No | No | Partial | Yes | No | Yes |
| **GZIP on REST APIs** | No | No | No | No | No | Yes |
| **Streaming APIs** | Yes | No | No | No | No | Yes |   
| **Multiple streamings** | No | No | No | No | No | Yes |
| **OAuth 2.0** | Yes | No | Yes | No | No | No |
| **Uploading images** | Yes | No | Yes | Yes | Yes | Yes |
| **Asynchronous requests** | No | No | No | Yes | Yes | Yes |
| **Para-xAuth authorization** | No | No | No | No | Yes | Yes |
| **Avoid API Limits** | No | No | No | No | Yes | Yes |

Class Overview
==============

TwistException
--------------

```php
class TwistExcepton extends RuntimeException
```

This exception treats **Error on Twitter**.

#### TwistBase

```php
abstract class TwistBase
```

Provide static filtering method for child classes.

#### TwistUnserializable

```php
abstract class TwistUnserializable extends TwistBase
```

Protect instances from invalid serializing.

TwistCredential
---------------

```php
class TwistCredential extends TwistBase
```

This instance holds **identity**.  

TwistRequest
------------

```php
class TwistRequest extends TwistBase
```

This instance holds **request model**.  
For actually executing, use `TwistExecuter` instance.  

#### TwistExecuter

```php
class TwistExecuter extends TwistUnserializable 
```

Execute single or multiple requests **asynchronously**.  

TwistIterator
-------------

```php
class TwistCredential extends TwistExecuter
```

Implemented `Iterator` on `TwistExecuter`.


TwistOAuth
----------

```php
class TwistOAuth extends TwistUnserializable implements Iterator
```

Wrapper for `TwistCredential` and `TwistRequest` and `TwistIterator`.  
This instance also provides you **API limit avoidance**.  

Class Details
=============

Only important classes, properties and methods are documented here.

TwistException
--------------

### final public string getMessage()

Return error message.

### final public int getCode()

Return **HTTP Status Code**.

### final public mixed getRequest()

Return `TwistRequest` instance on which an error occurred.  
In the case of error on `stream_select()`, the return value will be **null**.

### final public string __toString()

Return string according to the following format.

```php
return sprintf('[%d] %s', $this->getCode(), $this->getMessage());
```

TwistCredential
---------------

### Properties

- **User Agent**

```php
private readonly string $userAgent = 'TwistOAuth';
```

- **OAuth Consumer**

```php
private readonly string $consumerKey    = '';
private readonly string $consumerSecret = '';
```

- **OAuth Tokens and Verifier**

```php
private readonly string $requestToken       = '';
private readonly string $requestTokenSecret = '';
private readonly string $accessToken        = '';
private readonly string $accessTokenSecret  = '';
private readonly string $verifier           = '';
```

- **User Information**

```php
private readonly string $userId     = '';
private readonly string $screenName = '';
```

- **Parameters for Para-xAuth authorization**

```php
private readonly string $screenName = '';
private readonly string $password   = '';
```

### final public __construct()

Constructor.

```php
new TwistCredential(
    string $consumerKey       = '', // Required.
    string $consumerSecret    = '', // Required.
    string $accessToken       = '', // Required unless authorize or authenticate later.
    string $accessTokenSecret = '', // Required unless authorize or authenticate later.
    string $screenName        = '', // Required if Para-xAuth authorize later.
    string $password          = ''  // Required if Para-xAuth authorize later.
)
```

### final public string __toString()

Return string according to the following format.

```php
$string = '';
if ($this->screenName !== '') {
    $string .= "@{$this->screenName}";
}
if ($this->userId !== '') {
    $string .= "(#{$this->userId})";
}
return $string;
```

### final public `$this` setUserAgent()<br />final public `$this` setConsumer()<br />final public `$this` setRequestToken()<br />final public `$this` setAccessToken()<br />final public `$this` setUserId()<br />final public `$this` setScreenName()<br />final public `$this` setPassword()<br />final public `$this` setVerifier()

Setter for properties.

```php
$TwistCredential
    ->setUserAgent    (string $userAgent    = '')
    ->setConsumer     (string $consumerKey  = '', string $consumerSecret     = '')
    ->setRequestToken (string $requestToken = '', string $requestTokenSecret = '')
    ->setAccessToken  (string $accessToken  = '', string $accessTokenSecret  = '')
    ->setUserId       (string $userId       = '')
    ->setScreenName   (string $screenName   = '')
    ->setPassword     (string $password     = '')
    ->setVerifier     (string $verifier     = '')
```

### final public string getAuthenticateUrl()<br />final public string getAuthorizeUrl()

Return URL for **authentication** or **authorization**.

```php
(string) $TwistCredential->getAuthenticateUrl (bool $force_login = false)
(string) $TwistCredential->getAuthorizeUrl    (bool $force_login = false)
```

#### Note: What is the difference between *Authenticate* and *Authorize* ?

|                | Authenticate  |  Authorize   |
| :-------------: |:---------------:| :-----------:|
| New User, Authed User on **force_login** | Jump to Twitter | Jump to Twitter |
| Authed User   | Jump to Twitter, however, if you set your application **__Allow this application to be used to Sign in with Twitter__**, quickly jump back to your callback URL.  |  Jump to Twitter  |

TwistRequest
------------

This class has a private constructor.  
Use static **factory methods** instead.

### Properties

```php
private readonly string $host;     // e.g. "api.twitter.com"
private readonly string $endpoint; // e.g. "/1.1/statuses/update.json"
private readonly string $method;   // e.g. "POST"
private readonly TwistCredential $credential;
private readonly mixed $response;  // Response object is set here
```

### final public static TwistRequest get()<br />final public static TwistRequest getAuto()<br />final public static TwistRequest post()<br />final public static TwistRequest postAuto()<br />final public static TwistRequest send()

Create a new `TwistRequest` instance for specified request.  
`$params` and `$credential` can be altered later.

- `get` `getAuto` are used for **GET** requests.
- `post` `postAuto` `send` are used for **POST** requests.
- `getAuto` `postAuto` automatically throw `TwistException`.
- `send` never waits responses.

```php
(TwistRequest) TwistRequest::get      (string $endpoint, mixed $params = array(), TwistCredential $credential = null)
(TwistRequest) TwistRequest::getAuto  (string $endpoint, mixed $params = array(), TwistCredential $credential = null)
(TwistRequest) TwistRequest::post     (string $endpoint, mixed $params = array(), TwistCredential $credential = null)
(TwistRequest) TwistRequest::postAuto (string $endpoint, mixed $params = array(), TwistCredential $credential = null)
(TwistRequest) TwistRequest::send     (string $endpoint, mixed $params = array(), TwistCredential $credential = null)
```

#### Examples

Each request has same meaning on each group.

```php
$TwistRequest = TwistRequest::getAuto('users/show'); 
$TwistRequest = TwistRequest::getAuto('users/show.json');
$TwistRequest = TwistRequest::getAuto('users/show.json?');
$TwistRequest = TwistRequest::getAuto('users/show?');
$TwistRequest = TwistRequest::getAuto('/users/show');
$TwistRequest = TwistRequest::getAuto('1.1/users/show'); 
$TwistRequest = TwistRequest::getAuto('/1.1/users/show'); 
$TwistRequest = TwistRequest::getAuto('https://api.twitter.com/1.1/users/show.json'); 
```

```php
$TwistRequest = TwistRequest::getAuto('users/show', array('id' => '12345')); 
$TwistRequest = TwistRequest::getAuto('users/show', 'id=12345'); // WITHOUT URL ENCODED!!
```

```php
$TwistRequest = TwistRequest::postAuto('account/update_profile_image', array(
    '@image' => 'test.png',
));
$TwistRequest = TwistRequest::postAuto('account/update_profile_image', '@image=test.png');
$TwistRequest = TwistRequest::postAuto('account/update_profile_image', array(
    'image' => base64_encode(file_get_contents('test.png')), // BASE64 ENCODED!!
));
```

```php
$TwistRequest = TwistRequest::postAuto('statuses/update_with_media', array(
    '@media[]' => 'test.png',
    'status'   => 'TEST',
));
$TwistRequest = TwistRequest::postAuto('statuses/update_with_media',
    '@media[]=test.png&status=TEST',
);
$TwistRequest = TwistRequest::postAuto('statuses/update_with_media', array(
    'media[]' => file_get_contents('test.png'), // WITHOUT BASE64 ENCODED!!
    'status'  => 'TEST',
));
```

### final public static TwistRequest login()

Provides a model for **Para-xAuth** authorization.

```php
(TwistRequest) TwistRequest::login(TwistCredential $credential)
```

### final public `$this` setParams()<br />final public `$this` setCredential()

Setter for properties.  
Exceptionally, the instances created by `TwistRequest::login()` cannot be applied.

```php
$TwistRequest
    ->setParams     (mixed $params     = array())
    ->setCredential (mixed $credential = null)
```

### final public mixed execute()

```php
(mixed) $TwistRequest->execute()
```

Execute request internally using `TwistIterator` and return response.  

#### Return Value Types

| Methods     | Value                                                                |
|:-----------:|:--------------------------------------------------------------------:|
|get          | **stdClass** or **array** or `TwistException`                        |
|post         | **stdClass** or **array** or `TwistException`                        |
|getAuto      | **stdClass** or **array** (`TwistException` is automatically thrown) |
|postAuto     | **stdClass** or **array** (`TwistException` is automatically thrown) |
|send         | **null**                                                             |
|login        | **stdClass** (`TwistException` is automatically thrown)              |

#### Examples

```php
try {
    $statuses = TwistRequest::getAuto('statuses/home_timeline', '', $credential)->execute();
    foreach ($statuses as $status) {
        echo "<p>@{$status->screen_name}: {$status->text}<p>\n";
    }
} catch (TwistException $e) {
    die($e);
}
```

```php
$statuses = TwistRequest::get('statuses/home_timeline', '', $credential)->execute();
if ($statuses instanceof TwistException) {
    die($statuses);
}
foreach ($statuses as $status) {
    echo "<p>@{$status->screen_name}: {$status->text}<p>\n";
}
```

TwistIterator
-------------

Manually use this instance insteadof `$TwistRequest->execute()` for **Multiple Requests** or **Streaming Requests**.  

### final public __construct()

```php
new TwistIterator(TwistRequest $request1, TwistRequest $request2, ...)
new TwistIterator(array<TwistRequest> $requests)
```

### final public `$this` setInterval()

Set interval function called while looping `foreach` block.

```php
$TwistIterator->setInterval(callable $callback, float $interval = 0, array $args = array())
```

### Examples

#### Multiple REST requests

```php
$TwistRequests = array(
    TwistRequest::send('statuses/update', 'status=@BarackObama FOO!!', $credential),
    TwistRequest::send('statuses/update', 'status=@BarackObama BAR!!', $credential),
    TwistRequest::send('statuses/update', 'status=@BarackObama BAZ!!', $credential),
);
foreach (new TwistIterator($TwistRequests) as $dummy) { }
```

#### Single streaming request

```php
set_time_limit(0);
try {
    $TwistRequest = TwistRequest::getAuto('user', '', $credential);
    foreach (new TwistIterator($TwistRequest) as $TwistRequest) {
        var_dump($TwistRequest->response);
    }
} catch (TwistException $e) {
    die($e);
}
```

#### Multiple streaming requests

```php
set_time_limit(0);
try {
    $TwistRequests = array(
        TwistRequest::getAuto('user', '', $credential),
        TwistRequest::getAuto('statuses/sample', '', $credential),
        TwistRequest::postAuto('statuses/filter', 'track=youtube', $credential),
    );
    foreach (new TwistIterator($TwistRequests) as $TwistRequest) {
        var_dump($TwistRequest->response);
    }
} catch (TwistException $e) {
    die($e);
}
```

TwistOAuth
----------

Basically use this instance for **Single REST Request**.  
In the case of **POST** method, **Only first credential** is used.  
In the case of **GET** method, all credentials are **rotationally** used for **API limit avoidance**.  
Unauthorized credentials are automatically tried to be authorized usnig **Para-xAuth** authorization.  
The instances are **not serializable**! Please serialize array of `TwistCredential` itself instead.

### final public __construct()

```php
new TwistOAuth(TwistCredential $credential1, TwistCredential $credential2, ...)
new TwistOAuth(array<TwistCredential> $credentials)
```

### final public mixed get()<br />final public mixed getAuto()<br />final public mixed post()<br />final public mixed postAuto()<br />final public mixed send()

```php
(mixed) $TwistOAuth->get      (string $endpoint, mixed $params = array())
(mixed) $TwistOAuth->getAuto  (string $endpoint, mixed $params = array())
(mixed) $TwistOAuth->post     (string $endpoint, mixed $params = array())
(mixed) $TwistOAuth->postAuto (string $endpoint, mixed $params = array())
(null)  $TwistOAuth->send     (string $endpoint, mixed $params = array())
```

### Example

```php
try {
    $TwistOAuth = new TwistOAuth($credential);
    foreach ($TwistOAuth->getAuto('statuses/home_timeline') as $status) {
        var_dump($stauts);
    }
    $TwistOAuth->postAuto('statuses/update', 'status=test');
} catch (TwistException $e) {
    die($e);
}
```




