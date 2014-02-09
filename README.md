TwistOAuth
==========

Highly object-oriented PHP Twitter library for RESTAPIs and StreamingAPIs.  
All `src` files are packed into `build` file.

Comparison with other libraries
===============================

| Item   | tmhOAuth | twitteroauth | codebird | twitter-async | UltimateOAuth | TwistOAuth |
| :----: | :------: | :----------: | :------: | :-----------: | :-----------: | :--------: |
| **Supported PHP version (as far back) | 5.1.2 | 5.2.0 | 5.3.0 | 5.2.0 | 5.2.0 | 5.2.0** |
| **Connection** | cURL | cURL | cURL | cURL | Socket | Socket |
| **Automatically decode responses** | No | Yes | Yes | Yes | Yes | Yes |
| **Automatically fix weird responses** | No | No | Yes | Partial | Yes | Yes |
| **Exception handling** | No | No | Partial | Yes | No | Yes |
| **GZIP on REST APIs** | No | No | No | No | No | Yes |
| **Streaming APIs** | Yes | No | No | No | No | Yes |   
| **Multiple streamings** | No | No | No | No | No | Yes |
| **OAuth 2.0** | Yes | No | Yes | No | No | No |
| **Uploading images** | Yes | No | Yes | Yes | Yes | Yes |
| **Asynchronized requests** | No | No | No | Yes | Yes | Yes |
| **Para-xAuth authorization** | No | No | No | No | Yes | Yes |
| **Avoid API Limits** | No | No | No | No | Yes | Yes |

Overview of classes
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

Class Details
=============

Only important classes, properties and methods are documented here.

TwistException
--------------

### Properties

### final public __construct()

### final public string __toString()

### final public TwistRequest getRequest()

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
$TwistCredential = new TwistCredential(
    (string) $consumerKey       = '', // Required.
    (string) $consumerSecret    = '', // Required.
    (string) $accessToken       = '', // Required unless authorize or authenticate later.
    (string) $accessTokenSecret = '', // Required unless authorize or authenticate later.
    (string) $screenName        = '', // Required if Para-xAuth authorize later.
    (string) $password          = ''  // Required if Para-xAuth authorize later.
)
```

### final public string __toString()

### final public mixed __get()

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
$verifier           = $TwistCredential->verifier;
```

### final public `$this` setUserAgent()<br />final public `$this` setConsumer()<br />final public `$this` setRequestToken()<br />final public `$this` setAccessToken()<br />final public `$this` setUserId()<br />final public `$this` setScreenName()<br />final public `$this` setPassword()<br />final public `$this` setVerifier()

### final public string getAuthorizeUrl()<br />final public string getAuthenticateUrl()

### final public string getAuthorizeUrl()<br />final public string getAuthenticateUrl()

TwistRequest
------------

### Properties

### final public static TwistRequest get()<br />final public static TwistRequest getAuto()<br />final public static TwistRequest post()<br />final public static TwistRequest postAuto()<br />final public static TwistRequest send()

### final public static TwistRequest login()

### final public mixed __get()

### final public `$this` setParams()<br />final public `$this` setCredential()

### final public mixed execute()


TwistIterator
-------------

### final public __construct()

Manually use this instance for **Multiple Requests** or **Streaming Requests**.  
Just use `foreach` statement.

#### Example 1: Multiple REST requests

#### Example 2: Single streaming requests

#### Example 3: Multiple streaming requests

TwistOAuth
----------

### final public __construct()

### final public mixed get()<br />final public mixed getAuto()<br />final public mixed post()<br />final public mixed postAuto()<br />final public mixed send()


