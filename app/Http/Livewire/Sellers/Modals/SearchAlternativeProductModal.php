<?php

namespace App\Http\Livewire\Sellers\Modals;

use App\Products;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SearchAlternativeProductModal extends Component
{
    use WithPagination;

    public
        $product,
        // $receiver_name, 
        // $phone_number,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    // public function mount(string | bool $receiver_name, string $phone_number)
    // {
    //     $this->resetAllPaginators();
    //     dd($receiver_name);
    // }
    public function mount($receiver_name, $phone_number)
    {
        $this->resetAllPaginators();
        // dd($receiver_name);
        // $this->receiver_name = $receiver_name;
        // $this->phone_number = $phone_number;
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
        $this->product = null;
    }

    public function addProduct($product_id)
    {
        try {
            /* Perform some operation */
            $this->product = Products::getProductInfo($product_id);
            /* Operation finished */
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error);
        }
    }

    public function render($receiver_name = null, $phone_number = null)
    {
        // dd($receiver_name);
        $name = $receiver_name;
        $products = Products::getProductsForSAPModal(Auth::id(), $this->search);
        return view('livewire.sellers.modals.search-alternative-product-modal', compact('products', 'name', 'phone_number'));
    }
}
