<?php

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $exception) {
    error_log($exception::class.': '.$exception->getMessage());

    throw $exception;
}
