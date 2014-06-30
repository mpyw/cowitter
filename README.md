TwistOAuth
==========

Advanced PHP Twitter library.  
Version 2.1.0

Requirements
============

- PHP version **5.3** or later
- **libcurl** (Sorry, required version is unknown)

Features
========

- Using **GZIP compressed** connections
- Automatically decode responses
- Automatically fix weird responses
- Exception handling
- Requests for **REST** API
- Requests for **Streaming** API
- Requests using **OAuth Echo**
- Multipart requests
- Multiple requests
- **Direct OAuth** authentication

Class - TwistException
======================

Simply extended from `RuntimeException`.  
Treats errors caused on Twitter.

Class - TwistImage
==================

Some `TwistOAuth` methods return an instance of `TwistImage` when `Content-Type: image/***` header is detected.

Properties
----------

### (String) `$ti->type`

*Readonly*.  
This means the following value.

```php
substr('Content-Type: ***', 14);
```

### (String) `$ti->data`

*Readonly*.  
This means binary image data.

Methods
-------

### (String) `$ti->getDataUri()`

#### Return Value

**Data URI**, such as `data:image/png;base64,......`.

Class - TwistOAuth
==================

Properties
----------

### (String) `$to->ck`

*Readonly*.  
consumer\_key.

### (String) `$to->cs`

*Readonly*.  
consumer\_secret.

### (String) `$to->ot`

*Readonly*.  
oauth\_token. (request\_token or access\_token)

### (String) `$to->os`

*Readonly*.  
oauth\_token_secret. (request\_token\_secret or access\_token\_secret)

Basic Methods
-------------

### (String) `TwistOAuth::url()`

#### Arguments

- (String) __*$endpoint*__<br />Required.<br />e.g. `statuses/update` `users/lookup` `user`

#### Return Value

An endpoint URL.<br />e.g. `https://api.twitter.com/1.1/statuses/update.json`

#### Note

See [Twitter API documentation](https://dev.twitter.com/docs/api/1.1).

### (TwistOAuth) `TwistOAuth::login($ck, $cs, $username, $password)`

#### Arguments

- (String) __*$ck*__<br />Required.<br />consumer\_key.
- (String) __*$cs*__<br />Required.<br />consumer\_secret.
- (String) __*$username*__<br />Required.<br />screen\_name or email.
- (String) __*$password*__<br />Required.

#### Return Value

A new instance of `TwistOAuth`.

#### Exception

Throws `TwistException`.

#### Note

Do not use this method a lot. You'll seem to be abusing.

### (Array) `TwistOAuth::multiLogin()`

#### Arguments

- (array) __*$credentials*__<br />Required.<br />An array consisting of the following structure.

```php
$credentials = array(
    'YOUR SCREEN_NAME 0' => array(
        'YOUR CONSUMER KEY 0',
        'YOUR CONSUMER SECRET 0',
        'YOUR USERNAME 0',
        'YOUR PASSWORD 0',
    ),
    'YOUR SCREEN_NAME 1' => array(
        'YOUR CONSUMER KEY 1',
        'YOUR CONSUMER SECRET 1',
        'YOUR USERNAME 1',
        'YOUR PASSWORD 1',
    ),
    'YOUR SCREEN_NAME 2' => array(
        'YOUR CONSUMER KEY 2',
        'YOUR CONSUMER SECRET 2',
        'YOUR USERNAME 2',
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

#### Note

Do not use this method a lot. You'll seem to be abusing.

### (TwistOAuth) `new TwistOAuth()`

#### Arguments

- (String) __*$ck*__<br />Required.<br />consumer\key.
- (String) __*$cs*__<br />Required.<br />consumer\_secret.
- (String) __*$ot*__<br />oauth\_token. (request\_token or access\_token)
- (String) __*$os*__<br />oauth\_token_secret. (request\_token\_secret or access\_token\_secret)

### (String) `$to->getAuthenticateUrl()`

#### Arguments

- (bool) __*$force\_login*__<br />Whether we force logined users to relogin.

#### Return Value

An URL for **Authentication**.

### (String) `$to->getAuthorizeUrl()`

#### Arguments

- (bool) __*$force\_login*__<br />Whether we force logined users to relogin.

#### Return Value

An URL for **Authorization**.

### `$to->renewWithRequestToken()`

### `$to->renewWithAccessToken()`

### `$to->get()`

### `$to->getOut()`

### `$to->post()`

### `$to->postOut()`

### `$to->postMultipart()`

### `$to->postMultipartOut()`

### `$to->streaming()`


Advanced Methods
----------------

### `TwistOAuth::curlMultiExec()`

### `TwistOAuth::curlMultiStreaming()`

### `$to->curlPostRequestToken()`

### `$to->curlPostAccessToken()`

### `$to->curlGet()`

### `$to->curlGetOut()`

### `$to->curlPost()`

### `$to->curlPostOut()`

### `$to->curlPostMultipart()`

### `$to->curlPostMultipartOut()`

### `$to->curlStreaming()`

### `TwistOAuth::decode()`

Notices
=======

- All classes are **Immutable**.
- OAuth 2.0 is **<ins>not</ins>** available.
- Static methods can also be called dynamically, such as the following example.

```php
$url = TwistOAuth::url('statuses/update');
$url = $to->url('statuses/update');
```