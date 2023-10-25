<?php

namespace App\Http\Livewire\Sellers\Modals;

use App\OrderItems;
use App\Orders;
use App\Products;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/* Search Alternative Product shortly known as "SAP" */

class SearchAlternativeProductModal extends Component
{
    use WithPagination;

    public
        $product_details,
        $order_id,
        $current_prod_id,
        $receiver_name,
        $phone_number,
        $selected_qty,
        $qty_error = false,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'selected_qty' => 'required|integer'
    ];

    protected $messages = [
        'selected_qty.required' => 'Please enter the qty',
        'selected_qty.integer' => 'The qty must be a integer value'
    ];

    // public function mount(string | bool $receiver_name, string $phone_number)
    // {
    //     $this->resetAllPaginators();
    //     dd($receiver_name);
    // }
    public function mount($order_id, $current_prod_id, $receiver_name, $phone_number)
    {
        $this->resetAllPaginators();
        $this->order_id = $order_id;
        $this->current_prod_id = $current_prod_id;
        $this->receiver_name = $receiver_name;
        $this->phone_number = $phone_number;
    }

    public function resetChildModal()
    {
        $this->resetAllErrors();
        dd('called');
        // $this->reset([
        //     'name',
        //     'l_name',
        //     'email',
        //     'phone',
        //     'address_1',
        //     'lat',
        //     'lon',
        //     'user_img',
        //     'last_login',
        //     'email_verified_at',
        //     'pending_withdraw',
        //     'total_withdraw',
        //     'is_online',
        //     'application_fee',
        // ]);
    }

    public function resetAllPaginators()
    {
        $this->resetPage('sap_products_page');
    }

    public function updatingSearch()
    {
        $this->resetAllPaginators();
    }

    public function removeProduct()
    {
        $this->product_details = null;
    }

    public function addProduct($product_id)
    {
        try {
            /* Perform some operation */
            $this->product_details = Products::getProductInfo($product_id);
            /* Operation finished */
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error);
        }
    }

    public function addProductIntoOrder($product_details)
    {
        $this->validate();
        try {
            /* Perform some operation */
            if ($product_details['quantity']['qty'] < $this->selected_qty) {
                session()->flash('qty_is_greater_error', config('constants.QTY_SHOULD_NOT_BE_GREATER'));
            } else {
                $alternative_prod_price = $product_details['price'] * $this->selected_qty;
                $current_prod_details = Products::getOnlyProductDetailsById($this->current_prod_id);
                Orders::replaceWithAlternativePrice($this->order_id, $current_prod_details->price, $alternative_prod_price);
                $updated = OrderItems::replaceWithAlternativeProduct($this->order_id, $this->current_prod_id, $product_details['id'], $this->selected_qty);
            }
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'searchAlternativeProductModal']);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error);
        }
    }

    public function render()
    {
        $products = Products::getProductsForSAPModal(Auth::id(), $this->search);
        return view('livewire.sellers.modals.search-alternative-product-modal', compact('products'));
    }
}
