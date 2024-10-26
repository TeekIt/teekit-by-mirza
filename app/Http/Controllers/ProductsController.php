<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Imports\ProductsImport;
use App\productImages;
use App\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Qty;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use App\Services\JsonResponseServices;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelConstants;

class ProductsController extends Controller
{
    /**
     * This will help us to update the qty with the given details
     * @version 1.0.0
     */
    public function updateProductQty($product_id, $user_id, $product_quantity)
    {
        Qty::updateProductQty($product_id, $user_id, $product_quantity);
    }
    /**
     *It will insert a single product
     *and insert it's given qty to qty table
     * @version 1.0.0
     */
    public function add(Request $request)
    {
        $validate = Products::validator($request);
        if ($validate->fails()) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $validate->errors(),
                config('constants.HTTP_UNPROCESSABLE_REQUEST')
            );
        }
        $user_id = Auth::id();
        $product = new Products();
        $product->category_id = $request->category_id;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description;
        $product->color = $request->color;
        $product->size = $request->size;
        $product->lat = $request->lat;
        $product->lon = $request->lon;
        $product->price = $request->price;
        $product->qty = $request->qty;
        $product->user_id = $user_id;
        $product->save();
        //this function will add qty to it's particular table
        $product_id = $product->id;
        $product_quantity = $request->qty;
        Qty::add($user_id, $product_id, $product->category_id, $product_quantity);
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $file = $image;
                $filename = uniqid($user_id . "_" . $product->id . "_") . "." . $file->getClientOriginalExtension(); //create unique file name..
                Storage::disk('user_public')->put($filename, File::get($file));
                if (Storage::disk('user_public')->exists($filename)) {
                    info("file is store successfully : " . $filename);
                } else {
                    info("file is not found :- " . $filename);
                }
                $product_images = new productImages();
                $product_images->product_id = $product->id;
                $product_images->product_image = $filename;
                $product_images->save();
            }
        }
        $product = Products::getProductInfo($user_id, $product->id, ['*']);
        return JsonResponseServices::getApiResponse(
            $product,
            config('constants.TRUE_STATUS'),
            config('constants.DATA_INSERTION_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * Upload's bulk products
     * @author Muhammad Abdullah Mirza
     */
    public function importProductsAPI(Request $request)
    {
        try {
            $validatedData = Validator::make(
                $request->all(),
                rules: [
                    'file' => 'required|file',
                    'seller_id' => [
                        'required',
                        'integer',
                        Rule::exists('users', 'id')->where(fn(Builder $query) => $query->where('role_id', UserRole::SELLER)),
                    ]
                ],
                messages: [
                    'seller_id.exists' => 'The given :attribute either does not exist in our system or its a child seller',
                ]
            );
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            Excel::import(new ProductsImport($request->seller_id), $request->file('file'), readerType: ExcelConstants::CSV);

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.DATA_INSERTION_SUCCESS'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     *It will update a single product
     *and update the given qty in qty table
     * @version 1.0.0
     */
    public function update(Request $request, $id)
    {
        $validate = Products::validator($request);
        if ($validate->fails()) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $validate->errors(),
                config('constants.HTTP_UNPROCESSABLE_REQUEST')
            );
        }
        $user_id = Auth::id();
        $product = Products::find($id);
        if (empty($product)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
                config('constants.HTTP_INVALID_ARGUMENTS')
            );
        }
        $product->category_id = $request->category_id;
        $product->product_name = $request->product_name;
        $product->product_description = $request->product_description;
        $product->color = $request->color;
        $product->size = $request->size;
        $product->lat = $request->lat;
        $product->lon = $request->lon;
        $product->price = $request->price;
        // $product->qty = $request->qty;
        $product->user_id = $user_id;
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $file = $image;
                $filename = uniqid($user_id . "_" . $product->id . "_" . $product->product_name . '_') . "." . $file->getClientOriginalExtension(); //create unique file name...
                Storage::disk('user_public')->put($filename, File::get($file));
                if (Storage::disk('user_public')->exists($filename)) {  // check file exists in directory or not
                    info("file is store successfully : " . $filename);
                    $filename = "/user_imgs/" . $filename;
                } else {
                    info("file is not found :- " . $filename);
                }
                $product_images = new productImages();
                $product_images->product_id = $product->id;
                $product_images->product_image = $filename;
                $product_images->save();
            }
        }
        $product->save();
        //this function will update qty in it's particular table with given data
        $product_id = $product->id;
        $product_quantity = $request->qty;
        $this->updateProductQty($product_id, $user_id, $product_quantity);
        $product = Products::getProductInfo($user_id, $product->id, ['*']);
        return JsonResponseServices::getApiResponse(
            $product,
            config('constants.TRUE_STATUS'),
            config('constants.DATA_UPDATED_SUCCESS'),
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
        try {
            $validate = Validator::make($request->all(), [
                'page' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
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
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * View products in bulk with array of given ids
     * @author Muhammad Abdullah Mirza
     */
    public function bulkView(Request $request)
    {
        $ids = explode(',', $request->ids);
        $products = Products::query()->whereIn('id', $ids)->paginate();
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            foreach ($products as $product) {
                $products_data[] = Products::getProductInfo($product->id);
            }
            unset($pagination['data']);
            return JsonResponseServices::getApiResponseExtention(
                $products_data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
                config('constants.HTTP_OK')
            );
        } else {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
                config('constants.HTTP_OK')
            );
        }
    }
    /**
     *It will sort the products by price
     * @version 1.0.0
     */
    public function sortByPrice()
    {
        try {
            $products = Products::paginate()->sortBy('price');
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                $products_data = [];
                foreach ($products as $product) {
                    $products_data[] = Products::getProductInfo($product->id);
                }

                unset($pagination['data']);
                return JsonResponseServices::getApiResponseExtention(
                    $products_data,
                    config('constants.TRUE_STATUS'),
                    '',
                    'pagination',
                    $pagination,
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     *It will sort the products by location
     * @version 1.0.0
     */
    public function sortByLocation(Request $request)
    {
        $latitude = $request->get('lat');
        $longitude = $request->get('lon');
        $products = Products::select(DB::raw('*, ( 6367 * acos( cos( radians(' . $latitude . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( lat ) ) ) ) AS distance'))->paginate()->sortBy('distance');
        $pagination = $products->toArray();
        if (!empty($products)) {
            $products_data = [];
            $i = 0;
            foreach ($products as $product) {
                if ($i == 50) {
                    continue;
                }
                $i = $i + 1;
                $t = Products::getProductInfo($product->id);
                $t->distance = $product->distance;
                //$t->distance = round($product->distance);
                $products_data[] = $t;
            }
            unset($pagination['data']);
            return JsonResponseServices::getApiResponse(
                $products_data,
                config('constants.TRUE_STATUS'),
                '',
                config('constants.HTTP_OK')
            );
        } else {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
                config('constants.HTTP_OK')
            );
        }
    }
    /**
     * This function will return back store open/close & product qty status
     * Along with this information it will also send store_id & product_id
     * If the store is active & product is live
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function recheckProducts(Request $request)
    {
        try {
            $validated_data = Validator::make($request->all(), [
                'items' => 'required|array',
                'day' => 'required|string',
                'time' => 'required|string'
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->error());
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
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * View product w.r.t ID
     * @author Muhammad Abdullah Mirza
     */
    public function view(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'sellerId' => 'required|integer',
                'productId' => 'required|integer'
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            // $product = Products::getProductInfo($request->sellerId, $request->productId, ['*']);
            $data = Qty::getProductByGivenIds($request->productId, $request->sellerId);

            /*
            * Just creating this variable so we don't have to call the "empty()" function again & again
            * Which will obviouly reduce the API response speed
            */
            $dataIsEmpty = empty($data);
            return JsonResponseServices::getApiResponse(
                ($dataIsEmpty) ? [] : $data['data'],
                ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
                config('constants.HTTP_OK'),
            );

            // if (!empty($product)) {
            //     return JsonResponseServices::getApiResponse(
            //         $product,
            //         config('constants.TRUE_STATUS'),
            //         '',
            //         config('constants.HTTP_OK')
            //     );
            // }

            // return JsonResponseServices::getApiResponse(
            //     [],
            //     config('constants.FALSE_STATUS'),
            //     config('constants.NO_RECORD'),
            //     config('constants.HTTP_OK')
            // );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will delete the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    // public function delete($product_id)
    // {
    //     return Products::find($product_id)->delete();
    // }
    /**
     * It will delete the image of the given product
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function deleteImage($image_id, $product_id)
    {
        productImages::find($image_id)->delete();
        return Products::getProductInfo($product_id);
    }
    /**
     * It list the featured products
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    public function featuredProducts(Request $request)
    {
        try {
            $featured_products = (new Products())->getFeaturedProducts($request->store_id);
            $pagination = $featured_products->toArray();
            if (!$featured_products->isEmpty()) {
                $products_data = [];
                foreach ($featured_products as $product) {
                    $data = Products::getProductInfo($product->id);
                    $data->store = User::find($product->user_id);
                    $products_data[] = $data;
                }
                unset($pagination['data']);
                return JsonResponseServices::getApiResponseExtention(
                    $products_data,
                    config('constants.TRUE_STATUS'),
                    '',
                    'pagination',
                    $pagination,
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     *It will export products into csv
     * @version 1.0.0
     */
    public function exportProducts()
    {
        $user_id = Auth::id();
        $products = Products::getParentSellerProductsAsc($user_id);
        $all_products = [];
        foreach ($products as $product) {
            $pt = json_decode(json_encode(Products::getProductInfo($product->id)->toArray()));
            unset($pt->category);
            unset($pt->ratting);
            unset($pt->id);
            unset($pt->user_id);
            unset($pt->created_at);
            unset($pt->updated_at);
            $temp_img = [];
            if (isset($pt->images)) {
                foreach ($pt->images as $img)
                    $temp_img[] = $img->product_image;
            }
            $pt->images = implode(',', $temp_img);
            $all_products[] = $pt;
        }
        $destinationPath = public_path() . "/upload/csv/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file = time() . '_export.csv';
        return $this->jsonToCsv(json_encode($all_products), $destinationPath . $file, true);
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
        try {
            $validatedData = Validator::make($request->all(), [
                'productName' => 'required|string',
                'sellerIds' => 'required|string',
                'scoutPage' => 'required|integer',
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            $userLat = $request->lat;
            $userLon = $request->lon;
            $miles = $request->miles;
            if (isset($miles))
                $nearBySellerIds = $this->searchWrtNearByStores($userLat, $userLon, $miles);

            $products = Products::searchProducts(
                $request->productName,
                (isset($nearBySellerIds['ids'])) ? $nearBySellerIds['ids'] : json_decode($request->sellerIds),
                $request->categoryId,
                // json_decode($request->sellerIds),
                $request->brand,
                $request->minPrice,
                $request->maxPrice,
                $request->minWeight,
                $request->maxWeight
            );

            /*
            * Just creating this variable so we don't have to call the "empty()" function again & again
            * Which will obviouly reduce the API response speed
            */
            $dataIsEmpty = $products['data']->isEmpty();
            return JsonResponseServices::getApiResponseExtention(
                ($dataIsEmpty) ? [] : $products['data'],
                ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
                'pagination',
                ($dataIsEmpty) ? [] : $products['pagination'],
                config('constants.HTTP_OK')
            );

            // $pagination = $products->toArray();
            // $data = $pagination['data'];
            // unset($pagination['data']);

            // if (!$products->isEmpty()) {
            //     return JsonResponseServices::getApiResponseExtention(
            //         $data,
            //         config('constants.TRUE_STATUS'),
            //         '',
            //         'pagination',
            //         $pagination,
            //         config('constants.HTTP_OK')
            //     );
            // }

            // return JsonResponseServices::getApiResponse(
            //     [],
            //     config('constants.FALSE_STATUS'),
            //     config('constants.NO_RECORD'),
            //     config('constants.HTTP_OK')
            // );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It takes lat,lon  from user and store,converts them into distaance
     * and gives all store ids within given miles
     */
    public function searchWrtNearByStores($user_lat, $user_lon, $miles)
    {
        $radius = 3958.8;
        $store_data = (new User())->nearbyUsers($user_lat, $user_lon, $radius);

        foreach ($store_data as $data) {
            if ($data->distance <= $miles) {
                $store_ids[] = $data->id;
                $latitude2[] = $data->lat;
                $longitude2[] = $data->lon;
            }
        }
        $pm = $this->getDurationBetweenPointsNew($user_lat, $user_lon, $latitude2, $longitude2);
        return [
            'ids' => $store_ids,
            'time' => $pm,
        ];
    }
    /**
     *It will get the duration between given
     *lat,lon
     * @version 1.0.0
     */
    public function getDurationBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $count = count($longitude2);
        for ($i = 0; $i < $count; $i++) {
            $address2 = $latitude2[$i] . ',' . $longitude2[$i];
            $address1 = $latitude1 . ',' . $longitude1;
            //  $address2 = $latitude2 . ',' . $longitude2;
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($address1) . "&destination=" . urlencode($address2) . "&transit_routing_preference=fewer_transfers&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";
            $query = file_get_contents($url);
            $results = json_decode($query, true);
            $distanceString[] = explode(' ', $results['routes'][0]['legs'][0]['distance']['text']);
            $durationString = explode(' ', $results['routes'][0]['legs'][0]['duration']['text']);
            $miles[] = (int) $distanceString[0] * 0.621371;
            $duration[] = implode(",", $durationString);
        }
        // Google Distance Matrix
        // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&mode=driving&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";
        // return $miles > 1 ? $miles : 1;
        return $duration;
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
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required',
                'store_id' => 'required',
            ]);
            if ($validator->fails()) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validator->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                // File Details
                $filename = $file->getClientOriginalName();
                $location = public_path('upload/csv');
                $file->move($location, $filename);
                $filepath = $location . "/" . $filename;
                // Reading file
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
                    // Find product by sku, user_id, category_id and update price and quantity
                    $product = (new Products)->getProductsByParameters($request->store_id, $sku, $catgory_id);
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
                        usleep(500000); // Wait for 0.5 seconds between batches to avoid overwhelming the database
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
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Listing of all products w.r.t Seller/Store 'id'
     * @author Muhammad Abdullah Mirza
     */
    public function sellerProducts(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'seller_id' => 'required|integer',
                'page' => 'required|integer'
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            $pagination = Cache::remember('sellerProducts' . $request->seller_id . $request->page, now()->addDay(), function () use ($request) {
                return Products::getProductsInfoBySellerId($request->seller_id)->toArray();
            });
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
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
