<?php

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    echo 'You have to execute via CLI' . PHP_EOL;
    exit;
}
if (ini_get('phar.readonly') || ini_get('phar.require_hash')) {
    fprintf(fopen('php://stderr', 'wb'), '%s', 'You have to disable "phar.readonly" and "phar.require_hash"' . PHP_EOL);
    exit(1);
}

$version = json_decode(file_get_contents('composer.json'))->version;
$pharpath = __DIR__ . '/build/TwistOAuth.phar';
if (is_file($pharpath)) {
    unlink($pharpath);
}
$phar = new \Phar($pharpath, 0, basename($pharpath));
$phar->setStub("<?php 

/* 
 * TwistOAuth Version $version
 * 
 * @author  CertaiN
 * @github  https://github.com/mpyw/TwistOAuth
 * @license BSD 2-Clause
 */

spl_autoload_register(function (\$class) {
    \$path = 'phar://' . __FILE__ . DIRECTORY_SEPARATOR . \$class . '.php';
    if (is_file(\$path)) {
        require \$path;
    }
});

__HALT_COMPILER(); ?>");
$phar->buildFromDirectory(__DIR__ . '/src');