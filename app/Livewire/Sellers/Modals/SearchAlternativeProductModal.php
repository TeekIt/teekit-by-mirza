<?php

namespace App\Livewire\Sellers\Modals;

use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

/* Search Alternative Product shortly known as "SAP" */

class SearchAlternativeProductModal extends Component
{
    use WithPagination;

    public $productDetails;

    public $orderId;

    public $currentProdId;

    public $currentProdQty;

    public $customerName;

    public $phoneNumber;

    public $alternativeProdUserGivenQty;

    public $sellerId;

    public $search = '';

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'alternativeProdUserGivenQty' => 'required|integer',
    ];

    protected $messages = [
        'alternativeProdUserGivenQty.required' => 'Please enter the qty',
        'alternativeProdUserGivenQty.integer' => 'The qty must be a integer value',
    ];

    /*
     * Lifecycle Hooks
     */
    public function mount($orderId, $currentProdId, $currentProdQty, $customerName, $phoneNumber)
    {
        $this->resetAllPaginators();

        $this->orderId = $orderId;
        $this->currentProdId = $currentProdId;
        $this->currentProdQty = $currentProdQty;
        $this->customerName = $customerName;
        $this->phoneNumber = $phoneNumber;
        $this->sellerId = User::getAuthUser()->id;
    }

    /*
     * Helpers
     */
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

    /*
     * CRUD Methods
     */
    public function addProduct($productId)
    {
        try {
            /* Perform some operation */
            $this->productDetails = Products::getProductInfoWithRelations(
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

    public function addProductIntoOrder($id)
    {
        $this->validate();

        try {
            /* Perform some operation */
            $alternativeProd = Products::getProductInfoWithRelations(
                $this->sellerId,
                $id,
                ['id', 'category_id']
            )->toArray();
            
            if ($alternativeProd['qty'][0]['qty'] < $this->alternativeProdUserGivenQty) {
                return session()->flash('qty_should_not_be_greater', config('constants.QTY_SHOULD_NOT_BE_GREATER'));
            }

            $currentProdTotalPrice = Products::getProductPrice($this->currentProdId) * $this->currentProdQty;
            $alternativeProdTotalPrice = Products::getProductPrice($alternativeProd['id']) * $this->alternativeProdUserGivenQty;

            $replacedPrice = Orders::replaceWithAlternativePrice(
                $this->orderId,
                $currentProdTotalPrice,
                $alternativeProdTotalPrice
            );

            $replacedProduct = OrderItems::replaceWithAlternativeProduct(
                $this->orderId,
                $this->currentProdId,
                $alternativeProd['id'],
                $alternativeProdTotalPrice,
                $this->alternativeProdUserGivenQty
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'callParentRenderMethod');
            $this->dispatch('close-modal', ['id' => 'searchAlternativeProductModal']);

            if ($replacedPrice && $replacedProduct) {
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
