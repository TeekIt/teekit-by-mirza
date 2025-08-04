<?php

namespace App;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Helpers
     */
    public static function getParentAndChildSellersWithdrawalRequests(): Collection
    {
        return  self::whereHas('user', function ($query) {
            $query->whereIn('role_id', [UserRoleEnum::SELLER, UserRoleEnum::CHILD_SELLER]);
        })->get();
    }

    public static function getWithdrawalRequests(
        int $userId,
        ?string $search = null,
        ?int $amount = null,
        ?string $createdAt = null
    ): Builder {
        return self::query()
            ->select('id', 'amount', 'status', 'transaction_id', 'created_at')
            ->where('user_id', $userId)
            ->when($search, fn($query) => $query->where('status', $search))
            ->when($amount, fn($query) => $query->where('amount', $amount))
            ->when($createdAt, fn($query) => $query->whereDate('created_at', $createdAt));
    }

    public static function add(
        int $userId, 
        int $amount, 
        string $status, 
        string $bankDetails
    ): WithdrawalRequests {
        return self::create([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $status,
            'bank_detail' => $bankDetails,
        ]);
    }
}
