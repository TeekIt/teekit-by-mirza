<?php

namespace App\Http\Controllers\Web\v2;

use App\Enums\TransportVehicleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AddOrUpdateProductRequest;
use App\Models\Categories;
use App\Models\ProductImage;
use App\Models\Products;
use App\Models\Qty;
use App\Services\ImageServices;
use App\Services\ProductServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * It will redirect us to add
     * inventory page
     *
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
        $validatedData = $request->validated();

        if (request()->has('colors')) {
            $validatedData['colors'] = ProductServices::jsonEncodeColors($validatedData['colors']);
        }

        $validatedData['bike'] = ($validatedData['vehicle'] == TransportVehicleEnum::BIKE->value) ? 1 : 0;
        $validatedData['car'] = ($validatedData['vehicle'] == TransportVehicleEnum::CAR->value) ? 1 : 0;
        $validatedData['van'] = ($validatedData['vehicle'] == TransportVehicleEnum::VAN->value) ? 1 : 0;
        $validatedData['discount_percentage'] = (! isset($validatedData['discount_percentage'])) ? 0.00 : $validatedData['discount_percentage'];
        $validatedData['contact'] = '+44'.$validatedData['contact'];
        $validatedData['seller_id'] = auth()->id();
        $validatedData['feature_img'] = ImageServices::uploadImg(request: $request, imgKeyName: 'feature_img', id: $validatedData['seller_id']);

        unset($validatedData['_token']);
        unset($validatedData['color']);
        unset($validatedData['gallery']);
        unset($validatedData['vehicle']);

        $product = Products::add($validatedData);

        Qty::add($validatedData['seller_id'], $product->id, $validatedData['category_id'], $validatedData['qty']);

        if (request()->hasFile('gallery')) {
            foreach (request()->file('gallery') as $singleImage) {
                $uniqueId = $validatedData['seller_id'].$product->id;
                $fileName = ImageServices::uploadImg(id: $uniqueId, imageFile: $singleImage);
                ProductImage::add($product->id, $fileName);
            }
        }

        return redirect()->route('seller.inventory');
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public function editSingleInventoryForm($productId)
    {
        $categories = Categories::all();

        $inventory = Products::getProductInfoEvenDisabled(auth()->id(), $productId);

        return view('shopkeeper.inventory.edit', compact('inventory', 'categories'));
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public function updateInventory(AddOrUpdateProductRequest $request, $productId)
    {
        $validatedData = $request->validated();

        $validatedData['colors'] = (request()->has('colors')) ? ProductServices::jsonEncodeColors($validatedData['colors']) : null;

        if (request()->hasFile('feature_img')) {
            $validatedData['feature_img'] = ImageServices::uploadImg($request, 'feature_img', $validatedData['seller_id']);
        }

        $validatedData['bike'] = ($validatedData['vehicle'] == TransportVehicleEnum::BIKE->value) ? 1 : 0;
        $validatedData['car'] = ($validatedData['vehicle'] == TransportVehicleEnum::CAR->value) ? 1 : 0;
        $validatedData['van'] = ($validatedData['vehicle'] == TransportVehicleEnum::VAN->value) ? 1 : 0;
        $validatedData['discount_percentage'] = $validatedData['discount_percentage'] ?? 0.00;
        $validatedData['contact'] = '+44'.$validatedData['contact'];
        $validatedData['seller_id'] = auth()->id();

        unset($validatedData['_token']);
        unset($validatedData['color']);
        unset($validatedData['gallery']);
        unset($validatedData['qty']);
        unset($validatedData['vehicle']);

        Qty::updateQty($productId, $validatedData['seller_id'], $request->safe()->only(['qty'])['qty']);

        $product = Products::findOrFail($productId);
        if (! empty($product)) {

            if (request()->hasFile('gallery')) {
                foreach (request()->file('gallery') as $image) {
                    $fileName = ImageServices::uploadImg(id: $productId, imageFile: $image);
                    ProductImage::add($productId, $fileName);
                }
            }

            foreach ($validatedData as $key => $value) {
                $product->$key = ($key == 'contact') ? '+44'.$value : $value;
            }

            $updated = $product->save();

            if ($updated) {
                session()->flash('success', 'Inventory updated successfully');
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
     *
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
            $filepath = $location.'/'.$filename;
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
