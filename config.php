<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'u95122db_123');
define('DB_USER', 'u95122db_123');
define('DB_PASS', '4oATr3vKqMi*');
define('PER_PAGE', 10);

spl_autoload_register(function($class) {
    $paths = ['repositories/', 'controllers/'];
    foreach ($paths as $path) {
        $file = __DIR__ . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/helpers.php';