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
    public static function add(
        string $username,
        string $operative,
        string $numberPlate,
        int $payload,
        float $width,
        float $height,
        float $length,
        string $password
    ): Van {
        return self::create([
            'username' => $username,
            'operative' => $operative,
            'number_plate' => $numberPlate,
            'payload' => $payload,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'password' => bcrypt($password),
        ]);
    }

    public static function updateInfo(
        int $id,
        ?string $username = null,
        ?string $operative = null,
        ?string $numberPlate = null,
        ?int $payload = null,
        ?float $width = null,
        ?float $height = null,
        ?float $length = null,
        ?string $password = null
    ): bool {
        $van = self::findOrFail($id);

        if (! is_null($username)) {
            $van->username = $username;
        }

        if (! is_null($operative)) {
            $van->operative = $operative;
        }

        if (! is_null($numberPlate)) {
            $van->number_plate = $numberPlate;
        }

        if (! is_null($payload)) {
            $van->payload = $payload;
        }

        if (! is_null($width)) {
            $van->width = $width;
        }

        if (! is_null($height)) {
            $van->height = $height;
        }

        if (! is_null($length)) {
            $van->length = $length;
        }
        
        if (! is_null($password)) {
            $van->password = bcrypt($password);
        }

        return $van->save();
    }

    public static function getAll(OrderByEnum $orderBy, string $search = '', array $columns = ['*']): LengthAwarePaginator
    {
        return self::select($columns)
            ->when($search, function ($query) use ($search) {
                $searchValue = trim(mb_strtolower($search));
                $query->where(function ($query) use ($searchValue) {
                    $query->where('username', 'like', '%' . $searchValue . '%')
                        ->orWhere('operative', 'like', '%' . $searchValue . '%')
                        ->orWhere('number_plate', 'like', '%' . $searchValue . '%');
                });
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate(10);
    }
}
