TwistOAuth
==========

Advanced PHP Twitter library.  
Version 2.1.1

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
- **Asynchronous Multiple requests**
- **Asynchronous Multiple streaming**
- **Direct OAuth** authentication

Preparation
===========

### 1. Download this library

Click [here](https://github.com/Certainist/TwistOAuth/archive/master.zip) to save `TwistOAuth.php` in your working directory.

### 2. Register your application

You can manage your API keys in [https://apps.twitter.com](https://apps.twitter.com/).
Now, let's register your own application.

1. Click `Create New App`
2. Fill `Name` `Description` `WebSite`.
3. Fill `Callback URL`. <ins>Users are redirected here after successfully authenticating.</ins>
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

### Are all classes are immutable?

Yes.

```php
$a = new TwistOAuth('CK', 'CS');
$b = $a->renewWithRequestToken();
var_dump($a === $b); // false
```

however, you can change propety values by directly calling `__construct()`.

```php
$obj = new TwistOAuth('a', 'b');
$obj->__construct('c', 'd'); // Break immutable rules
```


### Is OAuth 2.0 is **<ins>not</ins>** available.

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

You should do like this.

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

You should do like this.

```html+php
name: <?=htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8')?><br>
```