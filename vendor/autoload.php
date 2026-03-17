<?php

spl_autoload_register(function ($class) {

    $prefix = 'Smalot\\PdfParser\\';

    $base_dir = __DIR__ . '/smalot/pdfparser/src/Smalot/PdfParser/';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});