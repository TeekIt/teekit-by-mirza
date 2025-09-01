<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Enums\SortByEnum;
use App\Enums\UserRoleEnum;
use App\Imports\ProductsImport;
use App\Products;
use Illuminate\Http\Request;
use App\User;
use App\Qty;
use App\Services\GoogleMapServices;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\JsonResponseServices;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelConstants;

class ProductController extends Controller
{
    /**
     * Upload's bulk products
     * @author Muhammad Abdullah Mirza
     */
    public function importProducts(Request $request)
    {
        $validatedData = Validator::make(
            $request->all(),
            rules: [
                'file' => 'required|file',
                'sellerId' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(fn(Builder $query) => $query->where('role_id', UserRoleEnum::SELLER)),
                ],
            ],
            messages: [
                'sellerId.exists' => 'The given :attribute either does not exist in our system or its a child seller',
            ]
        );
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        Excel::import(new ProductsImport($validatedData->sellerId), $request->file('file'), readerType: ExcelConstants::CSV);

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.DATA_INSERTION_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * All products listing
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    public function all(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'page' => 'required|integer'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $pagination = Products::getAllProducts()->toArray();
        $data = $pagination['data'];
        unset($pagination['data']);

        if (!empty($data)) {
            return JsonResponseServices::getApiResponseExtention(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.NO_RECORD'),
            config('constants.HTTP_OK')
        );
    }
    /**
     *It will sort the products by location
     * @version 1.0.0
     */
    // public function sortByLocation(Request $request)
    // {
    //     $latitude = $request->get('lat');
    //     $longitude = $request->get('lon');
    //     $products = Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
    //     $pagination = $products->toArray();
    //     if (!empty($products)) {
    //         $products_data = [];
    //         $i = 0;
    //         foreach ($products as $product) {
    //             if ($i == 50) {
    //                 continue;
    //             }
    //             $i = $i + 1;
    //             $t = Products::getProductInfo($product->id);
    //             $t->distance = $product->distance;
    //             //$t->distance = round($product->distance);
    //             $products_data[] = $t;
    //         }
    //         unset($pagination['data']);
    //         return JsonResponseServices::getApiResponse(
    //             $products_data,
    //             config('constants.TRUE_STATUS'),
    //             '',
    //             config('constants.HTTP_OK')
    //         );
    //     } else {
    //         return JsonResponseServices::getApiResponse(
    //             [],
    //             config('constants.FALSE_STATUS'),
    //             config('constants.NO_RECORD'),
    //             config('constants.HTTP_OK')
    //         );
    //     }
    // }
    /**
     * This function will return back store open/close & product qty status
     * Along with this information it will also send store_id & product_id
     * If the store is active & product is live
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function recheckProducts(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'items' => 'required|array',
            'day' => 'required|string',
            'time' => 'required|string'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->error());
        }

        $i = 0;
        foreach ($request->items as $item) {
            $open_time = User::select('business_hours->time->' . $request->day . '->open as open')
                ->where('id', '=', $item['store_id'])
                ->where('is_active', '=', 1)
                ->get();

            $close_time = User::select('business_hours->time->' . $request->day . '->close as close')
                ->where('id', '=', $item['store_id'])
                ->where('is_active', '=', 1)
                ->get();

            $qty = Products::select('qty')
                ->where('id', '=', $item['product_id'])
                ->where('user_id', '=', $item['store_id'])
                ->where('status', '=', 1)
                ->get();

            $order_data[$i]['store_id'] = $item['store_id'];
            $order_data[$i]['product_id'] = $item['product_id'];
            $order_data[$i]['closed'] = (strtotime($request->time) >= strtotime($open_time[0]->open) && strtotime($request->time) <= strtotime($close_time[0]->close)) ? "No" : "Yes";
            $order_data[$i]['qty'] = (isset($qty[0]->qty)) ? $qty[0]->qty : NULL;
            $i++;
        }

        return JsonResponseServices::getApiResponse(
            $order_data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
    /**
     * View product w.r.t ID
     * @author Muhammad Abdullah Mirza
     */
    public function view(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'sellerId' => 'required|integer',
            'productId' => 'required|integer'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $data = Products::getProductInfo(
            $request->sellerId,
            $request->productId,
            Products::getCommonColumns(),
        );

        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Which will obviouly decrease the API response speed
        */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK'),
        );
    }
    /**
     *helper function for exporting products
     * @version 1.0.0
     */
    public function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
        // See if the string contains something
        if (empty($json)) {
            die("The JSON string is empty!");
        }
        // If passed a string, turn it into an array
        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }
        $strTempFile = public_path() . "/upload/csv/" . 'csvOutput' . date("U") . ".csv";
        $f = fopen($strTempFile, "w+");
        $csvFilePath = $strTempFile;
        $firstLineKeys = false;
        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($f, $firstLineKeys);
                $firstLineKeys = array_flip($firstLineKeys);
            }
            // Using array_merge is important to maintain the order of keys acording to the first element
            fputcsv($f, array_merge($firstLineKeys, $line));
        }
        fclose($f);
        // Take the file and put it to a string/file for output (if no save path was included in function arguments)
        // Delete the temp file
        // unlink($strTempFile);
        return response()->download($csvFilePath, null, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
    }
    /**
     * It searches all products with w.r.t all given filters
     * @author Muhammad Abdullah Mirza
     * @version 1.7.0
     */
    public function search(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'productName' => 'required|string',
            'sellerIds' => 'required|string',
            'categoryId' => 'integer',
            'minPrice' => 'integer',
            'maxPrice' => 'integer',
            'minWeight' => 'numeric',
            'maxWeight' => 'numeric',
            'brand' => 'string',
            'miles' => 'integer',
            'lat' => 'required_with:miles|numeric|between:-90,90',
            'lon' => 'required_with:miles|numeric|between:-180,180',
            'city' => 'required_with:miles|string',
            'scoutPage' => 'required|integer',
            'sortBy' => [
                'string',
                Rule::in(array_column(SortByEnum::cases(), 'value')),
            ],
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        if (isset($validatedData->miles)) {
            $nearBySellersIds = array_column(
                GoogleMapServices::findNearByUsersByMakingChunks(
                    $validatedData->lat,
                    $validatedData->lon,
                    User::getParentAndChildSellersByCity($validatedData->city),
                    nearByMiles: $validatedData->miles,
                ),
                'id'
            );

            if (empty($nearBySellersIds)) {
                return JsonResponseServices::getApiResponseExtention(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    'pagination',
                    (object) [],
                    config('constants.HTTP_OK')
                );
            }
        }

        $products = Products::searchProducts(
            $validatedData->productName,
            (isset($nearBySellersIds)) ? $nearBySellersIds : json_decode($validatedData->sellerIds),
            $validatedData->categoryId ?? null,
            $validatedData->brand ?? null,
            $validatedData->minPrice ?? null,
            $validatedData->maxPrice ?? null,
            $validatedData->minWeight ?? null,
            $validatedData->maxWeight ?? null,
            $validatedData->sortBy ?? null,
        );
        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Which will obviouly decrease the API response speed
        */
        $dataIsEmpty = $products['data']->isEmpty();
        
        return JsonResponseServices::getApiResponseExtention(
            ($dataIsEmpty) ? [] : $products['data'],
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            'pagination',
            ($dataIsEmpty) ? (object) [] : $products['pagination'],
            config('constants.HTTP_OK')
        );
    }
    /**
     * Update product price from csv file w.r.t their SKU and store_id
     * @author Muhammad Abdullah Mirza
     *
     */
    // public function updatePriceBulk(Request $request, $delimiter = ',', $filename = '')
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'file' => 'required',
    //             'store_id' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'data' => $validator->errors(),
    //                 'status' => config('constants.FALSE_STATUS'),
    //                 'message' => ""
    //             ], 422);
    //         }
    //         if ($request->hasFile('file')) {
    //             $file = $request->file('file');
    //             // File Details
    //             $filename = $file->getClientOriginalName();
    //             $location = public_path('upload/csv');
    //             $file->move($location, $filename);
    //             $filepath = $location . "/" . $filename;
    //             // Reading file
    //             $file = fopen($filepath, "r");
    //             $i = 0;
    //             while (($filedata = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
    //                 if ($i == 0) {
    //                     $i++;
    //                     continue;
    //                 };
    //                 DB::statement('CREATE Temporary TABLE temp_products LIKE products');
    //                 $db = DB::statement('INSERT INTO `temp_products`( `user_id`, `category_id`,`product_name`, `sku`, `price`, `featured`, `discount_percentage`, `contact`)VALUES (' . $request->store_id . ',' . $filedata[0] . ',' . $filedata[0] . ',' . $filedata[1] . ',3, ' . $filedata[2] . ',1,20,02083541500 )');
    //                 DB::statement('UPDATE products,temp_products SET products.price = temp_products.price, products.updated_at = "' . Carbon::now() . '" WHERE products.user_id = temp_products.user_id AND products.category_id = temp_products.category_id AND products.sku = temp_products.sku');
    //                 DB::statement('DROP Temporary TABLE temp_products');
    //             }
    //             fclose($file);
    //             return response()->json([
    //                 'data' => [],
    //                 'status' => config('constants.TRUE_STATUS'),
    //                 'message' =>  config('constants.DATA_UPDATED_SUCCESS'),
    //             ], 200);
    //         }
    //     } catch (Throwable $error) {
    //         report($error);
    //         return response()->json([
    //             'data' => [],
    //             'status' => config('constants.FALSE_STATUS'),
    //             'message' => $error
    //         ], 500);
    //     }
    // }

    public function updatePriceAndQtyBulk(Request $request, $delimiter = ',', $filename = '', $batchSize = 1000)
    {
        ini_set('max_execution_time', 120);

        $validatedData = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv',
            'store_id' => 'required|integer',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $location = public_path('upload/csv');
        $file->move($location, $filename);
        $filepath = $location . "/" . $filename;        
        /* Reading file */
        $file = fopen($filepath, "r");
        $i = 0;
        while (($filedata = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
            if ($i == 0) {
                $i++;
                continue;
            }
            $catgory_id = $filedata[0];
            $sku = $filedata[1];
            $price = $filedata[2];
            $qty = $filedata[3];
            /* Find product by sku, user_id, category_id and update price and quantity */
            $product = Products::getProductsByParameters($request->store_id, $sku, $catgory_id);
            if ($product) {
                $product->price = $price;
                $product->save();
                $productQty = (new Qty())->getQtybyStoreAndProductId($request->store_id, $product->id);
                if (!empty($productQty)) {
                    $productQty->qty = $qty;
                    $productQty->save();
                }
            }
            $i++;
            if ($i % $batchSize == 0) {
                /* Wait for 0.5 seconds between batches to avoid overwhelming the database */
                usleep(500000);
            }
        }

        fclose($file);

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.DATA_UPDATED_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * Listing of all products w.r.t Seller 'id'
     * @author Muhammad Abdullah Mirza
     */
    public function sellerProducts(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'sellerId' => 'required|integer',
            'page' => 'required|integer'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $pagination = Cache::remember(
            'sellerProducts' . $request->sellerId . $request->page,
            now()->addHour(),
            function () use ($request) {
                return Products::getProductsInfoBySellerId(
                    $request->sellerId,
                    Products::getCommonColumns(),
                )->toArray();
            }
        );

        $data = $pagination['data'];
        unset($pagination['data']);

        if (!empty($data)) {
            return JsonResponseServices::getApiResponseExtention(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.NO_RECORD'),
            config('constants.HTTP_OK')
        );
    }
}
