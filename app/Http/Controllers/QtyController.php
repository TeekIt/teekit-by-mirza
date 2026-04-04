<?php

namespace App\Http\Controllers;

use App\Models\Qty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QtyController extends Controller
{
    /**
     * It edits the qty for a child store
     *
     * @version 1.0.0
     */
    public function updateChildQty(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'qty' => 'required|int|min:0',
        ]);
        if ($validatedData->fails()) {
            flash('Invalid data')->error();

            return redirect()->back()->withInput($request->input());
        }

        Qty::updateOrInsert(
            ['seller_id' => Auth::id(), 'product_id' => $request->input('product_id')],
            ['qty' => $request->input('qty')]
        );

        return response()->json([
            'status' => config('constants.HTTP_OK'),
            'error' => 'false',
            'qty' => $request->input('qty'),
        ]);
    }
    
    /**
     * This method will share parent store
     * qty with their child store
     *
     * @version 1.0.0
     */
    // public function insertParentQtyToChild(Request $request)
    // {
    //     $validatedData = Validator::make($request->all(), [
    //         'parent_store' => 'required|int',
    //         'child_store' => 'required|int'
    //     ]);
    //     if ($validatedData->fails()) {
    //         return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
    //     }

    //     $parent_store_data = Qty::where('seller_id', $request->parent_store)->get();
    //     $child_store_data = Qty::where('seller_id', $request->child_store)->first();
    //     if (!is_null($child_store_data)) {
    //         return JsonResponseServices::getApiResponse(
    //             [],
    //             config('constants.TRUE_STATUS'),
    //             config('constants.DATA_ALREADY_EXISTS') . $request->child_store,
    //             config('constants.HTTP_OK')
    //         );
    //     } elseif ($parent_store_data->isEmpty()) {
    //         return JsonResponseServices::getApiResponse(
    //             [],
    //             config('constants.TRUE_STATUS'),
    //             config('constants.NO_SELLER'),
    //             config('constants.HTTP_OK')
    //         );
    //     }
    //     /**
    //      * Split data into chunks of 1000 rows each
    //      */
    //     $chunked_data = array_chunk($parent_store_data->toArray(), 1000);
    //     foreach ($chunked_data as $chunk) {
    //         $data = [];
    //         foreach ($chunk as $item) {
    //             $data[] = [
    //                 'seller_id' => $request->child_store,
    //                 'product_id' => $item['product_id'],
    //                 'category_id' => $item['category_id'],
    //                 'qty' => $item['qty'],
    //                 'created_at' => Carbon::now()
    //             ];
    //         }
    //         Qty::insert($data);
    //     }

    //     return JsonResponseServices::getApiResponse(
    //         [],
    //         config('constants.TRUE_STATUS'),
    //         config('constants.DATA_INSERTION_SUCCESS'),
    //         config('constants.HTTP_OK')
    //     );
    // }
}
