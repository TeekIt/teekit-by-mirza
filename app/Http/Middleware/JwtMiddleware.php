<?php

namespace App\Http\Middleware;

use App\Services\JsonResponseServices;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $error) {
            if ($error instanceof TokenInvalidException) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'Token is Invalid',
                    config('constants.HTTP_UNAUTHORIZED')
                );
            } else if ($error instanceof TokenExpiredException) {
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
                    'Authorization Token not found',
                    config('constants.HTTP_UNAUTHORIZED')
                );
            }
        }

        return $next($request);
    }
}
