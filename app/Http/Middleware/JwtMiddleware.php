<?php

namespace App\Http\Middleware;

use App\Services\JsonResponseServices;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        try {
            ($guard) ? auth($guard)->userOrFail() : JWTAuth::parseToken()->authenticate();
        } catch (Exception $error) {
            if ($error instanceof TokenInvalidException) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'Token is Invalid',
                    config('constants.HTTP_UNAUTHORIZED')
                );
            } elseif ($error instanceof TokenExpiredException) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'Token is Expired',
                    config('constants.HTTP_UNAUTHORIZED')
                );
            } else {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'Authorization Token not found or Token belongs to a different Guard',
                    config('constants.HTTP_UNAUTHORIZED')
                );
            }
        }

        return $next($request);
    }
}
