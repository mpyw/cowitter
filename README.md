TwistOAuth
==========

Advanced PHP Twitter library.  
Version 3.5.1

Requirements
============

- PHP version **5.3.2** or later
- **libcurl** (Sorry, required version is unknown)

Features
========

Basic:

- Using **GZIP compressed** connections
- Automatically decode responses
- Automatically fix weird responses
- Exception handling
- Requests for **REST** API
- Requests for **Streaming** API
- Requests using **OAuth Echo**
- Requests via **Proxy**
- Multipart requests

Abusing:

- **Asynchronous Multiple requests**
- **Asynchronous Multiple streaming**
- **Direct OAuth** authentication

Preparation
===========

### 1. Download this library

You can choose one of the following methods.

#### Direct Download

Click [here](https://github.com/mpyw/TwistOAuth/raw/master/build/TwistOAuth.phar) to save `TwistOAuth.phar` in your working directory.

#### Composer

Modify `require` directive in `composer.json`.

```json
{
    "require": {
        "mpyw/twistoauth": "@dev"
    }
}
```

If you choose this, replace all

```php
require 'TwistOAuth.phar';
```

into

```php
require 'vendor/autoload.php';
```

in examples.

### 2. Register your application

You can manage your API keys in [https://apps.twitter.com](https://apps.twitter.com/).
Now, let's register your own application.

1. Click `Create New App`
2. Fill `Name` `Description` `WebSite`.
3. Fill `Callback URL`. <ins>By default, users are redirected here after successfully authenticating.</ins>
4. Read rules and check `Yes, I agree`.
5. Click `Create your Twitter application`.

**NOTE: `localhost` is not available for Callback URL. Use `127.0.0.1` instead.**

### 3. Change application permissions

By default, you can only read tweets but cannot post tweets.
You have to configure permission settings.

1. Open detail page of your application.
2. Click `Permissions` Tab.
3. Select **`Read, Write and Access direct messages`**.
4. Click `Update settings`.

### 4. Note your *consumer\_key* and *consumer\_secret*

These parameters are identifier for **your application**.

1. Open detail page of your application.
2. Click `API Keys` Tab.
3. Note `API key` and `API secret`. They mean *consumer\_key* and *consumer\_secret*.

### 5. Generate your *access\_token* and *access\_token\_secret*

These parameters are identifier for **your account**.

1. Open detail page of your application.
2. Click `API Keys` Tab.
3. Click `Generate my access token`.
4. Note `Access token` and `Access token secret`.

Contents
========

- **[Examples](https://github.com/Certainist/TwistOAuth/blob/master/README_EXAMPLES.md)**
- **[Details](https://github.com/Certainist/TwistOAuth/blob/master/README_DETAILS.md)**

FAQ
====

- [How can I learn about Twitter API?](#how-can-i-learn-about-twitter-api)
- [Aren't there any nice authentication tools for obtaining tokens?](#arent-there-any-nice-authentication-tools-for-obtaining-tokens)
- [How do I use OAuth 2.0 authentication flow?](#how-do-i-use-oauth-20-authentication-flow)
- [What is `oauth_verifier`?](#what-is-oauth_verifier-)
- [What is `oauth_callback`?](#what-is-oauth_callback-)
- [How do I use `$to` in callback closure?](#how-do-i-use-to-in-callback-closure)
- [Are all classes immutable?](#are-all-classes-immutable)
- [Why don't you use namespace?](#why-dont-you-use-namespace)
- [Tweets are already escaped... wtf!?](#tweets-are-already-escaped-wtf)
- [User description contains unescaped `&`... wtf!?](#user-description-contains-unescaped--wtf)
- [cURL causes `SSL certificate problem` error in Windows!](#curl-causes-ssl-certificate-problem-error-in-windows)

### How can I learn about Twitter API?

Learn from documentation.

- **[REST API: endpoints](https://dev.twitter.com/rest/public)**
- **[REST API: field guides](https://dev.twitter.com/overview/api)**
- [Streaming API: endpoints and field guides](https://dev.twitter.com/streaming/overview)

Or watch actual response. The following tool is very very useful.

- **[twitSandbox](http://twitsandbox.com/)**

### Aren't there any nice authentication tools for obtaining tokens?

Try these tools.  

#### Commandline tool (Recommended)

- **[mpyw/twhelp-go](https://github.com/mpyw/twhelp-go)** **(Cross-compiled binaries)**
- [mpyw/twhelp](https://github.com/mpyw/twhelp) (\*nix or MSYS2/Cygwin required)

Global install is recommended.

#### Web tool hosted on Heroku (Deprecated)

- [TwistHelper](https://twist-helper.herokuapp.com/)

I absolutely promise you that <ins>I never record your credentials</ins>.  
But please never use if you cannot trust me even a little.

### How do I use OAuth 2.0 authentication flow?

Sorry, it is <ins>not</ins> available with this library. Use OAuth 1.0a instead.

### What is `oauth_verifier` ?

It is **required** for calling the following methods.

- `TwistOAuth::renewWithAccessToken()`
- `TwistOAuth::curlPostAccessToken()`

You can get it after user redirecting.

```php
$oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');
```

### What is `oauth_callback` ?

It is **not required**, but you can apply it for calling the following methods.

- `TwistOAuth::renewWithRequestToken()`
- `TwistOAuth::curlPostRequestToken()`

There are three value types.

| Name          | Example Value                       | Authentication Type              |
| :-----------: | :---------------------------------: | :------------------------------: |
| Empty String  | `""`                                | PIN or URL (Use default setting) |
| URL           | `"http://example.com/callback.php"` | URL                              |
| Out-Of-Band   | `"oob"`                             | PIN                              |

**WARNING:**  
You can only use URL if your application is configured as **Browser Application**.  
This means <ins>`Callback URL` is not empty</ins>.

### How do I use `$to` in callback closure?

Use `use()`.

```php
$to->streaming('user', function ($status) use ($to) { ... });
```

### How do I ignore `TwistException` thrown?

Now your code is:

```php
try {
    $to->post('statuses/update', array('status' => 'test'));
} catch (TwistException $e) { } // This is very lengthy!!!
```

To ignore all responses...

```php
curl_exec($to->curlPost('statuses/update', array('status' => 'test'))); // Wow, cool
```

### Are all classes immutable?

Yes.

```php
$a = new TwistOAuth('CK', 'CS');
$b = $a->renewWithRequestToken();
var_dump($a === $b); // false
```

However, you can change propety values by directly calling `__construct()`.

```php
$obj = new TwistOAuth('a', 'b');
$obj->__construct('c', 'd'); // Break immutable rules
```

### Why don't you use namespace?

This is because of the compatibility with previous versions of [abraham/twitteroauth](https://github.com/abraham/twitteroauth).  
I believe that the prefix `Twist-` will never collide with any other libraries.

### Tweets are already escaped... wtf!?

HTML special chars in texts of statuses are already escaped <ins>by Twitter</ins> like this.

```php
$status->text = htmlspecialchars($status->text, ENT_NOQUOTES, 'UTF-8');
```

**WARNING:**  
The flag is **`ENT_NOQUOTES`**, not `ENT_QUOTES` or `ENT_COMPAT`.  
The following snippet may print broken HTML.

```html+php
<input type="text" name="text" value="<?=$status->text?>">
```

You should do like this. <ins>Do not forget to set **4th** parameter into `false`.</ins>

```html+php
<input type="text" name="text" value="<?=htmlspecialchars(status->text, ENT_QUOTES, 'UTF-8', false)?>">
```

### User description contains unescaped `&`... wtf!?

HTML special chars in others are already sanitized <ins>by Twitter</ins> like this.

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

You should do like this.

```html+php
name: <?=htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8')?><br>
```

### cURL causes `SSL certificate problem` error in Windows!

In the past library, this problem was done with the following solution.

```php
// You are saying, "Hey libcurl, do not certificate whether I'm really talking to Twitter."
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
```

However, it makes **vulnerability** for *[man-in-the-middle attack](http://en.wikipedia.org/wiki/Man-in-the-middle_attack)*. Your connection can be hijacked <ins>even if using the protocol `https://`.</ins> This attack can be committed in the following case.

- Some DNS servers' caches are poisoned. Refer to *[DNS spoofing](https://en.wikipedia.org/wiki/DNS_spoofing)*
- You are connecting a public access point that an attacker launched as a trap.

The right way is to download to add CA information to your computer.

1&#46; Download [ca-bundle.crt](https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt) to save in the directory,
&#32;&#32;&#32;which <ins>path should not contain multibyte characters</ins>.

```bash
# Good
C:\ca-bundles\ca-bundles.crt

# Bad
C:\Users\田所浩二\Documents\証明書\ca-bundles.crt
```

2&#46; Add the following definition in `php.ini`.

```ini
curl.cainfo="C:\ca-bundles\ca-bundles.crt"
```

3&#46; Restart Apache.
