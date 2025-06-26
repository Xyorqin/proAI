<?php

use App\Exceptions\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (NotFoundHttpException $e) {
            // Optionally log or handle reporting here
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'message' => 'Model not found',
                    'error' => $previous->getMessage()
                ], 404);
            }
            return response()->json([
                'message' => 'Not found',
                'error' => $e->getMessage()
            ], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->errorJson($e->getMessage(), $e->getStatusCode());
        });
    })->create();
