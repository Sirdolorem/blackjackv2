<?php

use blackjack\Env;

spl_autoload_register(function ($class) {

    $baseDir = __DIR__ . '/Classes/';


    $class = str_replace('blackjack\\', '', $class);


    $file = $baseDir . str_replace('\\', '/', $class) . '.php';


    if (file_exists($file)) {
        require $file;
    }
});
Env::load(__DIR__ . "/.env");
