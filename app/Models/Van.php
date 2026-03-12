<?php

namespace App\Models;

use App\Enums\OrderByEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function userName(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower($value),
        );
    }
    /**
     * Relations
     */

    /**
     * Helpers
     */
    public function getValidationRules(?int $vanId = null): array
    {
        return [
            'userName' => ($vanId ? "unique:vans,user_name,{$vanId}" : 'unique:vans,user_name') . '|required|string|max:20|regex:/^\S+$/',
            'operative' => 'required|string|max:20',
            'numberPlate' => ($vanId ? "unique:vans,number_plate,{$vanId}" : 'unique:vans,number_plate') . '|required|string|max:20',
            'payload' => 'required|integer|min:1',
            'width' => 'required|numeric|min:1.0',
            'height' => 'required|numeric|min:1.0',
            'length' => 'required|numeric|min:1.0',
            'password' => ($vanId ? 'nullable' : 'required') . '|string|min:6',
        ];
    }

    public static function add(
        string $userName,
        string $operative,
        string $numberPlate,
        int $payload,
        float $width,
        float $height,
        float $length,
        string $password
    ): Van {
        return self::create([
            'company_id' => User::getAuthUser()->id,
            'user_name' => $userName,
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
        ?string $userName = null,
        ?string $operative = null,
        ?string $numberPlate = null,
        ?int $payload = null,
        ?float $width = null,
        ?float $height = null,
        ?float $length = null,
        ?string $password = null
    ): bool {
        $van = self::findOrFail($id);

        if (! is_null($userName)) {
            $van->user_name = $userName;
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
                $search = trim(mb_strtolower($search));
                $query->where(function ($query) use ($search) {
                    $query->where('user_name', 'like', '%' . $search . '%')
                        ->orWhere('operative', 'like', '%' . $search . '%')
                        ->orWhere('number_plate', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', $orderBy->value)
            ->paginate(10);
    }
}
