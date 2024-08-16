<?php

namespace App\Http\Livewire\Admin;

use App\Categories;
use App\Models\CommissionAndServiceFee;
use App\User;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class ParentSellersLiveWire extends Component
{
    use WithPagination;

    public
        $seller_id,
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
        $enable_fixed_commission,
        $enable_different_commissions,
        $enable_apply_commission_btn,
        $categories,
        $fixed_commission,
        $different_commissions = [],
        $category_id_map,
        $modal_success = false,
        $modal_success_msg,
        $modal_error = false,
        $modal_error_msg,
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

    public function enableThis($input_to_enable)
    {
        $this->resetAllErrors();

        $this->{$input_to_enable} = true;

        $commission_array = ['enable_fixed_commission', 'enable_different_commissions'];
        if (in_array($input_to_enable, $commission_array)) {
            $this->enable_apply_commission_btn = true;
            if ($input_to_enable === $commission_array[0]) $this->enable_different_commissions = false;
            if ($input_to_enable === $commission_array[1]) $this->enable_fixed_commission = false;
        }
    }

    public function renderInfoModal($id)
    {
        $data = User::find($id);

        $this->categories = Categories::getAllCategoriesByStoreId($id);
        $this->categories = ($this->categories->isEmpty()) ? [] : $this->categories;
        $this->category_id_map = is_array($this->categories) ? [] : $this->categories->pluck('category_id')->toArray();

        if ($data) {
            $this->seller_id = $data->id;
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

    public function applyCommission()
    {
        if ($this->enable_fixed_commission) $this->validate([
            'fixed_commission' => 'required|int',
        ]);

        if ($this->enable_different_commissions) $this->validate([
            'different_commissions' => 'required|array',
        ]);
        try {
            /* Perform some operation */
            if ($this->enable_fixed_commission) {

                // if ($is_fixed) {
                //     $commissionData = ['fixed_commission' => $commission['fixed_commission']];
                // } else {
                //     $commissionData = ['different_commissions' => $commissionArray];
                // }

                $inserted = CommissionAndServiceFee::updateOrAdd($this->seller_id, ['fixed_commission' => (int)$this->fixed_commission]);
            };

            if ($this->enable_different_commissions) {
                // dd($this->category_id_map);

                // foreach ($this->different_commissions as $index => $commission) {
                //     $categoryId = $this->categoryIdMap[$index] ?? null;
                //     if ($categoryId) {
                //         // Save commission for this category ID
                //     }
                // }

            }
            /* Operation finished */
            if ($inserted) {
                $this->modal_success = true;
                $this->modal_success_msg = config('constants.DATA_UPDATED_SUCCESS');
            } else {
                $this->modal_error = true;
                $this->modal_error_msg = config('constants.UPDATION_FAILED');
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('messages.INVALID_DATA'));
        }
    }

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
