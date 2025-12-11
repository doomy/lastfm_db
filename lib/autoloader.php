<?php

/**
 * Minimal autoloader for this repository.
 *
 * This project includes files manually in many places; however bootstrap.php expects
 * lib/autoloader.php to exist. This autoloader is deliberately conservative.
 */
spl_autoload_register(function (string $class): void {
    $base = __DIR__;

    // Direct match: lib/Foo.php
    $direct = $base . '/' . $class . '.php';
    if (is_file($direct)) {
        include_once $direct;
        return;
    }

    // Common folders used in this repo
    $candidates = [
        $base . '/app/' . $class . '.php',
        $base . '/base/' . $class . '.php',
        $base . '/service/' . $class . '.php',
        $base . '/enum/' . $class . '.php',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            include_once $path;
            return;
        }
    }
});
