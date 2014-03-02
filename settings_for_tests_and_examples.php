<?php

// YOUR CONSUMER KEY
define('CK', '');
// YOUR CONSUMER SECRET
define('CS', '');
// YOUR ACCESS TOKEN
define('AT', '');
// YOUR ACCESS TOKEN SECRET
define('ATS', '');
// YOUR SCREEN NAME
define('SN', '');
// YOUR PASSWORD
define('PW', '');

// register autoloader
function twist_class_load($name) {
    $basedir = dirname(__FILE__);
    $path = "{$basedir}/src/{$name}.php";
    if (is_file($path)) {
        require $path;
    }
}
spl_autoload_register('twist_class_load');