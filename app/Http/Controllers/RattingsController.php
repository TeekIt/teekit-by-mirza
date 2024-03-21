<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use App\Rattings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RattingsController extends Controller
{
    /**
     *It will add rating to a specific product    
     * @version 1.0.0
     */
    public function add(Request $request)
    {
        $validate = Rattings::validator($request);
        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
        $user_id = Auth::id();
        $response = [];
        $ratting = new Rattings();
        $ratting->user_id = $user_id;
        $ratting->product_id = $request->get('product_id');
        $ratting->ratting = $request->get('ratting');
        $ratting->save();
        return (new ProductsController)->view($request->get('product_id'));
    }
    /**
     *It will update rating of a specific product    
     * @version 1.0.0
     */
    public function update(Request $request)
    {
        $validate = Rattings::updateValidator($request);
        if ($validate->fails()) {
            $response = array('status' => false, 'message' => 'Validation error', 'data' => $validate->messages());
            return response()->json($response, 400);
        }
        $user_id = Auth::id();
        $response = [];
        $ratting = Rattings::find($request->get('id'));
        //  $ratting->user_id=$user_id;
        //  $ratting->product_id=$request->get('product_id');
        $ratting->ratting = $request->get('ratting');
        $ratting->save();
        return (new ProductsController)->view($request->get('product_id'));
    }
    /**
     *It will delete rating of a specific product    
     * @version 1.0.0
     */
    public function delete($ratting_id)
    {
        try {
            $delete_rating =  Rattings::find($ratting_id);
            if ($delete_rating) {
                $delete_rating->delete();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
}
