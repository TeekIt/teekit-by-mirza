<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionAndServiceFee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'commission',
        'service_fee',
    ];
    /**
     * Relations
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    /**
     * Helpers
     */
    public static function updateOrAdd(int $seller_id, array $commission = [], array $service_fee = []): CommissionAndServiceFee
    {
        $data = [];

        if (!empty($commission)) {
            $data['commission'] = json_encode($commission);
        }
    
        if (!empty($service_fee)) {
            $data['service_fee'] = json_encode($service_fee);
        }
    
        return self::updateOrCreate(
            ['seller_id' => $seller_id],
            $data
        );
    }
}
