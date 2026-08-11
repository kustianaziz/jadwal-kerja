<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $storage = '/tmp/storage';
    if (!is_dir($storage)) {
        mkdir($storage, 0777, true);
        mkdir($storage . '/framework/cache/data', 0777, true);
        mkdir($storage . '/framework/sessions', 0777, true);
        mkdir($storage . '/framework/views', 0777, true);
        mkdir($storage . '/logs', 0777, true);
    }
    $app->useStoragePath($storage);
}

return $app;
