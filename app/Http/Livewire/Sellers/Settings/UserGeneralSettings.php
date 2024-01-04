<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\Products;
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
        $Name,
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
        $image,
        $Image,
        $search = '',
        $filename;

    protected $rules = [
        'old_password' => 'required|min:8',
        'new_password' => 'required|min:8',
        'Image' => 'required'
    ];

    public function mount()
    {
        $this->user_id = auth()->id();
    }

    public function updateImage()
    {
        try {
            $User = auth()->user();
            $filename = null;
            if ($this->Image) {

                $filename = uniqid($User->id . '_' . $User->name . '_') . '.' . $this->Image->getClientOriginalExtension();

                Storage::disk('spaces')->put($filename, $this->Image->get());

                if (Storage::disk('spaces')->exists($filename)) {
                    info("File is stored successfully: " . $filename);
                } else {
                    info("File is not found: " . $filename);
                }
            }

            $User->user_img = $filename;
            $User->save();
            sleep(1);
            session()->flash('success', 'Image updated successfully.');
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
        try {
            $old_password = $this->old_password;
            $new_password = $this->new_password;
            $user = User::find($this->user_id);
            if (Hash::check($old_password, $user->password)) {
                $user->password = Hash::make($new_password);
                $updated = $user->save();
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
        try {
            /* Perform some operation */
            $updated = User::where('id', $this->user_id)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'business_name' => $this->business_name,
                    'phone' => $this->phone,
                    'l_name' => $this->l_name
                ]);
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
        return $user;
    }

    public function render()
    {
        $user = $this->setUserInfo($this->user_id);
        return view('livewire.sellers.settings.user-general-settings', compact('user'));
    }
}
