<?php

if (version_compare(PHP_VERSION, '5.2.0') < 0) {
    throw new Exception('This library requires PHP 5.2.0 or later.');
}
