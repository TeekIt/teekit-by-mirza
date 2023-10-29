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
        $current_prod_qty,
        $receiver_name,
        $phone_number,
        $selected_qty,
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
    public function mount($order_id, $current_prod_id, $current_prod_qty, $receiver_name, $phone_number)
    {
        $this->resetAllPaginators();
        $this->order_id = $order_id;
        $this->current_prod_id = $current_prod_id;
        $this->current_prod_qty = $current_prod_qty;
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

    public function removeAlternativeProduct()
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

    public function addProductIntoOrder($alternative_product)
    {
        $this->validate();
        try {
            /* Perform some operation */
            if ($alternative_product['quantity']['qty'] < $this->selected_qty) {
                return session()->flash('qty_should_not_be_greater', config('constants.QTY_SHOULD_NOT_BE_GREATER'));
            } else {
                $current_product = Products::getOnlyProductDetailsById($this->current_prod_id);
                $current_prod_price = $current_product->price * $this->current_prod_qty;
                $alternative_prod_price = $alternative_product['price'] * $this->selected_qty;

                $replaced = Orders::replaceWithAlternativePrice($this->order_id, $current_prod_price, $alternative_prod_price);
                $updated = OrderItems::replaceWithAlternativeProduct($this->order_id, $this->current_prod_id, $alternative_product['id'], $this->selected_qty);
            }
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'searchAlternativeProductModal']);
            if ($replaced && $updated) {
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
