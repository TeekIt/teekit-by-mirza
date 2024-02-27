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

    public static function getWithdrawalResquests(int $user_id ,string $search = null ,int $amount = null ,string $created_at = null)
    {
        return self::select('id', 'amount', 'status', 'transaction_id', 'created_at')->where('user_id', $user_id)            
            ->when($search, function ($query, $search) {
                return $query->where('status', $search);
            })
            ->when($amount, function ($query, $amount) {
                return $query->where('amount', $amount);
            })
            ->when($created_at, function ($query, $created_at) {
                return $query->whereDate('created_at', $created_at);
            });
    }
}
