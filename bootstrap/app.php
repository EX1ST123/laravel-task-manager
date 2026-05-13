<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: ['api/*']);
        
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            $timestamp = now()->toIso8601String();

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'status'    => 404,
                    'error'     => 'Not Found',
                    'message'   => $e instanceof ModelNotFoundException
                        ? class_basename($e->getModel()) . ' not found'
                        : 'Route not found',
                    'timestamp' => $timestamp,
                ], 404);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status'    => 401,
                    'error'     => 'Unauthorized',
                    'message'   => 'Unauthenticated. Please login first.',
                    'timestamp' => $timestamp,
                ], 401);
            }

            if ($e instanceof ValidationException) {
                $status = $e->status;
                return response()->json([
                    'status'    => $status,
                    'error'     => $status === 401 ? 'Unauthorized' : 'Validation Error',
                    'message'   => $status === 401
                        ? collect($e->errors())->flatten()->first()
                        : $e->errors(),
                    'timestamp' => $timestamp,
                ], $status);
            }

            return response()->json([
                'status'    => 500,
                'error'     => 'Internal Server Error',
                'message'   => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                'timestamp' => $timestamp,
            ], 500);
        });
    })->create();