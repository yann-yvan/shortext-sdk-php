<?php

/*
 * 1.x documented the namespace as "Nycorp\Shortext\..." while the classes are declared under "NyCorp\Shortext\...".
 * PHP class names are case-insensitive but Composer's PSR-4 lookup is not: this resolves the 1.x spelling.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'Nycorp\\Shortext\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $file = __DIR__.'/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';

    if (is_file($file)) {
        require_once $file;
    }
});
