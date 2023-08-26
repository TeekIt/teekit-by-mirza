<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Validator;

class Role extends Model
{
    protected $fillable = [
        'name', 'display_name', 'description'
    ];


    public static function validator(Request $request)
    {
        return Validator::make($request->toArray(), [
            'name' => 'required|unique:roles|max:255',
            'display_name' => 'required',
            'description' => 'required',
        ]);
    }

    public static function updateValidator(Request $request)
    {
        return Validator::make($request->toArray(), [
            'name' => 'required|max:255',
            'display_name' => 'required',
            'description' => 'required',
        ]);
    }

    // public function users()
    // {
    //     return $this->belongsToMany('App\User', 'role_user');
    // }
    public function permissions()
    {
        return    $this->belongsToMany(Permission::class);
    }
    public function users()
    {
        return $this->belongsToMany('App\User', 'role_user');
    }
}