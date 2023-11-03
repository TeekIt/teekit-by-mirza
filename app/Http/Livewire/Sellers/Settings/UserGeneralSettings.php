<?php

namespace App\Http\Livewire\Sellers\Settings;

use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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
        $image,
        $search = '';

    protected $rules = [
        'old_password' => 'required|min:8',
        'new_password' => 'required|min:8'
    ];

    public function mount()
    {
        $this->user_id = auth()->id();
    }

    public function updateImage()
    {
        try {
            $user = User::find(auth()->id());
            $filename = auth()->user()->name;
            if ($this->image) {
                // Generate a unique file name
                $filename = uniqid($user->id . '_' . $user->name . '_') . '.' . $this->image->getClientOriginalExtension();
                // Store the file in the designated storage disk
                Storage::disk('spaces')->put($filename, $this->image->get());
                // Check if the file was successfully stored
                if (Storage::disk('spaces')->exists($filename)) {
                    info("File is stored successfully: " . $filename);
                } else {
                    info("File is not found: " . $filename);
                }
            }
            // Update the user's user_img attribute
            $user->user_img = $filename;
            $user->save();
            sleep(1);
            // Show a success message
            session()->flash('success', 'Image updated successfully.');
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
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
        $User = User::find($this->user_id);
        $this->image = $User->user_img;
        $user = $this->setUserInfo();
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('livewire.sellers.settings.user-general-settings', compact('business_hours', 'address', 'business_location', 'user'));
    }
}
