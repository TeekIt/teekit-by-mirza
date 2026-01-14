<?php

namespace App\Livewire\Common;

use App\Actions\Orders\MoveOrderToOtherNearBySellersAction;
use App\Models\GophrDelivery;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\User;
use App\Services\GoogleMapServices;
use App\Services\GophrDeliveryServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;

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

    public $successMessage;

    public $errorMessage;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'callParentRenderMethod' => 'render',
        'callParentResetComponentMethod' => 'resetComponent',
    ];

    /*
    * Lifecycle Hooks
    */
    public function mount(Request $request)
    {
        if (! User::isSuperAdmin()) {
            $this->sellerId = User::getAuthUser()->id;
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
            'order',
            'orderItem',
            'nearbySellers',
            'selectedNearbySeller',
            'selectedOrder',
            'search',
            'customOrderId',
            'requestOrderId',
            'additionalParcelDescription',
            'selectedDeliveryDetails',
            'successMessage',
            'errorMessage',
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
            fn() => User::getActiveParentAndChildSellersByCityAndCategory(
                User::getAuthUser()->city,
                $this->orderItem->product->category_id,
                $this->sellerId,
            )
        );
    }

    public function renderSTOSModal($orderId, $orderItemId)
    {
        $this->resetComponent();

        $this->order = Orders::getById($orderId);
        $this->orderItem = $this->order->order_items->firstWhere('id', '=', $orderItemId);

        // $sellersOfTheSameCity = User::getParentAndChildSellersByCity(User::getAuthUser()->city);
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

            $moved = (new MoveOrderToOtherNearBySellersAction())->execute(
                order: $this->order,
                seller: $selectedSeller,
                orderItem: $this->orderItem,
            );
            /* Operation finished */
            sleep(1);
            unset($this->order);
            unset($this->orderItem);

            $this->dispatch('close-modal', ['id' => 'sendToOtherStoresModal']);

            if ($moved) {
                $this->successMessage = config('constants.SENT_TO_OTHER_STORE_SUCCESS');
            } else {
                $this->errorMessage = config('constants.SENT_TO_OTHER_STORE_FAILED');
            }
        } catch (Exception $error) {
            report($error);
            $this->errorMessage = $error->getMessage();
        }
    }

    public function removeItemFromOrder()
    {
        try {
            /* Perform some operation */
            $prodTotalPrice = $this->orderItem['product_price'] * $this->orderItem['product_qty'];
            $updated = Orders::subFromOrderTotal($this->orderItem['order_id'], $prodTotalPrice);
            $removed = OrderItems::remove($this->orderItem['id']);
            /* Operation finished */
            sleep(1);
            unset($this->orderItem);

            $this->dispatch('close-modal', ['id' => 'removeItemFromOrderModel']);

            if ($removed && $updated) {
                $this->successMessage = config('constants.PRODUCT_REMOVED_SUCCESSFULLY');
            } else {
                $this->errorMessage = config('constants.PRODUCT_REMOVED_FAILED');
            }
        } catch (Exception $error) {
            report($error);
            $this->errorMessage = $error->getMessage();
        }
    }

    public function resetThisPage()
    {
        $this->resetComponent();

        $this->resetPage();
    }

    public function performSearch()
    {
        /**
         * This method is called when the search query is submitted
         * Livewire will automatically call render() after this method completes
         * The render() method will use the updated $search property
         */
    }

    public function isSearchByIdSet()
    {
        if ($this->search) {
            $orderId = (int) $this->search;
            $searchedOrderId = $orderId;
            $this->requestOrderId = $orderId;
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
                $data = Orders::getOrdersForSellerView(
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
            $this->errorMessage = config('constants.SEARCH_FAILED');

            $data = [];

            return view('livewire.common.orders-livewire', compact('data'));
        }
    }
}
