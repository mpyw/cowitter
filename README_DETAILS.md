Class Description - TwistException
======================

Simply extended from `RuntimeException`.  
Treats errors caused on Twitter.

Class Description - TwistImage
=============================

Some `TwistOAuth` methods return an instance of `TwistImage` when `Content-Type: image/***` header is detected.

Properties
----------

### TwistImage::$type

```php
(String) $img->type
```

*Readonly*.  
Content-Type. This means the following value.

```php
substr('Content-Type: image/***', 14)
```

### TwistImage::$data

```php
(String) $img->data
```

*Readonly*.  
This means binary image data.

Methods
-------

### TwistImage::getDataUri()

```php
(String) $img->getDataUri()
```

#### Return Value

**Data URI**. This means the following value.

```php
'data:image/png;base64,......'
```

Class Description  - TwistOAuth
==================

Properties
----------

### TwistOAuth::$ck<br />TwistOAuth::$cs<br />TwistOAuth::$ot<br />TwistOAuth::$os

All properties are *Readonly*.

```php
(String) $to->ck // consumer_key
(String) $to->cs // consumer_secret
(String) $to->ot // oauth_token (request_token or access_token)
(String) $to->os // oauth_token_secret (request_token_secret or access_token_secret)
```

Basic Methods
-------------

### 

(String) `TwistOAuth::url()`

#### Arguments

- (String) __*$endpoint*__<br />Required.<br />e.g. `statuses/update` `users/lookup` `user`

#### Return Value

An endpoint URL.<br />e.g. `https://api.twitter.com/1.1/statuses/update.json`

#### Note

See [Twitter API documentation](https://dev.twitter.com/docs/api/1.1).

### (TwistOAuth) `TwistOAuth::login()`

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

### All classes are **Immutable**.

```php
$a = new TwistOAuth('CK', 'CS', 'AT', 'AS');
$b = $a->renewWithRequestToken();
var_dump($a === $b); // false
```

### OAuth 2.0 is **<ins>not</ins>** available.

Sorry. Use OAuth 1.0a instead.

### HTML special chars in texts of statuses are already escaped.

They are already filtered like this.

```php
$status->text = htmlspecialchars($status->text, ENT_NOQUOTES, 'UTF-8');
```

**WARNING:**  
The flag is **`ENT_NOQUOTES`**, not `ENT_QUOTES` or `ENT_COMPAT`.  
The following snippet may print broken HTML.

```html+php
<input type="text" name="text" value="<?=$status->text?>">
```

You should do like this:

```html+php
<input type="text" name="text" value="<?=str_replace('"', '&#039;', $status->text)?>">
```

### HTML special chars in others are already sanitized.

They are already filtered like this.

```php
$user->name        = str_replace(array('<', '>'), '', $user->name);
$user->description = str_replace(array('<', '>'), '', $user->description);
```

**WARNING:**  
`&` is not replaced into `&amp;`.  
The following snippet may print broken HTML.

```html+php
name: <?=$user->name?><br>
```

You should do like this:

```html+php
name: <?=htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8')?><br>
```