<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Cria pastas temporárias graváveis
$tmpBase = '/tmp/laravel';
$dirs = ['/views', '/cache', '/logs'];
foreach ($dirs as $dir) {
    if (!is_dir($tmpBase . $dir)) {
        @mkdir($tmpBase . $dir, 0755, true);
    }
}

// Redireciona TODAS as escritas do Laravel para o /tmp
putenv("VIEW_COMPILED_PATH={$tmpBase}/views");
putenv("APP_SERVICES_CACHE={$tmpBase}/cache/services.php");
putenv("APP_PACKAGES_CACHE={$tmpBase}/cache/packages.php");
putenv("APP_CONFIG_CACHE={$tmpBase}/cache/config.php");
putenv("APP_ROUTES_CACHE={$tmpBase}/cache/routes.php");
putenv("APP_EVENTS_CACHE={$tmpBase}/cache/events.php");
putenv("SESSION_DRIVER=cookie"); // Evita gravar sessões em disco
putenv("LOG_CHANNEL=stderr");

$_ENV['VIEW_COMPILED_PATH'] = $tmpBase . '/views';
$_ENV['LOG_CHANNEL'] = 'stderr';

require __DIR__ . '/../public/index.php';