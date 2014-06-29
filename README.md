TwistOAuth
==========

Advanced PHP Twitter library.  
Version 2.0.0

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

### `$ti->contentType`

*Readonly*.  
This means the following value.

```php
substr('Content-Type: ***', 14);
```

### `$ti->binaryData`

*Readonly*.  

Methods
-------

### `$ti->getDataUri()`

#### Return Value

**Data URI**, such as `data:image/png;base64,......`.

Class - TwistOAuth
==================

Properties
----------

### `$to->ck`

*Readonly*.  
consumer\_key.

### `$to->cs`

*Readonly*.  
consumer\_secret.

### `$to->ot`

*Readonly*.  
oauth\_token. (request\_token or access\_token)

### `$to->os`

*Readonly*.  
oauth\_token_secret. (request\_token\_secret or access\_token\_secret)

Basic Methods
-------------

### `TwistOAuth::url()`

### `TwistOAuth::login()`

### `TwistOAuth::multiLogin()`

### `$to = new TwistOAuth()`

### `$to->getAuthenticateUrl()`

### `$to->getAuthorizeUrl()`

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

### `TwistOAuth::decode()`

### `$to->curlPostRequestToken()`

### `$to->curlPostAccessToken()`

### `$to->curlGet()`

### `$to->curlGetOut()`

### `$to->curlPost()`

### `$to->curlPostOut()`

### `$to->curlPostMultipart()`

### `$to->curlPostMultipartOut()`

### `$to->curlStreaming()`

Notices
=======

- All classes are **Immutable**.
- OAuth 2.0 is **<ins>not</ins>** available.
- Static methods can also be called dynamically, such as the following example.

```php
$url = TwistOAuth::url('statuses/update');
$url = $to->url('statuses/update');
```