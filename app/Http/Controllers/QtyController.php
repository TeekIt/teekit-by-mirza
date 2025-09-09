<?php

namespace App\Http\Controllers;

use App\Qty;
use App\Services\JsonResponseServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QtyController extends Controller
{
    /**
     * It will return a specific product's qty
     * @version 1.0.0
     */
    public function getById(Request $request)
    {
        $validatedData = Validator::make($request->route()->parameters(), [
            'store_id' => 'required|integer',
            'prod_id' => 'required|integer'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $qty = Qty::where('seller_id', $request->store_id)
            ->where('product_id', $request->prod_id)
            ->get();

        if (!is_null($qty)) {
            return JsonResponseServices::getApiResponse(
                $qty,
                config('constants.TRUE_STATUS'),
                '',
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.NO_RECORD'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * It is used to test API's respose time
     * By sending them bulk requests in a single attempt
     * @version 1.9.0
     */
    public function multiCURL()
    {
        // *************Multi CURL
        for ($times = 0; $times < 100; $times++) {
            // create both cURL resources
            $ch[$times] = curl_init();
            curl_setopt_array($ch[$times], [
                CURLOPT_URL => 'https://teekitstaging.shop/api/qty/all',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);
        }

        //create the multiple cURL handle
        $mh = curl_multi_init();
        for ($a = 0; $a < count($ch); $a++)
            curl_multi_add_handle($mh, $ch[$a]);

        //execute the multi handle
        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        //close the handles
        for ($a = 0; $a < count($ch); $a++)
            curl_multi_remove_handle($mh, $ch[$a]);
        curl_multi_close($mh);

        return JsonResponseServices::getApiResponse(
            $mh,
            config('constants.TRUE_STATUS'),
            config('constants.DATA_UPDATED_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * It edits the qty for a child store
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
            'qty' => $request->input('qty')
        ]);
    }
    /**
     * This method will share parent store
     * qty with their child store
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
