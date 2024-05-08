<?php

namespace App\Http\Livewire\Sellers;

use App\Orders;
use App\Qty;
use App\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SellerDashboardLivewire extends Component
{
    use WithPagination;
    public
        $seller_id;

    protected $paginationTheme = 'bootstrap';
    /* 
    * Lifecycle Hooks
    */
    public function mount()
    {
        $this->seller_id = auth()->id();
    }
    /* 
    * Helpers
    */
    // 

    /* 
    * CRUD Methods
    */
    // 

    public function render()
    {
        $seller = User::getUserByID(id: $this->seller_id, columns: ['*']);
        $pending_orders = Orders::getOrdersByStatusWhereSellerId($this->seller_id, 'pending')->count();
        $total_orders = Orders::getTotalOrdersBySellerId($this->seller_id)->count();
        $total_products = Qty::getTotalProductsCountBySellerId($this->seller_id);
        $total_sales = Orders::getTotalSalesBySellerId($this->seller_id);
        $all_orders = Orders::where('seller_id', $this->seller_id)->whereNotNull('order_status')
            ->orderby(DB::raw('case when is_viewed = 0 then 0 when order_status = "pending" then 1 when order_status = "ready" then 2 when order_status = "assigned" then 3
                 when order_status = "onTheWay" then 4 when order_status = "delivered" then 5 end'))
            ->paginate(5);

        return view('livewire.sellers.seller-dashboard-livewire', [
            'seller' => $seller,
            'pending_orders' => $pending_orders,
            'total_products' => $total_products,
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'all_orders' => $all_orders
        ]);
    }
}
