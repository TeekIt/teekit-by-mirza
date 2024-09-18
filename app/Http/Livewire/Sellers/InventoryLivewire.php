<?php

namespace App\Http\Livewire\Sellers;

use Illuminate\Support\Facades\Gate;
use App\Products;
use App\Qty;
use Exception;
use Livewire\Component;
use App\Categories;
use Livewire\WithPagination;

class InventoryLivewire extends Component
{
    use WithPagination;
    
    public
        $category_id,
        $category,
        $product,
        $product_id,
        $quantity = [],
        $inventories,
        $owner,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    public function toggleProduct($id, $status)
    {
        try {
            /* Perform some operation */
            $updated = Products::toggleProduct($id, $status);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                if ($status == 0) {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                } else {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                }
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function toggleAllProducts($status)
    {
        try {
            /* Perform some operation */
            $updated = Products::toggleAllProducts($status);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function markAsFeatured($id, $status)
    {
        try {
            /* Perform some operation */
            $updated = Products::markAsFeatured($id, $status);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                if ($status == 0) {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                } else {
                    session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
                }
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateProductQuantity($index)
    {
        try {
            /* Perform some operation */
            $updated = Qty::updateChildProductQty($this->quantity[$index]);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function populateQuantityArray(object $data)
    {
        return $data->map(function ($product) {
            return [
                'prod_id' => $product->prod_id,
                'parent_seller_id' => $product->parent_seller_id,
                'child_seller_id' => ($product->child_seller_id === null) ? auth()->id() : $product->child_seller_id,
                'qty_id' => ($product->child_seller_id === null) ? 0 : $product->qty_id,
                'qty' => ($product->child_seller_id === null) ? 0 : $product->qty
            ];
        });
    }

    public function getFeaturedProducts(object $products)
    {
        $data = [];
        foreach ($products as $product) if ($product->featured === 1) array_push($data, $product);
        return $data;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Categories::allCategories(['id', 'category_name']);
        $featured = [];
        $this->category_id = ($this->category_id == 0) ? null : $this->category_id;
        if (Gate::allows('seller')) {
            $data = Products::getParentSellerProductsForView(auth()->id(), $this->search, $this->category_id, order_by: 'desc');
            $featured = $this->getFeaturedProducts($data);
        } elseif (Gate::allows('child_seller')) {
            /*
            1st scenario when a child store will come he will have parent products with "0" Qty
            2nd after entering the Qty for each product a child store can see his own entered Qty
             */
            $data = Products::getChildSellerProductsForView(auth()->id(), $this->search, $this->category_id);
            $this->quantity = $this->populateQuantityArray($data);
        }
        return view('livewire.sellers.inventory-livewire', ['data' => $data, 'categories' => $categories, 'featured_products' => $featured]);
    }
}
