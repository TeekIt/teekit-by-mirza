<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCodeRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'referred_by',
        'user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relations
     */
    public function referredByUser()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Helpers
     */
    public static function usingReferralFirstTime(int $userId): bool
    {
        return is_null(self::where('user_id', '=', $userId)->first());
    }

    public static function insertReferralRelation(int $referredBy, int $userId): ReferralCodeRelation
    {
        return self::create([
            'referred_by' => $referredBy,
            'user_id' => $userId,
        ]);
    }

    public static function getReferralRelationDetails(int $referralRelationId): Collection
    {
        return self::with('referredByUser')->where('id', '=', $referralRelationId)->get();
    }

    public static function updateReferralRelationStatus(int $referralRelationId, int $referralUseable): int
    {
        return self::where('id', $referralRelationId)
            ->update(['referral_useable' => $referralUseable]);
    }
}
