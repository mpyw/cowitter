Class Description - TwistException
=============================

Simply extended from `RuntimeException`.  
Treats errors caused on Twitter.

Class Description - TwistMedia
=============================

Some `TwistOAuth` methods return an instance of `TwistMedia` when the following types of header is detected.

- `Content-Type: image/***`
- `Content-Type: video/***`

Properties
----------

### TwistMedia::$type

```php
(string) $media->type
```

*Readonly*.  
Content-Type. This means the following value.

```php
'image/***'
'video/***'
```

### TwistMedia::$data

```php
(string) $media->data
```

*Readonly*.  
This means binary media data.

Methods
-------

### TwistMedia::getDataUri()

```php
(string) $media->getDataUri()
```

#### Return Value

**Data URI**. This means the following value.

```php
'data:image/***;base64,......'
'data:video/***;base64,......'
```

Class Description - TwistOAuth
==================

Properties
----------

### TwistOAuth::$ck<br />TwistOAuth::$cs<br />TwistOAuth::$ot<br />TwistOAuth::$os

All properties are *Readonly*.

```php
(string) $to->ck // consumer_key
(string) $to->cs // consumer_secret
(string) $to->ot // oauth_token (request_token or access_token)
(string) $to->os // oauth_token_secret (request_token_secret or access_token_secret)
```

Basic Methods
-------------

### TwistOAuth::\_\_construct()

Constructor.

```php
new TwistOAuth($ck, $cs, $ot = '', $os = '')
```

#### Arguments

- (string) __*$ck*__<br />consumer\_key.
- (string) __*$cs*__<br />consumer\_secret.
- (string) __*$ot*__<br />oauth\_token. (request\_token or access\_token)
- (string) __*$os*__<br />oauth\_token_secret. (request\_token\_secret or access\_token\_secret)

### TwistOAuth::getAuthenticateUrl()<br />TwistOAuth::getAuthorizeUrl()

Easily generate URL for users to login.

```php
(string) $to->getAuthenticateUrl($force_login = false)
(string) $to->getAuthorizeUrl($force_login = false)
```

#### Arguments

- (bool) __*$force\_login*__<br />Whether we force logined users to relogin.

#### Return Value

A URL for authentication or authorization.

### TwistOAuth::renewWithRequestToken()<br />TwistOAuth::renewWithAccessToken()<br />TwistOAuth::renewWithAccessTokenX()

Fetch tokens and regenerate instance with them.

```php
(TwistOAuth) $to->renewWithRequestToken($oauth_callback = '', $proxy = '')
(TwistOAuth) $to->renewWithAccessToken($oauth_verifier, $proxy = '')
(TwistOAuth) $to->renewWithAccessTokenX($username, $password, $proxy = '')
```

#### Arguments

- (string) __*$oauth\_callback*__
- (string) __*$oauth\_verifier*__
- (string) __*$username*__<br />screen_name or email.
- (string) __*$password*__
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

A <ins>new</ins> `TwistOAuth` instance.

#### Exception

Throws `TwistException`.

### TwistOAuth::get()<br />TwistOAuth::post()<br />TwistOAuth::postMultipart()

Execute a request for Twitter.

```php
(mixed) $to->get($url, $params = array(), $proxy = '')
(mixed) $to->post($url, $params = array(), $proxy = '')
(mixed) $to->postMultipart($url, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full or partial endpoint URL.<br />e.g. `statuses/update` `https://api.twitter.com/1.1/statuses/update.json`
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.<br />File data annotation is `#` on <ins>key</ins>.<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

Example value of __*$params*__:

```php
$params = 'status=test&in_reply_to_status_id=123456';
```

```php
$params = array(
    'status' => 'test',
    'in_reply_to_status_id' => '123456',
);
```

```php
$params = array(
    'status' => 'test',
    '@media[]' => 'test.jpg',
);
```

```php
$params = array(
    'status' => 'test',
    '#media[]' => file_get_contents('test.jpg'),
);
```

#### Return Value

Return value will mainly be `stdClass`, array or `TwistMedia`.

#### Exception

Throws `TwistException`.

### TwistOAuth::getOut()<br />TwistOAuth::postOut()<br />TwistOAuth::postMultipartOut()

Execute a request for third party sites using **OAuth Echo**.

```php
(mixed) $to->getOut($url, $params = array(), $proxy = '')
(mixed) $to->postOut($url, $params = array(), $proxy = '')
(mixed) $to->postMultipartOut($url, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full URL.<br />e.g. `http://api.twitpic.com/2/upload.json`
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.<br />File data annotation is `#` on <ins>key</ins>.<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

Return value will mainly be `stdClass`, array or `TwistMedia`.

#### Exception

Throws `TwistException`.

### TwistOAuth::streaming()

Execute a streaming request for Twitter.

```php
(void) $to->streaming($url, callable $callback, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full or partial endpoint URL.<br />e.g. `statuses/filter` `https://stream.twitter.com/1.1/statuses/filter.json`
- (callable) __*$callback*__<br />A callback function.<br />1 argument for each statuses.<br />Return true for disconnecting.
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.<br />File data annotation is `#` on <ins>key</ins>.<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

Example value of __*$callback*__:

```php
// A callback closure, which displays tweets unlimitedly.
$callback = function ($status) {
    // Treat only tweets
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        flush(); // Required if running not on Command Line but on Apache
    }
};
```

```php
// A callback closure, which displays 10 tweets and then disconnect.
$callback = function ($status) {
    static $i = 0;
    if ($i > 10) {
        // Return true for disconnecting.
        return true;
    }
    // Treat only tweets
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        ++$i;
        flush(); // Required if running not on Command Line but on Apache
    }
};
```

#### Exception

Throws `TwistException`.

Abusing Methods
---------------

### static TwistOAuth::login()

**Direct OAuth**. (Scraping Login)

```php
(TwistOAuth) TwistOAuth::login($ck, $cs, $username, $password, $proxy = '')
(TwistOAuth) $to->login($ck, $cs, $username, $password, $proxy = '')
```

#### Arguments

- (string) __*$ck*__<br />consumer\_key.
- (string) __*$cs*__<br />consumer\_secret.
- (string) __*$username*__<br />screen\_name or email.
- (string) __*$password*__
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

A new instance of `TwistOAuth`.

#### Exception

Throws `TwistException`.

### static TwistOAuth::multiLogin()

Multiple **Direct OAuth**. (Scraping Logins)

```php
(array) TwistOAuth::multiLogin(array $credentials, $throw_in_process = false)
(array) $to->multiLogin(array $credentials, $throw_in_process = false)
```

#### Arguments

- (array) __*$credentials*__<br />See example.
- (bool) __*$throw\_in\_process*__<br />See information about **static TwistOAuth::curlMultiExec()**.

Example value of __*$credentials*__:

```php
$credentials = array(
    'YOUR SCREEN_NAME 0' => array(
        'YOUR CONSUMER KEY 0',
        'YOUR CONSUMER SECRET 0',
        'YOUR SCREEN_NAME 0',
        'YOUR PASSWORD 0',
    ),
    'YOUR SCREEN_NAME 1' => array(
        'YOUR CONSUMER KEY 1',
        'YOUR CONSUMER SECRET 1',
        'YOUR SCREEN_NAME 1',
        'YOUR PASSWORD 1',
    ),
    'YOUR SCREEN_NAME 2' => array(
        'YOUR CONSUMER KEY 2',
        'YOUR CONSUMER SECRET 2',
        'YOUR SCREEN_NAME 2',
        'YOUR PASSWORD 2',
    ),
    ...
);
```

#### Return Value

An array consisting of the following structure.

```php
$return_value = array(
    'YOUR SCREEN_NAME 0' => new TwistOAuth(...),
    'YOUR SCREEN_NAME 1' => new TwistOAuth(...),
    'YOUR SCREEN_NAME 2' => new TwistOAuth(...),
    ...
);
```

#### Exception

Throws `TwistException`.

### TwistOAuth::curlPostRequestToken()<br />TwistOAuth::curlPostAccessToken()<br />TwistOAuth::curlGet()<br />TwistOAuth::curlGetOut()<br />TwistOAuth::curlPost()<br />TwistOAuth::curlPostOut()<br />TwistOAuth::curlPostMultipart()<br />TwistOAuth::curlPostMultipartOut()<br />TwistOAuth::curlStreaming()

```php
(resource) $to->curlPostRequestToken($oauth_callback = '', $proxy = '')
(resource) $to->curlPostAccessToken($oauth_verifier, $proxy = '')
(resource) $to->curlGet($url, $params = array(), $proxy = '')
(resource) $to->curlGetOut($url, $params = array(), $proxy = '')
(resource) $to->curlPost($url, $params = array(), $proxy = '')
(resource) $to->curlPostOut($url, $params = array(), $proxy = '')
(resource) $to->curlPostMultipart($url, $params = array(), $proxy = '')
(resource) $to->curlPostMultipartOut($url, $params = array(), $proxy = '')
(resource) $to->curlStreaming($url, callable $callback, $params = array(), $proxy = '')
```

#### Arguments

(Omitted)

#### Return Value

A cURL resource.

### static TwistOAuth::curlMultiExec()<br />static TwistOAuth::curlMultiStreaming()

```php
(array) TwistOAuth::curlMultiExec(array $curls, $throw_in_process = false)
(array) $to->curlMultiExec(array $curls, $throw_in_process = false)
(void) TwistOAuth::curlMultiStreaming(array $curls) // $throw_in_process is always true
(void) $to->curlMultiStreaming(array $curls) // $throw_in_process is always true
```

#### Arguments, Return Value, Exception

- (array) __*$curls*__<br />An array of cURL resources.
- (bool) __*$throw\_in\_process*__<br />See below.

Example:

```php
try {
    
    $throw_in_process = /* true or false*/ ;
    $result = $to->curlMultiExec(array(
        'a' => $to->curlGet('users/show', array(
            'screen_name' => 'foofoofoobarbarbarbazbazbaz', // invalid screen_name
        )),
        'b' => $to->curlGet('users/show', array(
            'screen_name' => 'twitter', // valid screen_name
        )),
    ), $throw_in_process);
    
    echo "Flow A\n";
    foreach ($result as $k => $v) {
        printf("[%s] %s\n", $k, $v instanceof stdClass ? $v->screen_name : $v->getMessage());
    }
    
} catch (TwistException $e) {
    
    echo "Flow B\n";
    printf("%s\n", $e->getMessage());
    
}
```

If __*$throw\_in\_process*__ is false...

```
Flow A
[a] Sorry, that page does not exist
[b] twitter
```

If __*$throw\_in\_process*__ is true...

```
Flow B
(a) Sorry, that page does not exist
```
