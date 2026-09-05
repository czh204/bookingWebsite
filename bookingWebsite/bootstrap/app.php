<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // api/* always gets JSON errors. The expectsJson() arm restores the
        // framework default for anything that explicitly asks for JSON —
        // without it, a failed validation on POST /cart came back as an
        // HTML redirect, which the Add to Cart fetch() can't read. Normal
        // form posts send Accept: text/html, so they still redirect back
        // with the error bag as before.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
