<?php

spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/src/';

    if (strpos($class, 'App\\') !== 0) {
        return;
    }

    $relativeClass = str_replace('App\\', '', $class);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
