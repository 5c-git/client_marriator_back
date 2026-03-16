<?php

use App\Logging\ExceptionLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Laravel\Passport\Exceptions\OAuthServerException;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueOAuthServerException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'scopes' => CheckScopes::class,
            'scope' => CheckForAnyScope::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/oauth/token' // <-- exclude this route
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            if($e instanceof OAuthServerException || $e instanceof LeagueOAuthServerException){
                return false;
            }
            (new ExceptionLogger())->log($e);
        });
    })->create();
