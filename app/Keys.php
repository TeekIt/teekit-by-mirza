<?php

namespace App;

use App\Http\Controllers\RattingsController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Http\Request;
use Validator;
use DB;

class Keys extends Model
{
    // This Model is only required for the SECRET KEYS information API
}
