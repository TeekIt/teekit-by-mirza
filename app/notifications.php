<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class notifications extends Model
{
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string'
        ]);
    }
}
