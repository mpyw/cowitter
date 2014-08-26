How to use Sign Up API
======================

Agreement
---------

This method is **seriously abusing**.

- Use it in the responsibility of own.
- Do not run on the public server. Its IP may be **banned**. Use on your own local server.

Implemention
------------

Append the following method into `TwistOAuth` class.

```php
    /**
     * Generate a new account via abusing Mobile Web API.
     * 
     * @param string $fullname
     * @param string $screen_name
     * @param string $email
     * @param string $password
     * @param string [$proxy]     full proxy URL.
     *                            e.g. https://111.222.333.444:8080
     * @return TwistOAuth
     * @throws TwistException
     */
    public static function androidSignUp($fullname, $screen_name, $email, $password, $proxy = '') {
        // abusing API key (unknown) 
        $to = new self('m9QsrrmJoANGROAiNKaC8g', 'udnsc1IAyTQnkj0KPfZffb9usZ6ZqVoXcdD3oxIVo');
        // abusing endpoint url
        $url = 'https://mobile.twitter.com/mobile_client_api/signup';
        $fullname    = self::validateString('$fullname', $fullname);
        $screen_name = self::validateString('$screen_name', $screen_name);
        $email       = self::validateString('$email', $email);
        $password    = self::validateString('$password', $password);
        $proxy       = self::validateString('$proxy', $proxy);
        $params = compact('fullname', 'screen_name', 'email', 'password');
        $ch = self::curlInit($proxy);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $to->getAuthorization($url, 'POST', $params, self::MODE_REQUEST_TOKEN),
            CURLOPT_URL        => $url,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
            CURLOPT_POST       => true,
        ));
        $response = self::decode($ch, curl_exec($ch));
        // this endpoint returns special JSON format on errors
        if (!isset($response->oauth_token, $response->oauth_token_secret)) {
            // each property is an array, so needs to be flatten
            $it = new RecursiveArrayIterator((array)$response);
            $it = new RecursiveIteratorIterator($it);
            throw new TwistException(implode("\n", iterator_to_array($it, false)), curl_getinfo($ch, CURLINFO_HTTP_CODE));
        }
        // abusing API key (Twitter for Android)
        return new self(
            '3nVuSoBZnx6U4vzUxf5w',
            'Bcs59EFbbsdF6Sl9Ng71smgStWEGwXXKSjYvPVt7qys',
            $response->oauth_token,
            $response->oauth_token_secret
        );
    }
```

Method Description
-----------

### static TwistOAuth::androidSignUp()

Create a new account.

```php
(TwistOAuth) TwistOAuth::androidSignUp($fullname, $screen_name, $email, $password, $proxy = '')
(TwistOAuth) $to->androidSignUp($fullname, $screen_name, $email, $password, $proxy = '')
```

#### Arguments

- (string) __*$fullname*__
- (string) __*$screen\_name*__
- (string) __*$email*__
- (string) __*$password*__
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

A new instance of `TwistOAuth`.

#### Exception

Throws `TwistException`.