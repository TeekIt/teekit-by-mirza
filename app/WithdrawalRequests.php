<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequests extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}