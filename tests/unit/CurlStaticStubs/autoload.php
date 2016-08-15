<?php

(function () {
    foreach (glob(__DIR__ . '/*.php') as $file) {
        $file = realpath($file);
        if ($file !== __FILE__) {
            require $file;
        }
    }
})();
