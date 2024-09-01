<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalRequests extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'bank_detail',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // public function role(): BelongsTo
    // {
    //     return $this->belongsTo('App\Role');
    // }

    public static function getWithdrawalResquests(int $user_id, string $search = null, int $amount = null, string $created_at = null)
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

    public static function add(int $user_id, int $amount, string $status , string $bank_details): WithdrawalRequests
    {
        return self::create([
            'user_id' => $user_id,
            'amount' => $amount,
            'status' => $status,
            'bank_detail' => $bank_details,
        ]);
    }
}
