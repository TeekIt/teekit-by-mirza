<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\Products;
use App\Services\CsvFileServices;
use App\Services\ImageServices;
use App\User;
use Livewire\Component;
use Exception;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class UserGeneralSettings extends Component
{
    use WithFileUploads;

    public
        $user_id,
        $name,
        // $l_name,
        $email,
        $business_name,
        $business_phone,
        $phone,
        $old_password,
        $new_password,
        $user_img,
        $image_to_upload;

    public function mount()
    {
        $this->user_id = auth()->id();
    }

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'name',
            'email',
            'business_name',
            'business_phone',
            'phone',
            'old_password',
            'new_password',
            'user_img',
            'image_to_upload',
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
            $this->resetModal();
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

    /*
    * Please do not remove the following method
    * As we may require this in the future
    */
    // public function exportProducts()
    // {
    //     $products = Products::getParentSellerProductsAsc($this->user_id);
    //     CsvFileServices::exportAsCsv($products, $this->user_id);
    // }

    public function passwordUpdate()
    {
        $this->validate([
            'old_password' => 'required|min:8',
            'new_password' => 'required|min:8',
        ]);
        try {
            /* Perform some operation */
            $user = User::find($this->user_id);
            if (Hash::check($this->old_password, $user->password)) {
                $updated = User::updateInfo($user->id, password: $this->new_password);
                /* Operation finished */
                $this->resetModal();
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

    public function updateName()
    {
        $this->validate([
            'name' => 'required|string|max:80',
        ]);
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->user_id,
                name: $this->name
            );
            /* Operation finished */
            $this->resetModal();
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateBusinessName()
    {
        $this->validate([
            'business_name' => 'required|string|max:80|unique:users,business_name',
        ]);
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->user_id,
                business_name: $this->business_name
            );
            /* Operation finished */
            $this->resetModal();
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateEmail()
    {
        $this->validate([
            'email' => 'required|email|max:80|unique:users',
        ]);
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->user_id,
                email: $this->email
            );
            /* Operation finished */
            $this->resetModal();
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateBusinessPhone()
    {
        $this->validate([
            'business_phone' => 'required|string|min:10|max:10',
        ]);
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->user_id,
                business_phone: $this->business_phone
            );
            /* Operation finished */
            $this->resetModal();
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updatePhone()
    {
        $this->validate([
            'phone' => 'required|string|min:10|max:10',
        ]);
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->user_id,
                phone: $this->phone
            );
            /* Operation finished */
            $this->resetModal();
            sleep(1);
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
        // $this->l_name = $user->l_name;
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
