<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Van extends Model
{
    /** @use HasFactory<\Database\Factories\VanFactory> */
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relations
     */

    /**
     * Helpers
     */
    public static function getAll(OrderByEnum $orderBy, array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)
            ->orderBy('created_at', $orderBy->value)
            ->paginate(3);
    }

    public static function addOrUpdate(string $operative, string $numberPlate): Van
    {
        return self::updateOrCreate(
            ['number_plate' => $numberPlate],
            [
                'operative' => $operative,
                'number_plate' => $numberPlate,
            ]
        );
    }
}
