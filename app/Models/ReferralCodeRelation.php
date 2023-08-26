<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCodeRelation extends Model
{
    use HasFactory;
    protected $fillable = [
        'referred_by',
        'user_id'
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
    public static function usingReferalFirstTime(int $user_id)
    {
        $data = ReferralCodeRelation::where('user_id', $user_id)->first();
        return (is_null($data)) ? true :  false;
    }

    public static function insertReferralRelation(int $referred_by, int $user_id)
    {
        return ReferralCodeRelation::create([
            'referred_by' => $referred_by,
            'user_id'   => $user_id
        ]);
    }

    public static function getReferralRelationDetails(int $referral_relation_id)
    {
        return ReferralCodeRelation::with('referredByUser')->where('id', $referral_relation_id)->get();
    }

    public static function updateReferralRelationStatus(int $referral_relation_id, int $referral_useable)
    {
        return ReferralCodeRelation::where('id', $referral_relation_id)->update(['referral_useable' => $referral_useable]);
    }
}
