<?php

namespace App\Http\Controllers\Web\v2;

use App\Http\Controllers\Controller;
use App\Categories;
use App\Enums\TransportVehicleEnum;
use App\Models\ProductImage;
use App\Products;
use Illuminate\Http\Request;
use App\Http\Requests\AddOrUpdateProductRequest;
use App\Qty;
use App\Services\ImageServices;
use App\Services\ProductServices;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * It will redirect us to add
     * inventory page
     * @version 1.0.0
     */
    public function addSingleInventoryForm(Request $request)
    {
        $categories = Categories::all();

        return view('shopkeeper.inventory.add', compact('categories'));
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function addSingleInventory(AddOrUpdateProductRequest $request)
    {
        $data = $request->validated();

        if ($request->has('colors')) {
            $data['colors'] = ProductServices::jsonEncodeColors($data['colors']);
        }

        $data['bike'] = ($data['vehicle'] == TransportVehicleEnum::BIKE->value) ? 1 : 0;
        $data['car'] = ($data['vehicle'] == TransportVehicleEnum::CAR->value) ? 1 : 0;
        $data['van'] = ($data['vehicle'] == TransportVehicleEnum::VAN->value) ? 1 : 0;
        $data['discount_percentage'] = (!isset($data['discount_percentage'])) ? 0.00 : $data['discount_percentage'];
        $data['contact'] = '+44' . $data['contact'];
        $data['seller_id'] = auth()->id();
        $data['feature_img'] = ImageServices::uploadImg($request, 'feature_img', $data['seller_id']);

        unset($data['_token']);
        unset($data['color']);
        unset($data['gallery']);
        unset($data['qty']);
        unset($data['vehicle']);

        $product = Products::add($data);

        Qty::add($data['seller_id'], $product->id, $data['category_id'], $request->safe()->only(['qty'])['qty']);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $singleImage) {
                $uniqueId = $data['seller_id'] . $product->id;
                $fileName = ImageServices::uploadImg(id: $uniqueId, imageFile: $singleImage);

                ProductImage::add($product->id, $fileName);
            }
        }

        return redirect()->route('seller.inventory');
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function editInventoryView($productId)
    {
        $categories = Categories::all();

        $inventory = Products::getProductInfoEvenDisabled(auth()->id(), $productId, ['*']);

        return view('shopkeeper.inventory.edit', compact('inventory', 'categories'));
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function updateInventory(AddOrUpdateProductRequest $request, $productId)
    {
        $data = $request->validated();

        $data['colors'] = ($request->has('colors')) ? ProductServices::jsonEncodeColors($data['colors']) : null;

        if ($request->hasFile('feature_img')) {
            $data['feature_img'] = ImageServices::uploadImg($request, 'feature_img', $data['seller_id']);
        }

        $data['bike'] = ($data['vehicle'] == TransportVehicleEnum::BIKE->value) ? 1 : 0;
        $data['car'] = ($data['vehicle'] == TransportVehicleEnum::CAR->value) ? 1 : 0;
        $data['van'] = ($data['vehicle'] == TransportVehicleEnum::VAN->value) ? 1 : 0;
        $data['discount_percentage'] = $data['discount_percentage'] ?? 0.00;
        $data['contact'] = '+44' . $data['contact'];
        $data['seller_id'] = auth()->id();

        unset($data['_token']);
        unset($data['color']);
        unset($data['gallery']);
        unset($data['qty']);
        unset($data['vehicle']);

        Qty::updateQty($productId, $data['seller_id'], $request->safe()->only(['qty'])['qty']);

        $product = Products::findOrFail($productId);
        if (!empty($product)) {

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $fileName = ImageServices::uploadImg(id: $productId, imageFile: $image);
                    ProductImage::add($productId, $fileName);
                }
            }

            foreach ($data as $key => $value) {
                $product->$key = ($key == 'contact') ? '+44' . $value : $value;
            }

            $updated = $product->save();

            if ($updated) {
                flash('Inventory updated successfully')->success();
            }
        }

        return redirect()->back();
    }

    public function inventoryAddBulk()
    {
        return view('shopkeeper.inventory.add_bulk');
    }
    /**
     * It will delete the product image
     * @version 1.0.0
     */
    public function deleteImg($imageId)
    {
        if (ProductImage::deleteById($imageId)) {
            flash('Image deleted successfully')->success();
        }

        return redirect()->back();
    }
    /**
     * Upload's bulk products
     * @author Huzaifa Haleem
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

            //Check for file extension and size
            // $this->checkUploadedFileProperties($extension, $fileSize);

            //Where uploaded file will be stored on the server
            $location = public_path('upload/csv');
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database
            $filepath = $location . "/" . $filename;
            // Reading file
            $file = fopen($filepath, "r");
            // Read through the file and store the contents as an array
            $importData_arr = [];
            $i = 0;
            //Read the contents of the uploaded file
            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
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
            fclose($file); //Close after reading
            $j = 0;
            foreach ($importData_arr as $importData) {
                $product = new Products();
                $product->user_id = $user_id;
                $product->category_id = $importData[0];
                $product->product_name = $importData[1];
                $product->sku = $importData[2];
                $product->price = str_replace(',', '', $importData[4]);
                $product->discount_percentage = ($importData[5] == "") ? 0 : $importData[5];
                $product->weight = $importData[6];
                $product->brand = $importData[7];
                $product->size = ($importData[8] == "null") ? NULL : $importData[8];
                $product->status = $importData[9];
                $product->contact = $importData[10];
                $product->colors = ($importData[11] == "null") ? NULL : $importData[11];
                $product->bike = $importData[12];
                $product->car = $importData[13];
                $product->van = $importData[14];
                $product->feature_img = $importData[18];
                $product->height = $importData[15];
                $product->width = $importData[16];
                $product->length = $importData[17];
                $product->save();

                //this function will add qty to it's parti;cular table
                $product_id = (int) $product->id;
                $product_quantity = ($importData[3] == "") ? 0 : $importData[3];
                Qty::add($user_id, $product_id, $product->category_id, $product_quantity);
                ProductImage::add((int) $product->id, $importData[18]);
                $j++;
            }
        }
        flash('Your Bulk Products Have Been Imported Successfully!');

        return redirect()->back();
    }
}
