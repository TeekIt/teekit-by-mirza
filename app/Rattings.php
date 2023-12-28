<?php

namespace App;

use App\Http\Controllers\UsersController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Rattings extends Model
{
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
    // public static function getRattingAverage(){
    //     ret
    // }

    public static function getRatting(int $product_id): array
    {
        $raw_ratting = Rattings::where('product_id', '=', $product_id);
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
