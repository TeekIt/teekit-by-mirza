<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function destroy(Request $request)
    {
        User::adminUsersDel($request);

        return response(config('constants.USERS_DELETION_SUCCESS'));
    }
}
