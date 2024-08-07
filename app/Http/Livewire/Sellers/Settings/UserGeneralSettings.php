<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\Products;
use App\Services\ImageServices;
use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class UserGeneralSettings extends Component
{
    use WithFileUploads;

    public
        $user_id,
        $name,
        $l_name,
        $email,
        $business_name,
        $business_phone,
        $phone,
        $old_password,
        $new_password,
        $full_address,
        $unit_address,
        $postcode,
        $country,
        $state,
        $city,
        $lat,
        $lon,
        $user_img,
        $image_to_upload,
        $filename;

    protected $rules = [
        'old_password' => 'required|min:8',
        'new_password' => 'required|min:8',
    ];

    public function mount()
    {
        $this->user_id = auth()->id();
    }

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'order_id',
            'current_prod_id',
            'current_prod_qty',
            'receiver_name',
            'phone_number',
            'order_item',
            'nearby_sellers',
            'selected_nearby_seller',
            'search',
            'custom_order_id',
        ]);
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updateImage()
    {
        $this->validate([
            'image_to_upload' => 'required|image|max:800',
        ]);
        try {
            /* Perform some operation */
            $filename = ImageServices::uploadLivewireImg($this->image_to_upload, $this->user_id);
            if ($filename) User::updateInfo($this->user_id, user_img: $filename);
            /* Operation finished */
            sleep(1);
            if ($filename) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

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
                foreach ($pt->images as $img) $temp_img[] = $img->product_image;
            }
            $pt->images = implode(',', $temp_img);
            $all_products[] = $pt;
        }
        $destinationPath = public_path() . "/upload/csv/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file = time() . '_export.csv';
        return  $this->jsonToCsv(json_encode($all_products), $destinationPath . $file, true);
    }

    public function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
        if (empty($json)) {
            die("The JSON string is empty!");
        }

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
                fputcsv($f, array_map('strval', $firstLineKeys));
                $firstLineKeys = array_flip($firstLineKeys);
            }

            // Using array_merge is important to maintain the order of keys according to the first element
            // $line = array_map('strval', $line);
            fputcsv($f, array_merge($firstLineKeys, $line));
        }

        fclose($f);

        return response()->download($csvFilePath, null, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
    }

    public function passwordUpdate()
    {
        $this->validate();
        try {
            /* Perform some operation */
            $user = User::find($this->user_id);
            if (Hash::check($this->old_password, $user->password)) {
                $updated = User::updateInfo($user->id, password: $this->new_password);
                /* Operation finished */
                sleep(1);
                if ($updated) {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                } else {
                    session()->flash('error', config('constants.UPDATION_FAILED'));
                }
            } else {
                session()->flash('error', 'Your old password is incorrect.');
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|string|email|max:80|unique:users',
            'phone' => 'required|string|min:10|max:10',
            'business_name' => 'required|string|max:80|unique:users,business_name',
            'business_phone' => 'required|string|min:10|max:10',
        ]);
        try {
            /* Perform some operation */
            $updated = User::where('id', $this->user_id)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'business_name' => $this->business_name,
                    'business_phone' => $this->business_phone,
                ]);

            // User::updateInfo(
            //     $this->user_id,
            //     name:
            // );

            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'editUserModal']);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function setUserInfo()
    {
        $user = User::find($this->user_id);
        $this->name = $user->name;
        $this->l_name = $user->l_name;
        $this->email = $user->email;
        $this->business_name = $user->business_name;
        $this->business_phone = $user->business_phone;
        $this->phone = $user->phone;
        $this->user_img = $user->user_img;
        return $user;
    }

    public function render()
    {
        $user = $this->setUserInfo($this->user_id);
        return view('livewire.sellers.settings.user-general-settings', compact('user'));
    }
}
