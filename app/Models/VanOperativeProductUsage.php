<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VanOperativeProductUsage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Fields protected from mass assignment
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Attribute type casting
     *
     * @var array
     */
    protected $casts = [
        'quantity_used' => 'integer',
        'used_at'       => 'datetime',
    ];

    /**
     * Relations
     */
    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function vanProduct(): BelongsTo
    {
        return $this->belongsTo(VanProduct::class, 'van_product_id');
    }

    public static function add(array $data): VanOperativeProductUsage
    {
        return self::create([
            'van_id'        => $data['vanId'],
            'van_product_id' => $data['productId'],
            'quantity_used' => $data['quantityUsed'],
            'job_reference' => $data['jobReference'],
            'used_at'       => $data['timestamp'] ?? now(),
        ]);
    }

    public static function getAll(
        OrderByEnum $orderBy,
        ?int $vanId = null,
        int $perPage = 10,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return self::select($columns)
            ->with(['vanProduct:id,product_name'])
            ->when($vanId, function ($query) use ($vanId) {
                $query->where('van_id', '=', $vanId);
            })
            ->orderBy('used_at', $orderBy->value)
            ->paginate($perPage);
    }
}
