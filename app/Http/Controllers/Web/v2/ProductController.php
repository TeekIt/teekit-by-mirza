<?php

namespace App\Http\Controllers\Web\v2;

use App\Models\Categories;
use App\Enums\TransportVehicleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AddOrUpdateProductRequest;
use App\Models\ProductImage;
use App\Models\Products;
use App\Models\Qty;
use App\Models\User;
use App\Services\ImageServices;
use App\Services\ProductServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function inventoryAddBulk()
    {
        return view('seller.inventory.add_bulk');
    }

    /**
     * Upload's bulk products
     *
     * @author Huzaifa Haleem
     *
     * @version 1.0.0
     */
    public function importProducts(Request $request)
    {
        $user_id = Auth::id();
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            // $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            // $tempPath = $file->getRealPath();
            // $fileSize = $file->getSize(); //Get size of uploaded file in bytes

            // Check for file extension and size
            // $this->checkUploadedFileProperties($extension, $fileSize);

            // Where uploaded file will be stored on the server
            $location = public_path('upload/csv');
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database
            $filepath = $location . '/' . $filename;
            // Reading file
            $file = fopen($filepath, 'r');
            // Read through the file and store the contents as an array
            $importData_arr = [];
            $i = 0;
            // Read the contents of the uploaded file
            while (($filedata = fgetcsv($file, 1000, ',')) !== false) {
                $num = count($filedata);
                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                    $i++;

                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file); // Close after reading
            $j = 0;
            foreach ($importData_arr as $importData) {
                $product = new Products;
                $product->user_id = $user_id;
                $product->category_id = $importData[0];
                $product->product_name = $importData[1];
                $product->sku = $importData[2];
                $product->price = str_replace(',', '', $importData[4]);
                $product->discount_percentage = ($importData[5] == '') ? 0 : $importData[5];
                $product->weight = $importData[6];
                $product->brand = $importData[7];
                $product->size = ($importData[8] == 'null') ? null : $importData[8];
                $product->status = $importData[9];
                $product->contact = $importData[10];
                $product->colors = ($importData[11] == 'null') ? null : $importData[11];
                $product->bike = $importData[12];
                $product->car = $importData[13];
                $product->van = $importData[14];
                $product->feature_img = $importData[18];
                $product->height = $importData[15];
                $product->width = $importData[16];
                $product->length = $importData[17];
                $product->save();

                // this function will add qty to it's parti;cular table
                $product_id = (int) $product->id;
                $product_quantity = ($importData[3] == '') ? 0 : $importData[3];
                Qty::add($user_id, $product_id, $product->category_id, $product_quantity);
                ProductImage::add((int) $product->id, $importData[18]);
                $j++;
            }
        }

        session()->flash('success', 'Your Bulk Products Have Been Imported Successfully!');

        return redirect()->back();
    }
}
