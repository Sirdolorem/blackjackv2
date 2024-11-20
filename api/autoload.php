<?php
spl_autoload_register(function ($class) {
    // Define the base directory for your classes
    $baseDir = __DIR__ . '/Classes/';

    // Remove the namespace prefix
    $class = str_replace('blackjack\\', '', $class);

    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    // Require the file if it exists
    if (file_exists($file)) {
        require $file;
    }
});
