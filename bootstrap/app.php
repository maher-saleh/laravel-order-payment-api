<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\DomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Helper function to detect API requests
        $isApiRequest = function ($request) {
            return str_starts_with($request->path(), 'api/');
        };

        // Domain/business exceptions (422)
        $exceptions->render(function (DomainException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status'  => 422,
                ], 422);
            }
        });

        // Generic API 404 (covers route not found + model not found)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {  // Only check path, not Accept header
                return response()->json([
                    'message' => 'Resource not found',
                    'status'  => 404,
                ], 404);
            }
        });

        // Model not found (404)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'Resource not found',
                    'status'  => 404,
                ], 404);
            }
        });

        // Authorization failure (403)
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action',
                    'status'  => 403,
                ], 403);
            }
        });

        // Validation error (422)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                    'status'  => 422,
                ], 422);
            }
        });

        // Unauthenticated (401)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status'  => 401,
                ], 401);
            }
        });

    })->create();