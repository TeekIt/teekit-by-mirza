<?php

namespace App\Http\Livewire\Sellers;

use App\Models\Categories;
use App\Models\Products;
use App\Models\Qty;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryLivewire extends Component
{
    use WithPagination;

    public $category_id;

    public $category;

    public $product;

    public $product_id;

    public $quantity = [];

    public $inventories;

    public $owner;

    public $search = '';

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    /*
    * Helpers
    */
    public function getFeaturedProducts(object $products)
    {
        $data = [];
        foreach ($products as $product) {
            if ($product->featured === 1) {
                array_push($data, $product);
            }
        }

        return $data;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /*
    * CRUD Methods
    */
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
            session()->flash('error', $error->getMessage());
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
            session()->flash('error', $error->getMessage());
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
            session()->flash('error', $error->getMessage());
        }
    }

    public function updateProductQuantity($index)
    {
        try {
            /* Perform some operation */
            $updated = Qty::updateChildProductQty($this->quantity[$index]);
            Cache::flush();
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error->getMessage());
        }
    }

    public function populateQuantityArray(object $data)
    {
        return $data->map(function ($product) {
            return [
                'prod_id' => $product->prod_id,
                'category_id' => $product->category_id,
                'parent_seller_id' => $product->parent_seller_id,
                'child_seller_id' => ($product->child_seller_id === null) ? auth()->id() : $product->child_seller_id,
                'qty_id' => ($product->child_seller_id === null) ? 0 : $product->qty_id,
                'qty' => ($product->child_seller_id === null) ? 0 : $product->qty,
            ];
        });
    }

    public function render()
    {
        $categories = Categories::allCategories(['id', 'category_name']);
        $featuredProducts = [];
        $this->category_id = ($this->category_id == 0) ? null : $this->category_id;

        if (Gate::allows('seller')) {
            $data = Products::getParentSellerProductsForView(auth()->id(), $this->search, $this->category_id, orderBy: 'desc');
            $featuredProducts = $this->getFeaturedProducts($data);
        } elseif (Gate::allows('child_seller')) {
            /*
            1st scenario when a child store will come he will have parent products with "0" Qty
            2nd after entering the Qty for each product a child store can see his own entered Qty
             */
            $data = Products::getChildSellerProductsForView(auth()->id(), $this->search, $this->category_id);
            $this->quantity = $this->populateQuantityArray($data);
        }

        return view('livewire.sellers.inventory-livewire', [
            'data' => $data,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
