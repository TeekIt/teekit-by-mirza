<?php

namespace App\Http\Middleware;

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
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                $response = array('data' => [], 'status' => false, 'message' => 'Token is Invalid');
                return response()->json($response, 401);
            } else if ($e instanceof TokenExpiredException) {
                $response = array('data' => [], 'status' => false, 'message' => 'Token is Expired');
                return response()->json($response, 401);
            } else {
                $response = array('data' => [], 'status' => false, 'message' => 'Authorization Token not found');
                return response()->json($response, 401);
            }
        }

        return $next($request);
    }
}
