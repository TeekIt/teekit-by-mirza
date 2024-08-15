<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Notification extends Model
{
    use HasFactory;

    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string'
        ]);
    }
}
