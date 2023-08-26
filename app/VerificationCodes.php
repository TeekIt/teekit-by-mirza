<?php

namespace App;

use App\Http\Controllers\RattingsController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Http\Request;
use Validator;
use DB;

class VerificationCodes extends Model
{
    //
    public static function getProductInfo($product_id){
        $product = Products::find($product_id);
        $product->images = productImages::query()->where('product_id','=',$product->id)->get();
        $product->category = Categories::find($product->category_id);
        $product->ratting = (new RattingsController())->getRatting($product_id);
        return $product;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
