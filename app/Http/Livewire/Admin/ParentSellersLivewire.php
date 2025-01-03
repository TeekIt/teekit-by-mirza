<?php

namespace App\Http\Livewire\Admin;

use App\Categories;
use App\Models\CommissionAndServiceFee;
use App\User;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class ParentSellersLivewire extends Component
{
    use WithPagination;

    public
        $seller_id,
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
        $enable_fixed_commission,
        $enable_different_commissions,
        $fixed_commission,
        $different_commissions = [],
        $enable_apply_commission_btn,
        $enable_fixed_service_fees,
        $enable_different_service_fees,
        $fixed_service_fees,
        $different_service_fees = [],
        $enable_apply_service_fees_btn,
        $categories,
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
            'enable_fixed_commission',
            'enable_different_commissions',
            'enable_apply_commission_btn',
            'enable_fixed_service_fees',
            'enable_different_service_fees',
            'enable_apply_service_fees_btn',
            'categories',
            'fixed_commission',
            'different_commissions',
            'fixed_service_fees',
            'different_service_fees',
            'category_id_map',
            'modal_success',
            'modal_success_msg',
            'modal_error',
            'modal_error_msg',
            'search',
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

            if ($input_to_enable === $commission_array[0])
                $this->enable_different_commissions = false;

            if ($input_to_enable === $commission_array[1])
                $this->enable_fixed_commission = false;
        }

        $service_fees_array = ['enable_fixed_service_fees', 'enable_different_service_fees'];
        if (in_array($input_to_enable, $service_fees_array)) {
            $this->enable_apply_service_fees_btn = true;

            if ($input_to_enable === $service_fees_array[0])
                $this->enable_different_service_fees = false;

            if ($input_to_enable === $service_fees_array[1])
                $this->enable_fixed_service_fees = false;
        }
    }

    public function renderCommissions($commission)
    {
        $commission = json_decode($commission);

        if (isset($commission->fixed_commission)) {

            $this->enableThis('enable_fixed_commission');
            $this->fixed_commission = $commission->fixed_commission;
        } else if (isset($commission->different_commissions)) {

            $this->enableThis('enable_different_commissions');
            /* Convert different_commissions to an array of associative arrays */
            $different_commissions = collect($commission->different_commissions)->map(fn($item) => (array) $item);

            /* Align commissions according to categories */
            $this->different_commissions = collect($this->categories)
                ->mapWithKeys(function ($category, $index) use ($different_commissions) {

                    $matching_commission = $different_commissions->firstWhere('category_id', $category->id);

                    return [$index => $matching_commission['commission'] ?? 0];
                })->toArray();
        }
    }

    public function renderServiceFees($service_fee)
    {
        $service_fee = json_decode($service_fee);

        if (isset($service_fee->fixed_service_fees)) {

            $this->enableThis('enable_fixed_service_fees');
            $this->fixed_service_fees = $service_fee->fixed_service_fees;
        } else if (isset($service_fee->different_service_fees)) {

            $this->enableThis('enable_different_service_fees');
            /* Convert different_service_fees to an array of associative arrays */
            $different_service_fees = collect($service_fee->different_service_fees)->map(fn($item) => (array) $item);

            /* Align service fees according to categories */
            $this->different_service_fees = collect($this->categories)
                ->mapWithKeys(function ($category, $index) use ($different_service_fees) {

                    $matching_service_fees = $different_service_fees->firstWhere('category_id', $category->id);

                    return [$index => $matching_service_fees['service_fees'] ?? 0];
                })->toArray();
        }
    }

    public function renderInfoModal($id)
    {
        $data = User::find($id);

        $this->categories = Categories::getAllCategoriesByStoreId($id, ['*']);
        $this->categories = ($this->categories->isEmpty()) ? [] : $this->categories;
        $this->category_id_map = is_array($this->categories) ? [] : $this->categories->pluck('id')->toArray();

        if ($data) {
            $this->seller_id = $data->id;
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
            $seller_commission_and_service_fee = $data->commissionAndServiceFee;

            if ($seller_commission_and_service_fee?->commission) {
                $this->renderCommissions($seller_commission_and_service_fee->commission);
            }

            if ($seller_commission_and_service_fee?->service_fee) {
                $this->renderServiceFees($seller_commission_and_service_fee->service_fee);
            }
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
        if ($this->enable_fixed_commission)
            $this->validate([
                'fixed_commission' => 'required|int',
            ]);

        if ($this->enable_different_commissions)
            $this->validate([
                'different_commissions' => 'required|array',
            ]);
        try {
            /* Perform some operation */
            if ($this->enable_fixed_commission)
                $inserted = CommissionAndServiceFee::updateOrAdd(
                    $this->seller_id,
                    commission: ['fixed_commission' => (int) $this->fixed_commission]
                );

            if ($this->enable_different_commissions) {
                foreach ($this->different_commissions as $index => $commission) {
                    /*
                     * Using $category_id_map array to retreive the category ids
                     * Which were saved during the initial rendering of the "infoModal"
                     */
                    $category_id = $this->category_id_map[$index] ?? null;
                    if ($category_id) {
                        $different_commissions_array[] = ['category_id' => $category_id, 'commission' => (int) $commission];
                    }
                }
                $inserted = CommissionAndServiceFee::updateOrAdd(
                    $this->seller_id,
                    commission: ['different_commissions' => $different_commissions_array]
                );
            }
            /* Operation finished */
            // if ($inserted) {
            //     $this->modal_success = true;
            //     $this->modal_success_msg = config('constants.DATA_UPDATED_SUCCESS');
            // } else {
            //     $this->modal_error = true;
            //     $this->modal_error_msg = config('constants.UPDATION_FAILED');
            // }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('messages.INVALID_DATA'));
        }
    }

    public function applyServiceFees()
    {
        if ($this->enable_fixed_service_fees)
            $this->validate([
                'fixed_service_fees' => 'required|int',
            ]);

        if ($this->enable_different_service_fees)
            $this->validate([
                'different_service_fees' => 'required|array',
            ]);
        try {
            /* Perform some operation */
            if ($this->enable_fixed_service_fees)
                $inserted = CommissionAndServiceFee::updateOrAdd(
                    $this->seller_id,
                    service_fee: ['fixed_service_fees' => (int) $this->fixed_service_fees]
                );

            if ($this->enable_different_service_fees) {
                foreach ($this->different_service_fees as $index => $service_fees) {
                    /*
                     * Using $category_id_map array to retreive the category ids
                     * Which were saved during the initial rendering of the "infoModal"
                     */
                    $category_id = $this->category_id_map[$index] ?? null;
                    if ($category_id) {
                        $different_service_fees_array[] = ['category_id' => $category_id, 'service_fees' => (int) $service_fees];
                    }
                }
                $inserted = CommissionAndServiceFee::updateOrAdd(
                    $this->seller_id,
                    service_fee: ['different_service_fees' => $different_service_fees_array]
                );
            }
            /* Operation finished */
            // if ($inserted) {
            //     $this->modal_success = true;
            //     $this->modal_success_msg = config('constants.DATA_UPDATED_SUCCESS');
            // } else {
            //     $this->modal_error = true;
            //     $this->modal_error_msg = config('constants.UPDATION_FAILED');
            // }
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
        return view('livewire.admin.parent-sellers-livewire', compact('data'));
    }
}
