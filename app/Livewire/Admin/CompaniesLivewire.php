<?php

namespace App\Livewire\Admin;

use App\Enums\OrderByEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CompaniesLivewire extends Component
{
    use WithPagination;

    public ?int $companyId;

    public ?string $name;

    public ?string $email;

    public ?string $phone;

    public ?string $fullAddress;

    public ?string $businessName;

    public ?float $lat;

    public ?float $lon;

    public ?string $userImg;

    public ?string $lastLogin;

    public ?string $emailVerifiedAt;

    public ?bool $isOnline;

    public ?string $search = '';

    private const int ACTIVE = 1;

    private const int BLOCK = 0;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /*
     * Custom Helpers
     */
    public function resetComponent()
    {
        $this->resetValidation();

        $this->reset([
            'name',
            'email',
            'phone',
            'fullAddress',
            'businessName',
            'lat',
            'lon',
            'userImg',
            'lastLogin',
            'emailVerifiedAt',
            'isOnline',
            'search',
        ]);
    }

    public function renderInfoModal(int $id)
    {
        $data = User::find($id);

        if ($data) {
            $this->companyId = $data->id;
            $this->name = $data->name;
            $this->email = $data->email;
            $this->phone = $data->phone;
            $this->fullAddress = $data->full_address;
            $this->businessName = $data->business_name;
            $this->lat = $data->lat;
            $this->lon = $data->lon;
            $this->userImg = $data->user_img;
            $this->lastLogin = $data->last_login;
            $this->emailVerifiedAt = $data->email_verified_at;
            $this->isOnline = $data->is_online;
        } else {
            return redirect()->to(route('admin.companies'))->with('error', 'Record Not Found.');
        }
    }

    public function changeStatus(int $id, int $isActive)
    {
        try {
            /* Perform some operation */
            $status = ($isActive) ? self::BLOCK : self::ACTIVE;
            $statusChanged = User::activateOrBlock($id, $status);
            /* Operation finished */
            if ($statusChanged) {
                $this->resetPage();
            } else {
                session()->flash('error', config('constants.STATUS_CHANGING_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INVALID_DATA'));
        }
    }

    public function render(): View
    {
        $data = User::getByRole(
            UserRoleEnum::COMPANY,
            OrderByEnum::DESC,
            $this->search
        );

        return view('livewire.admin.companies-livewire', compact('data'));
    }
}
