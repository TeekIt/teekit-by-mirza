<?php

use App\Http\Middleware\AuthenticateParentChildSeller;
use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\TransactionWrapper;
use App\Providers\AppServiceProvider;
use App\Services\JsonResponseServices;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jenssegers\Agent\AgentServiceProvider;
use PrettyRoutes\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        LaravelServiceProvider::class,
        AgentServiceProvider::class,
        ServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn() => route('login'));

        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->append(CheckForMaintenanceMode::class);

        $middleware->alias([
            'auth.sellers' => AuthenticateParentChildSeller::class,
            'auth.super.admin' => AuthenticateSuperAdmin::class,
            'bindings' => SubstituteBindings::class,
            'jwt.verify' => JwtMiddleware::class,
            'transaction.wrapper' => TransactionWrapper::class,
        ]);

        $middleware->priority([
            StartSession::class,
            ShareErrorsFromSession::class,
            // \App\Http\Middleware\Authenticate::class,
            AuthenticateSession::class,
            SubstituteBindings::class,
            Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $error) {});

        $exceptions->renderable(function (ValidationException $validationException, $request) {
            if ($request->is('api/*')) {
                return JsonResponseServices::getApiValidationFailedResponse($validationException->errors());
            }
        });

        $exceptions->renderable(function (HttpException $httpException) {
            if ($httpException->getStatusCode() == config('constants.HTTP_PAGE_EXPIRED')) {
                Auth::logout();

                session()->invalidate();

                return redirect()->route('home');
            }
        });

        $exceptions->renderable(function (AccessDeniedHttpException $error, $request) {
            if ($request->is('api/*')) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.UNAUTHORIZED_ACTION'),
                    config('constants.HTTP_FORBIDDEN')
                );
            }
        });

        $exceptions->renderable(function (NotFoundHttpException $error, $request) {
            if ($request->is('api/*')) {
                $previousException = $error->getPrevious();

                if ($previousException instanceof ModelNotFoundException) {
                    return JsonResponseServices::getApiResponse(
                        [],
                        config('constants.FALSE_STATUS'),
                        'No results found against id: ' . implode(',', $previousException->getIds()),
                        config('constants.HTTP_NOT_FOUND')
                    );
                }
            }
        });

        $exceptions->renderable(function (Throwable $error, $request) {
            // if ($request->is('web/*')) {
            //     return redirect()->back(config('constants.HTTP_SERVER_ERROR'))->withErrors($error->getMessage());
            // }

            if ($request->is('api/*')) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $error,
                    config('constants.HTTP_SERVER_ERROR')
                );
            }
        });
    })->create();
