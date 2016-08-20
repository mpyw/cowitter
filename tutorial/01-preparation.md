# 01: Preparation

## Create your API keys

Before creating an application, you have to [register your phone number](https://twitter.com/settings/add_phone).

### Create an application

1. Access [https://apps.twitter.com/](https://apps.twitter.com/).
2. Click the button <kbd>Create New App</kbd>.
3. Fill out the form to submit.

- `Callback URL` is actually a REQUIRED field when you create web-based applications.<br />Even if you can change it when you provide authentication, you should fill with dummy URL.
  - e.g. [http://127.0.0.1/](http://127.0.0.1/)

### Upgrade privileges

To use all endpoints, you have to upgrade privilages of your API key.

1. Access [https://apps.twitter.com/](https://apps.twitter.com/) to select the application you created.
2. Click the tab <kbd>Permissions</kbd>.
3. Select `Read, Write and Access direct messages` to submit.

### Confirm API keys

1. Access [https://apps.twitter.com/](https://apps.twitter.com/) to select the application you created.
2. Click the tab <kbd>Keys and Access Tokens</kbd>.
3. Note `Consumer Key` `Consumer Secret`. They are for every account that uses your application.
3. Note `Access Token` `Access Token Secret`. They are only for your own account.

## Install PHP, Composer and Cowitter

### For Windows beginners

1. Download and install [XAMPP](https://www.apachefriends.org) that contains PHP version **7.x**.
2. Download and install [Composer](https://getcomposer.org/doc/00-intro.md#installation-windows).
3. Create your project folder somewhere.
4. Right-click in the folder pressing <kbd>Shift</kbd> key to click `Open Command Window Here`.
5. Run the followings commands.
  - `composer require mpyw/cowitter:@dev`
  - `composer install`
6. Now you can verify some files are created.
  - The file `composer.json` contains your package information.
  - The folder `vendor` contains installed packages.

### For Windows, macOS or Linux geeks

1. Install PHP and Composer using your favorite package manager.
2. Run the followings commands.
  - `mkdir ~/myapp && cd ~/myapp`
  - `composer require mpyw/cowitter:@dev`
  - `composer install`

I believe your skill :D
