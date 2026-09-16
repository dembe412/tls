<?php

$runtimeStorage = '/tmp/laravel-storage';
$runtimeCache = '/tmp/laravel-cache';

foreach ([$runtimeStorage, $runtimeCache] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$runtimeEnvironment = [
    'LARAVEL_STORAGE_PATH' => $runtimeStorage,
    'APP_SERVICES_CACHE' => $runtimeCache.'/services.php',
    'APP_PACKAGES_CACHE' => $runtimeCache.'/packages.php',
    'APP_CONFIG_CACHE' => $runtimeCache.'/config.php',
    'APP_ROUTES_CACHE' => $runtimeCache.'/routes.php',
    'APP_EVENTS_CACHE' => $runtimeCache.'/events.php',
    'APP_COMPILED_VIEW_PATH' => $runtimeStorage.'/framework/views',
    'LOG_CHANNEL' => 'stderr',
];

foreach ($runtimeEnvironment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../public/index.php';
