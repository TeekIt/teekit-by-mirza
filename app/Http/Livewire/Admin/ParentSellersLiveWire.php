<?php

namespace App\Http\Livewire\Admin;

use App\User;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class ParentSellersLiveWire extends Component
{
    use WithPagination;

    public
        $parent_seller_id,
        $name,
        $email,
        $phone,
        $address_1,
        $business_name,
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

    private const
        ACTIVE = 1,
        BLOCK = 0;

    protected $paginationTheme = 'bootstrap';

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'name',
            'email',
            'phone',
            'address_1',
            'business_name',
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
        $data = User::find($id);
        if ($data) {
            $this->name = $data->name;
            $this->email = $data->email;
            $this->phone = $data->phone;
            $this->address_1 = $data->address_1;
            $this->business_name = $data->business_name;
            $this->lat = $data->lat;
            $this->lon = $data->lon;
            $this->user_img = $data->user_img;
            $this->last_login = $data->last_login;
            $this->email_verified_at = $data->email_verified_at;
            $this->pending_withdraw = $data->pending_withdraw;
            $this->total_withdraw = $data->total_withdraw;
            $this->is_online = $data->is_online;
            $this->application_fee = $data->application_fee;
        } else {
            return redirect()->to(route('admin.sellers.parent'))->with('error', 'Record Not Found.');
        }
    }

    // public function renderDeleteModal($id)
    // {
    //     $this->parent_seller_id = $id;
    // }

    // public function destroy()
    // {
    //     try {
    //         /* Perform some operation */
    //         $deleted = User::delEmployee($this->parent_seller_id);
    //         /* Operation finished */
    //         sleep(1);
    //         $this->dispatchBrowserEvent('close-modal', ['id' => 'deleteModal']);
    //         if ($deleted) {
    //             session()->flash('success', config('messages.DELETION_SUCCESS'));
    //         } else {
    //             session()->flash('error', config('messages.DELETION_FAILED'));
    //         }
    //     } catch (Exception $error) {
    //         report($error);
    //         session()->flash('error', config('messages.INVALID_DATA'));
    //     }
    // }
    /**
     * The sole purpose of this function is to resolve the double-click problem
     * Which occurs while using wire:model.lazy directive
     * Now this function will be called only when a button is clicked
     * And after that it will remove the focus from the forms input fields & calls
     * The given form action manually
     * @author Muhammad Abdullah Mirza
     */
    // public function submitForm($form_name)
    // {
    //     $this->$form_name();
    // }

    public function applyCommision() {}

    public function changeStatus($id, $is_active)
    {
        try {
            /* Perform some operation */
            $status = ($is_active) ? self::BLOCK : self::ACTIVE;
            $status_cahnged = User::activeOrBlockStore($id, $status);
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
        $data = User::getParentSellers($this->search);
        return view('livewire.admin.parent-sellers-live-wire', compact('data'));
    }
}
