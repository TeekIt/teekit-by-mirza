<?php

namespace App\Http\Livewire\Admin;

use App\Drivers;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class DriversLivewire extends Component
{
    use WithPagination;
    public
        $name,
        $l_name,
        $email,
        $phone,
        $address_1,
        $lat,
        $lon,
        $user_img,
        $last_login,
        $email_verified_at,
        $pending_withdraw,
        $total_withdraw,
        $is_online,
        $application_fee,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'name',
            'l_name',
            'email',
            'phone',
            'address_1',
            'lat',
            'lon',
            'user_img',
            'last_login',
            'email_verified_at',
            'pending_withdraw',
            'total_withdraw',
            'is_online',
            'application_fee',
        ]);
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function renderInfoModal($id)
    {
        $data = Drivers::getUserByID($id);
        $this->name = $data->name;
        $this->l_name = $data->l_name;
        $this->email = $data->email;
        $this->phone = $data->phone;
        $this->address_1 = $data->address_1;
        $this->lat = $data->lat;
        $this->lon = $data->lon;
        $this->user_img = $data->user_img;
        $this->last_login = $data->last_login;
        $this->email_verified_at = $data->email_verified_at;
        $this->pending_withdraw = $data->pending_withdraw;
        $this->total_withdraw = $data->total_withdraw;
        $this->is_online = $data->is_online;
        $this->application_fee = $data->application_fee;
    }

    public function changeStatus($id, $is_active)
    {
        try {
            /* Perform some operation */
            $status = ($is_active === 1) ? 0 : 1;
            $status_cahnged = Drivers::activeOrBlockCustomer($id, $status);
            /* Operation finished */
            if ($status_cahnged) {
                $this->resetPage();
            } else {
                session()->flash('error', config('messages.STATUS_CHANGING_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('messages.INVALID_DATA'));
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $data = Drivers::getDrivers($this->search);
        return view('livewire.admin.drivers-livewire', compact('data'));
    }
}
