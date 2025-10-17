<?php

namespace App\Http\Livewire\Common;

use App\Models\GophrDelivery;
use App\Models\OrdersFromOtherSeller;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Services\GoogleMapServices;
use App\Services\GophrDeliveryServices;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersLivewire extends Component
{
    use WithPagination;

    public $sellerId;

    public $orderId;

    public $currentProdId;

    public $currentProdQty;

    public $customerName;

    public $phoneNumber;

    public $order;

    public $orderItem;

    public $nearbySellers;

    public $selectedNearbySeller;

    public $selectedOrder;

    public $search;

    public $customOrderId;

    public $requestOrderId;

    public $additionalParcelDescription;

    public $selectedDeliveryDetails;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'alternativeProductIncluded' => 'render',
        'callParentResetComponent' => 'resetComponent',
    ];

    /*
    * Lifecycle Hooks
    */
    public function mount(Request $request)
    {
        if (! User::isSuperAdmin()) {
            $this->sellerId = auth()->id();
        }

        $this->requestOrderId = $request->requestOrderId;

        $this->resetAllPaginators();
    }

    /*
     * Custom Helpers
     */
    public function resetComponent()
    {
        $this->resetValidation();

        $this->reset([
            'orderId',
            'currentProdId',
            'currentProdQty',
            'customerName',
            'phoneNumber',
            'orderItem',
            'nearbySellers',
            'selectedNearbySeller',
            'selectedOrder',
            'search',
            'customOrderId',
            'additionalParcelDescription',
            'selectedDeliveryDetails',
            'requestOrderId',
        ]);
    }

    public function resetAllPaginators()
    {
        $this->resetPage('sap_products_page');
    }

    public function renderOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function renderSAPModal($orderId, $currentProdId, $currentProdQty, $customerName, $phoneNumber)
    {
        /* Details of the current product & cutomer who has placed the order */
        $this->orderId = $orderId;
        $this->currentProdId = $currentProdId;
        $this->currentProdQty = $currentProdQty;
        $this->customerName = $customerName;
        $this->phoneNumber = $phoneNumber;
    }

    public function getSellersOfSameCityAndCategory()
    {
        return Cache::remember(
            'getSellersOfSameCityAndCategory' . $this->sellerId,
            Carbon::now()->addDay(),
            fn() => User::getParentAndChildSellersByCityAndCategory(
                auth()->user()->city,
                $this->orderItem->product->category_id,
                $this->sellerId,
            )
        );
    }

    public function renderSTOSModal($orderId)
    {
        $this->resetComponent();

        $this->order = Orders::getById($orderId);
        $this->orderItem = $this->order->order_items[0];

        // $sellersOfTheSameCity = User::getParentAndChildSellersByCity(auth()->user()->city);
        $sellersOfTheSameCityAndCategory = $this->getSellersOfSameCityAndCategory();
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $this->order->customer_lat,
            $this->order->customer_lon,
            $sellersOfTheSameCityAndCategory,
            $this->sellerId,
        );
    }

    public function renderRemoveItemModal($orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function renderCustomerContactModal($customerName, $phoneNumber)
    {
        $this->customerName = $customerName;
        $this->phoneNumber = $phoneNumber;
    }

    public function renderTrackGophrDeliveryModal($orderId)
    {
        $gophrDelivery = GophrDelivery::getByOrderId((new Orders)->getMorphClass(), $orderId, ['job_id']);
        $this->selectedDeliveryDetails = json_decode(
            json_encode(GophrDeliveryServices::getJob($gophrDelivery->job_id)),
            true
        );
    }

    /*
     * CRUD Methods
     */
    public function sendItemToAnOtherStore()
    {
        $this->validate([
            'selectedNearbySeller' => 'required|string',
        ]);
        try {
            /* Perform some operation */
            $selectedSeller = User::getSellerByBusinessName($this->selectedNearbySeller);

            $productTotalPrice = $this->orderItem->product_price * $this->orderItem->product_qty;
            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->order->created_by_type,
                $this->order->created_by_id,
                $selectedSeller->id,
                $this->order->id,
                $this->orderItem->product_belongs_to_type,
                $this->orderItem->product_belongs_to_id,
                $this->orderItem->product_price,
                $this->orderItem->product_qty,
                $productTotalPrice,
                isset($this->order->customer_lat) ? (float) $this->order->customer_lat : null,
                isset($this->order->customer_lon) ? (float) $this->order->customer_lon : null,
                $this->order->customer_name,
                $this->order->country_code,
                $this->order->phone_number,
                $this->order->address,
                $this->order->house_no,
                $this->order->flat,
                $this->order->country,
                $this->order->state,
                $this->order->city,
                $this->order->postcode,
                $this->order->payment_intent_id,
                $this->order->driver_charges,
                $this->order->delivery_charges,
                $this->order->service_charges,
                $this->order->device,
                $this->order->type,
                $this->order->description,
                $this->order->payment_status,
                $this->order->offloading,
                $this->order->offloading_charges,
                now(),
                $this->order->created_at,
            );
            /* Subtract the total price of this product/order_item from the current order's total */
            $subtracted = Orders::subFromOrderTotal($this->orderItem->order_id, $productTotalPrice);
            /**
             * If there's only 1 item in the order, remove the whole order,
             * else only remove the selected item from current order items
             */
            $removed = ($this->order->order_items->count() == 1) ? Orders::remove($this->order->id) : OrderItems::remove($this->orderItem->id);
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'sendToOtherStoresModal']);

            if ($removed && $subtracted) {
                session()->flash('success', config('constants.SENT_TO_OTHER_STORE_SUCCESS'));
            } else {
                session()->flash('error', config('constants.SENT_TO_OTHER_STORE_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function removeItemFromOrder()
    {
        try {
            /* Perform some operation */
            $removed = OrderItems::remove($this->orderItem['id']);

            $prodTotalPrice = $this->orderItem['product_price'] * $this->orderItem['product_qty'];
            $updated = Orders::subFromOrderTotal($this->orderItem['order_id'], $prodTotalPrice);
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'removeItemFromOrderModel']);

            if ($removed && $updated) {
                session()->flash('success', config('constants.PRODUCT_REMOVED_SUCCESSFULLY'));
            } else {
                session()->flash('error', config('constants.PRODUCT_REMOVED_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function resetThisPage()
    {
        $this->resetComponent();

        $this->resetPage();
    }

    public function isSearchByIdSet()
    {
        if ($this->search) {
            $searchedOrderId = (int) $this->search;
            $this->requestOrderId = (int) $this->search;
        } else {
            $searchedOrderId = $this->requestOrderId;
        }

        if ($searchedOrderId != 0) {
            $this->resetPage();
        }

        return $searchedOrderId;
    }

    public function render()
    {
        try {
            if (! User::isSuperAdmin()) {
                $data = Orders::getOrdersForView(
                    orderId: $this->isSearchByIdSet(),
                    sellerId: $this->sellerId,
                    orderBy: 'desc',
                );
            } else {
                $data = Orders::getOrdersForSuperAdminView(
                    orderId: $this->isSearchByIdSet(),
                    orderBy: 'desc',
                );
            }

            return view('livewire.common.orders-livewire', compact('data'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.SEARCH_FAILED'));

            $data = [];

            return view('livewire.common.orders-livewire', compact('data'));
        }
    }
}
