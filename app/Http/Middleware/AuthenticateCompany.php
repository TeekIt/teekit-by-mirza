<?php

namespace App\Http\Middleware;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if (User::getAuthUser()->role_id === UserRoleEnum::COMPANY->value) {
            return $next($request);
        }

        abort(403, 'Access Denied');
    }
}
