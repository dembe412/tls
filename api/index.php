<?php

$runtimeStorage = '/tmp/laravel-storage';
$runtimeCache = '/tmp/laravel-cache';

$requiredDirectories = [
    $runtimeStorage.'/framework/views',
    $runtimeStorage.'/framework/sessions',
    $runtimeStorage.'/framework/cache',
    $runtimeStorage.'/framework/cache/data',
    $runtimeStorage.'/app/public',
    $runtimeStorage.'/logs',
    $runtimeCache,
];

foreach ($requiredDirectories as $directory) {
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
    'CACHE_STORE' => 'array',
];

foreach ($runtimeEnvironment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $e) {
    error_log('Vercel Laravel Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    if (getenv('APP_DEBUG') === 'true' || ($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo '<h1>Startup Exception</h1><p>'.htmlspecialchars($e->getMessage()).'</p><pre>'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    } else {
        throw $e;
    }
}
