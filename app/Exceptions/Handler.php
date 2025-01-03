<?php

namespace App\Exceptions;

use App\Services\JsonResponseServices;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $error) {});

        $this->renderable(function (Throwable $error, $request) {
            if ($request->is('api/*')) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $error,
                    config('constants.HTTP_SERVER_ERROR')
                );
            }
        });

        $this->renderable(function (HttpException $httpException) {
            if ($httpException->getStatusCode() == config('constants.HTTP_PAGE_EXPIRED')) {
                Auth::logout();

                session()->invalidate();
                
                return redirect()->route('home');
            }
        });
    }
}
