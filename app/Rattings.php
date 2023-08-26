<?php

namespace App;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Rattings extends Model
{
    public static function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'ratting' => 'required',
            'product_id' => 'required',
        ]);
    }

    public static function updateValidator(Request $request)
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
    public static function getRatting(int $product_id)
    {
        $raw_ratting = Rattings::where('product_id', '=', $product_id);
        $average = $raw_ratting->avg('ratting');
        $all_raw = $raw_ratting->get();
        $all = [];
        foreach ($all_raw as $aw) {
            $aw->user = (new AuthController)->get_user($aw->user_id);
            $all[] = $aw;
        }
        return ['average' => $average, 'all' => $all];
    }
}
