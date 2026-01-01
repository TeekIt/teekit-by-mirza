<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class TransactionWrapper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        DB::beginTransaction();
        /* Process the request */
        $response = $next($request);

        if (isset($response->exception)) {
            DB::rollBack();

            return $response;
        }
        /* If no exceptions, commit the changes */
        DB::commit();

        return $response;
    }
}
