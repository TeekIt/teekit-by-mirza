<?php

namespace App\Actions\Van;

use App\Models\Van;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

final class LoginVanAction
{
    public function execute(string $userName, string $password): array
    {
        $van = Van::getByUserName($userName, ['id', 'operative', 'number_plate', 'user_name']);

        if (! Hash::check($password, $van->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $token = auth('van')->attempt([
            'user_name' => $userName,
            'password' => $password,
        ]);

        return [
            'id' => $van->id,
            'operative' => $van->operative,
            'number_plate' => $van->number_plate,
            'user_name' => $van->user_name,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }
}
