TwistOAuth
==========

Highly object-oriented PHP Twitter library for RESTAPIs and StreamingAPIs.  
All `src` files are packed into `build` file.

Comparison with other libraries
===============================

| Item   | tmhOAuth | twitteroauth | codebird | twitter-async | UltimateOAuth | TwistOAuth |
| :----: | :------: | :----------: | :------: | :-----------: | :-----------: | :--------: |
| Supported PHP version (as far back) | 5.1.2 | 5.2.0 | 5.3.0 | 5.2.0 | 5.2.0 | 5.2.0 |
| Connection | cURL | cURL | cURL | cURL | Socket | Socket |
| Automatically decode responses | No | Yes | Yes | Yes | Yes | Yes |
| Automatically fix weird responses | No | No | Yes | Partial | Yes | Yes |
| Exception handling | No | No | Partial | Yes | No | Yes |
| GZIP on REST APIs | No | No | No | No | No | Yes |
| Streaming APIs | Yes | No | No | No | No | Yes |   
| Multiple streamings | No | No | No | No | No | Yes |
| OAuth 2.0 | Yes | No | Yes | No | No | No |
| Uploading images | Yes | No | Yes | Yes | Yes | Yes |
| Asynchronize requests | No | No | No | Yes | Yes | Yes |
| Para-xAuth authorization | No | No | No | No | Yes | Yes |
| Avoid API Limits | No | No | No | No | Yes | Yes |

Overview of Clasess
===================

TwistException
--------------

```php
class TwistExcepton extends RuntimeException
```

This exception treats **Error on Twitter**.

### TwistBase

```php
abstract class TwistBase
```

Provide static filtering method for child classes.

### TwistUnserializable

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

### TwistExecuter

```php
class TwistExecuter extends TwistUnserializable 
```

Execute single or multiple requests **asynchronously**.  

TwistIterator
-------------

```php
class TwistCredential extends TwistExecuter
```

Implemented `iterator` on TwistExecuter.


TwistOAuth
----------

```php
class TwistOAuth extends TwistUnserializable
```

Wrapper for `TwistCredential` and `TwistRequest` and `TwistIterator`.  
This instance also provides you **API limit avoidance**.  

Methods and Properties
======================

Only important classes are documented here.

TwistException - Properties
---------------------------

final public TwistCredential::__construct()
-------------------------------------------

final public string TwistCredential::__toString()
------------------------------------------------

final public TwistRequest TwistCredential::getRequest()
-------------------------------------------------------

TwistCredential - Properties
----------------------------

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
private readonly string $screenName        = '';
private readonly string $password          = '';
private readonly string $authenticityToken = '';
private readonly string $verifier          = '';
```

- **API call history**

```php
private readonly array<string, string> $history = array();
```

- **Cookies**

```php
private readonly array<string, string> $cookies = array();
```

final public TwistCredential::__construct()
-------------------------------------------

Constructor.

```php
$TwistCredential = new TwistCredential(
    (string) $consumerKey       = '', // Required.
    (string) $consumerSecret    = '', // Required.
    (string) $accessToken       = '', // Required unless authorize or authenticate later.
    (string) $accessTokenSecret = '', // Required unless authorize or authenticate later.
    (string) $screenName        = '', // Required if Para-xAuth authorize later.
    (string) $password          = ''  // Required if Para-xAuth authorize later.
)
```

final public string TwistCredential::__toString()
-----------------------------------------------


final public mixed TwistCredential::__get()
-------------------------------------------

Getter for properties.

```php
$userAgent          = $TwistCredential->userAgent;
$consumerKey        = $TwistCredential->consumerKey;
$consumerSecret     = $TwistCredential->consumerSecret;
$requestToken       = $TwistCredential->requestToken;
$requestTokenSecret = $TwistCredential->requestTokenSecret;
$userId             = $TwistCredential->userId;
$screenName         = $TwistCredential->screenName;
$password           = $TwistCredential->password;
$authenticityToken  = $TwistCredential->authenticityToken;
$verifier           = $TwistCredential->verifier;
$history            = $TwistCredential->history;
```

final public `$this` TwistCredential::setUserAgent()<br />final public `$this` TwistCredential::setConsumer()<br />final public `$this` TwistCredential::setRequestToken()<br />final public `$this` TwistCredential::setAccessToken()<br />final public `$this` TwistCredential::setUserId()<br />final public `$this` TwistCredential::setScreenName()<br />
final public `$this` TwistCredential::setPassword()<br />
final public `$this` TwistCredential::setAuthenticityToken()<br />
final public `$this` TwistCredential::setVerifier()
---------------------------------------------------

final public `$this` TwistCredential::setHistory()<br />final public `$this` TwistCredential::setCookie()
---------------------------------------------------

final public string TwistCredential::getAuthorizeUrl()<br />final public string TwistCredential::getAuthenticateUrl()
-------------------------------------








