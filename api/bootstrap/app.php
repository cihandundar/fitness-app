<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'trainer' => \App\Http\Middleware\TrainerMiddleware::class,
            'guest' => \App\Http\Middleware\GuestMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API JSON Response for all exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                $message = 'Sunucu hatası oluştu';

                // Authentication errors
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Oturum açmanız gerekiyor';
                }

                // Authorization errors
                elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $statusCode = 403;
                    $message = 'Bu işleme yetkiniz yok';
                }

                // Model not found
                elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Kayıt bulunamadı';
                }

                // Validation errors
                elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Doğrulama hatası',
                        'errors' => $e->errors(),
                    ], $statusCode);
                }

                // Database errors
                elseif ($e instanceof \Illuminate\Database\QueryException) {
                    $statusCode = 500;
                    $message = 'Veritabanı hatası oluştu';
                }

                // Rate limiting
                elseif ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    $statusCode = 429;
                    $message = 'Çok fazla istek, lütfen bekleyin';
                }

                // Production: Don't expose error details
                $debug = config('app.debug');
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'error' => $debug ? [
                        'type' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], $statusCode);
            }
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Her gün gece yarısı gün düşme komutu çalışır
        $schedule->command('memberships:decrement-days')->daily();
    })
    ->create();
