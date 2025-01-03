<?php

namespace App\Http\Livewire\Admin;

use App\Qty;
use App\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ChildSellersLivewire extends Component
{
    use WithPagination;

    public
        $child_seller_id,
        $name,
        $email,
        $phone,
        $full_address,
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

    protected $paginationTheme = 'bootstrap';

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'name',
            'email',
            'phone',
            'full_address',
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
        $data = User::getUserByID($id, ['*']);
        $this->name = $data->name;
        $this->email = $data->email;
        $this->phone = $data->phone;
        $this->full_address = $data->full_address;
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
    }

    public function syncParentQty($child_seller_id, $parent_seller_id)
    {
        try {
            /* Perform some operation */
            if (Qty::getTotalProductsCountBySellerId($child_seller_id) > 0) {
                $synced = true;
                $message = config('constants.PARENT_QTY_ALREADY_SYNCED');
            } else {
                $synced = Qty::syncParentSellerQuantities($parent_seller_id, $child_seller_id);
                $message = ($synced) ? config('constants.PARENT_QTY_SYNCED_SUCCESS') : config('constants.PARENT_QTY_SYNCED_FAILED');
            }
            sleep(1);
            /* Operation finished */
            if ($synced) {
                Cache::flush();
                session()->flash('success', $message);
            } else {
                session()->flash('error', $message);
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    public function changeStatus($id, $is_active)
    {
        try {
            /* Perform some operation */
            $status = ($is_active === 1) ? 0 : 1;
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
        $data = User::getChildSellers($this->search);
        return view('livewire.admin.child-sellers-livewire', compact('data'));
    }
}
