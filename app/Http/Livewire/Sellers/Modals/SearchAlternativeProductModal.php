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
        $productDetails,
        $orderId,
        $currentProdId,
        $currentProdQty,
        $customerName,
        $phoneNumber,
        $selectedQty,
        $sellerId,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'selectedQty' => 'required|integer'
    ];

    protected $messages = [
        'selectedQty.required' => 'Please enter the qty',
        'selectedQty.integer' => 'The qty must be a integer value'
    ];

    public function mount($orderId, $currentProdId, $currentProdQty, $customerName, $phoneNumber)
    {
        $this->resetAllPaginators();

        $this->orderId = $orderId;
        $this->currentProdId = $currentProdId;
        $this->currentProdQty = $currentProdQty;
        $this->customerName = $customerName;
        $this->phoneNumber = $phoneNumber;
        $this->sellerId = Auth::id();
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
        $this->productDetails = null;
    }

    public function addProduct($productId)
    {
        try {
            /* Perform some operation */
            $this->productDetails = Products::getProductInfo(
                $this->sellerId,
                $productId,
                ['id', 'category_id', 'product_name', 'sku', 'price', 'feature_img']
            );
            /* Operation finished */
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error);
        }
    }

    public function addProductIntoOrder($alternativeProduct)
    {
        $this->validate();
        try {
            /* Perform some operation */
            if ($alternativeProduct['qty'][0]['qty'] < $this->selectedQty) {
                return session()->flash('qty_should_not_be_greater', config('constants.QTY_SHOULD_NOT_BE_GREATER'));
            } else {
                $currentProduct = Products::getOnlyProductDetailsById($this->currentProdId);
                $currentProdPrice = $currentProduct->price * $this->currentProdQty;
                $alternativeProdPrice = $alternativeProduct['price'] * $this->selectedQty;

                $replaced = Orders::replaceWithAlternativePrice(
                    $this->orderId,
                    $currentProdPrice,
                    $alternativeProdPrice
                );
                
                $updated = OrderItems::replaceWithAlternativeProduct(
                    $this->orderId,
                    $this->currentProdId,
                    $alternativeProduct['id'],
                    $this->selectedQty
                );
            }
            /* Operation finished */
            sleep(1);
            $this->emit('alternativeProductIncluded');
            $this->emit('callParentResetModal');
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
        $products = Products::getProductsForSAPModal($this->sellerId, $this->search);

        return view('livewire.sellers.modals.search-alternative-product-modal', compact('products'));
    }
}
