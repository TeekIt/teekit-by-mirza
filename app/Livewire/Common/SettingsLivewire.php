<?php

namespace App\Livewire\Common;

use Illuminate\Contracts\View\View;
use App\Services\ImageServices;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsLivewire extends Component
{
    use WithFileUploads;

    public ?int $userId = null;

    public ?string $name = null;

    public ?string $lName = null;

    public ?string $email = null;

    public ?string $businessName = null;

    public ?string $countryCode = null;

    public ?string $businessPhone = null;

    public ?string $phone = null;

    public ?string $oldPassword = null;

    public ?string $newPassword = null;

    public ?string $userImg = null;

    public mixed $imageToUpload = null;

    public ?string $fullAddress = null;

    public ?string $unitAddress = null;

    public ?string $postcode = null;

    public ?string $country = null;

    public ?string $state = null;

    public ?string $city = null;

    public ?float $lat = null;

    public ?float $lon = null;

    /*
     * Lifecycle Hooks
     */
    public function mount(): void
    {
        $this->userId = User::getAuthUser()->id;
    }

    /*
    * Helpers
    */
    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'name',
            'email',
            'businessName',
            'businessPhone',
            'countryCode',
            'phone',
            'oldPassword',
            'newPassword',
            'userImg',
            'imageToUpload',
            'fullAddress',
            'unitAddress',
            'postcode',
            'country',
            'state',
            'city',
            'lat',
            'lon',
        ]);
    }

    public function setUserInfo(): User
    {
        $user = User::find($this->userId);
        $this->name = $this->name ?? $user->name;
        // $this->l_name = $user->l_name;
        $this->email = $this->email ?? $user->email;
        $this->businessName = $this->businessName ?? $user->business_name;
        $this->businessPhone = $this->businessPhone ?? $user->business_phone;
        $this->countryCode = $this->countryCode ?? $user->country_code;
        $this->phone = $this->phone ?? $user->phone;
        $this->userImg = $this->userImg ?? $user->user_img;
        $this->fullAddress = $this->fullAddress ?? $user->full_address;
        $this->unitAddress = $this->unitAddress ?? $user->unit_address;
        $this->postcode = $this->postcode ?? $user->postcode;
        $this->country = $this->country ?? $user->country;
        $this->state = $this->state ?? $user->state;
        $this->city = $this->city ?? $user->city;
        $this->lat = $this->lat ?? $user->lat;
        $this->lon = $this->lon ?? $user->lon;

        return $user;
    }

    /*
    * CRUD Methods
    */
    public function updateImage(): void
    {
        $this->validate([
            'imageToUpload' => 'required|image|max:1024',
        ]);

        try {
            /* Perform some operation */
            if (!$this->imageToUpload) {
                session()->flash('error', 'No image file was uploaded.');
                return;
            }

            $filename = ImageServices::uploadLivewireImg($this->imageToUpload, $this->userId);
            if ($filename) {
                User::updateInfo($this->userId, userImg: $filename);
            }
            /* Operation finished */
            $this->resetComponent();
            sleep(1);

            if ($filename) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    /*
    * Please do not remove the following method
    * As we may require this in the future
    */
    // public function exportProducts()
    // {
    //     $products = Products::getParentSellerProductsAsc($this->userId);
    //     CsvFileServices::exportAsCsv($products, $this->userId);
    // }

    public function passwordUpdate(): void
    {
        $this->validate([
            'oldPassword' => 'required|min:8',
            'newPassword' => 'required|min:8',
        ]);

        try {
            /* Perform some operation */
            $user = User::find($this->userId);
            if (Hash::check($this->oldPassword, $user->password)) {
                $updated = User::updateInfo($user->id, password: $this->newPassword);
                /* Operation finished */
                $this->resetComponent();
                sleep(1);
                if ($updated) {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                } else {
                    session()->flash('error', config('constants.UPDATION_FAILED'));
                }
            } else {
                session()->flash('error', 'Your old password is incorrect');
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateName(): void
    {
        $this->validate([
            'name' => 'required|string|max:80',
        ]);
        
        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->userId,
                name: $this->name
            );
            /* Operation finished */
            $this->resetComponent();
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

    public function updateBusinessName(): void
    {
        $this->validate([
            'businessName' => 'required|string|max:80|unique:users,business_name',
        ]);

        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->userId,
                businessName: $this->businessName
            );
            /* Operation finished */
            $this->resetComponent();
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

    public function updateEmail(): void
    {
        $this->validate([
            'email' => 'required|email|max:80|unique:users',
        ]);

        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->userId,
                email: $this->email
            );
            /* Operation finished */
            $this->resetComponent();
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

    public function updateBusinessPhone(): void
    {
        $this->validate([
            'businessPhone' => 'required|string|min:10|max:10',
        ]);

        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->userId,
                businessPhone: $this->businessPhone
            );
            /* Operation finished */
            $this->resetComponent();
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

    public function updatePhone(): void
    {
        $this->validate([
            'countryCode' => 'required|string',
            'phone' => 'required|string|min:10|max:10',
        ]);

        try {
            /* Perform some operation */
            $updated = User::updateInfo(
                $this->userId,
                countryCode: $this->countryCode,
                phone: $this->phone
            );
            /* Operation finished */
            $this->resetComponent();
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    public function updateLivewireProperties(array $data = []): void
    {
        /* Dispatch browser event to update DOM inputs inside wire:ignore.self modal */
        $this->dispatch('location-updated', [
            'fullAddress' => $data['pickupAddress'] ?? null,
            'postcode' => $data['pickupPostcode'] ?? null,
            'country' => $data['pickupCountry'] ?? null,
            'state' => $data['pickupState'] ?? null,
            'city' => $data['pickupCity'] ?? null,
            'lat' => $data['pickupLat'] ?? null,
            'lon' => $data['pickupLon'] ?? null,
        ]);
    }

    public function updateLocation(): void
    {
        $this->validate([
            'fullAddress' => 'required|string',
            'unitAddress' => 'nullable|string',
            'postcode' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        try {
            /* Perform some operation */
           User::updateLocation(
                $this->userId,
                $this->fullAddress,
                $this->unitAddress,
                $this->country,
                $this->state,
                $this->city,
                $this->postcode,
                $this->lat,
                $this->lon
            );
            /* Operation finished */
            sleep(1);
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'googleMapModal']);

            session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    public function render(): View
    {
        $user = $this->setUserInfo();

        return view('livewire.common.settings-livewire', compact('user'));
    }
}
