<?php

namespace App;

use App\Http\Controllers\UsersController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Rattings extends Model
{
    use HasFactory, SoftDeletes;
    
    public static function validator(Request $request): object
    {
        return Validator::make($request->all(), [
            'ratting' => 'required',
            'product_id' => 'required',
        ]);
    }

    public static function updateValidator(Request $request): object
    {
        return Validator::make($request->all(), [
            'ratting' => 'required',
            'product_id' => 'required',
            'id' => 'required',
        ]);
    }
    /**
     * Helpers
     */
    public static function getRatting(int $product_id): array
    {
        $raw_ratting = self::where('product_id', '=', $product_id);
        $average = $raw_ratting->avg('ratting');
        $all_raw = $raw_ratting->get();
        $all = [];
        foreach ($all_raw as $aw) {
            $aw->user = UsersController::getSellerInfo(User::find($aw->user_id));
            $all[] = $aw;
        }
        return ['average' => $average, 'all' => $all];
    }
}
