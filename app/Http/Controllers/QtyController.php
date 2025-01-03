<?php

namespace App\Http\Controllers;

use App\Products;
use App\Qty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class QtyController extends Controller
{
    /**
     * It will return a specific product's qty
     * @version 1.0.0
     */
    public function getById(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'store_id' => 'required|integer',
                'prod_id' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $qty = Qty::where('seller_id', $request->store_id)
                ->where('product_id', $request->prod_id)
                ->get();
            if (!is_null($qty)) {
                return response()->json([
                    'data' => Qty::where('seller_id', $request->store_id)
                        ->where('product_id', $request->prod_id)
                        ->get(),
                    'status' => true,
                    'message' => ''
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => true,
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
    /**
     * It will update a specific product's qty
     * @version 1.0.0
     */
    public function updateById(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'store_id' => 'required|integer',
                'prod_id' => 'required|integer',
                'qty' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' =>  $validate->errors()
                ], 422);
            }
            DB::table('qty_tests')->where('seller_id', $request->store_id)
                ->where('product_id', $request->prod_id)
                ->update(['qty' => $request->qty]);
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.DATA_UPDATED_SUCCESS')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It is used to test API's respose time
     * By sending them bulk requests in a single attempt
     * @version 1.9.0
     */
    public function multiCURL()
    {
        try {
            // *************Multi CURL
            for ($times = 0; $times < 100; $times++) {
                // create both cURL resources
                $ch[$times] = curl_init();
                curl_setopt_array($ch[$times], array(
                    CURLOPT_URL => 'https://teekitstaging.shop/api/qty/all',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
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
            print_r($mh);
            exit;
            return response()->json([
                'data' => $mh,
                'status' => true,
                'message' => config('constants.DATA_UPDATED_SUCCESS')
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
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
            return Redirect::back()->withInput($request->input());
        }
        Qty::updateOrInsert(
            ['seller_id' => Auth::id(), 'product_id' => $request->input('product_id')],
            ['qty' => $request->input('qty')]
        );
        return response()->json([
            'status' => 200,
            'error' => 'false',
            'qty' => $request->input('qty')
        ]);
    }
    /**
     * This method will share parent store
     * qty with their child store
     * @version 1.0.0
     */
    public function insertParentQtyToChild(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'parent_store' => 'required|int',
                'child_store' => 'required|int'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            $parent_store_data = Qty::where('seller_id', $request->parent_store)->get();
            $child_store_data = Qty::where('seller_id', $request->child_store)->first();
            if (!is_null($child_store_data)) {
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.DATA_ALREADY_EXISTS') . $request->child_store
                ], 200);
            } elseif ($parent_store_data->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.NO_SELLER')
                ], 200);
            }
            /**
             * Split data into chunks of 1000 rows each
             */
            $chunked_data = array_chunk($parent_store_data->toArray(), 1000);
            foreach ($chunked_data as $chunk) {
                $data = [];
                foreach ($chunk as $item) {
                    $data[] = [
                        'seller_id' => $request->child_store,
                        'product_id' => $item['product_id'],
                        'category_id' => $item['category_id'],
                        'qty' => $item['qty'],
                        'created_at' => Carbon::now()
                    ];
                }
                Qty::insert($data);
            }
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.DATA_INSERTION_SUCCESS')
            ], 200);
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
