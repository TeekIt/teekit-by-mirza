<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\OrdersFromOtherSeller;
use App\OrderItems;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\GophrServices;
use App\Services\StripeServices;
use App\Services\StuartDeliveryServices;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersLivewire extends Component
{
    use WithPagination;

    public
        $seller_id,
        $orderId,
        $currentProdId,
        $currentProdQty,
        $customerName,
        $phoneNumber,
        $order,
        $order_item,
        $nearby_sellers,
        $selected_nearby_seller,
        $search,
        $custom_order_id,
        $request_order_id,
        $additionalParcelDescription;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'alternativeProductIncluded' => 'render',
        'callParentResetModal' => 'resetModal'
    ];

    public function mount(Request $request)
    {
        $this->seller_id = auth()->id();
        $this->request_order_id = $request->request_order_id;
        $this->resetAllPaginators();
    }

    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'orderId',
            'currentProdId',
            'currentProdQty',
            'customerName',
            'phoneNumber',
            'order_item',
            'nearby_sellers',
            'selected_nearby_seller',
            'search',
            'custom_order_id',
        ]);
    }

    public function resetAllPaginators()
    {
        $this->resetPage('sap_products_page');
    }

    // public function renderStuartModal($orderId)
    // {
    //     $this->orderId = $orderId;
    // }

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

    public function renderSTOSModal($orderId)
    {
        $this->resetModal();

        $this->order = Orders::getById($orderId);
        $this->order_item = $this->order->order_items[0];

        $sellers = User::getParentAndChildSellersByCity(auth()->user()->city);
        $this->nearby_sellers = GoogleMapServices::findDistanceByMakingChunks(
            auth()->user()->lat,
            auth()->user()->lon,
            $sellers,
            25
        );
    }

    public function renderRemoveItemModal($order_item)
    {
        $this->order_item = $order_item;
    }

    public function renderCustomerContactModal($customerName, $phoneNumber)
    {
        $this->customerName = $customerName;
        $this->phoneNumber = $phoneNumber;
    }

    public function assignToGophrDriver()
    {
        try {
            /* Perform some operation */
            $order = Orders::getById($this->orderId);

            $parcelDescription = $this->additionalParcelDescription ?? "Please pickup your order ASAP";

            $response = GophrServices::createJob($order, $parcelDescription);

            if (isset($response->errors)) {
                $this->dispatchBrowserEvent('close-modal', ['id' => 'gophrModal']);
                throw new Exception(json_encode($response->errors));
            }

            $updated = Orders::updateOrderStatus($this->orderId, OrderStatusEnum::ON_THE_WAY);
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'gophrModal']);

            if ($updated && isset($response->data)) {
                session()->flash('success', config('constants.DELIVERY_SUCCESS'));
            } else {
                session()->flash('error', json_encode($response->errors));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function assignToStuartDriver()
    {
        try {
            /* Perform some operation */
            $stuart_message = StuartDeliveryServices::stuartJobCreationLivewire(
                $this->orderId,
                $this->custom_order_id
            );
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'stuartModal']);
            if ($stuart_message === 'JobCreated') {
                session()->flash('success', config('constants.STUART_DELIVERY_SUCCESS'));
            } else {
                session()->flash('error', $stuart_message);
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function sendItemToAnOtherStore()
    {
        $this->validate([
            'selected_nearby_seller' => 'required|string'
        ]);
        try {
            /* Perform some operation */
            $selectedSeller = User::getSellerByBusinessName($this->selected_nearby_seller);

            $orderTotalPrice = $this->order_item->product_price * $this->order_item->product_qty;
            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->order->created_by_type,
                $this->order->created_by_id,
                $selectedSeller->id,
                $this->order_item->product_belongs_to_type,
                $this->order_item->product_belongs_to_id,
                $this->order_item->product_price,
                $this->order_item->product_qty,
                $orderTotalPrice,
                isset($this->order->customer_lat) ? (float) $this->order->customer_lat : null,
                isset($this->order->customer_lon) ? (float) $this->order->customer_lon : null,
                $this->order->customer_name,
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
            /* Remove the item from current order items */
            $removed = OrderItems::removeItem($this->order_item->id);
            /* Subtract the total price of this product/order_item from the current order's total */
            $subtracted = Orders::subFromOrderTotal($this->order_item->order_id, $orderTotalPrice);
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

    public function orderIsAccepted($id)
    {
        try {
            /* Perform some operation */
            $order = Orders::isViewed($id);

            $updated = Orders::updateOrderStatus($id, OrderStatusEnum::ACCEPTED);

            if ($order->type == OrderTypeEnum::SELF_PICKUP->value) {
                EmailServices::sendPickupYourOrderMail($order);
            }
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function orderIsCompleted($id)
    {
        try {
            /* Perform some operation */
            $updated = Orders::updateOrderStatus($id, OrderStatusEnum::COMPLETE);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function cancelOrder($orderId)
    {
        try {
            /* Perform some operation */
            $order = Orders::getById($orderId);

            StripeServices::refundCustomer($order);

            $cancelled = Orders::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);

            EmailServices::sendOrderHasBeenCancelledMail($order);
            /* Operation finished */
            sleep(1);

            if ($cancelled) {
                session()->flash('success', config('constants.ORDER_CANCELLATION_SUCCESS'));
            } else {
                session()->flash('error', config('constants.ORDER_CANCELLATION_FAILED'));
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
            $prod_total_price = $this->order_item['product_price'] * $this->order_item['product_qty'];
            $removed = OrderItems::removeItem($this->order_item['id']);
            $updated = Orders::subFromOrderTotal($this->order_item['order_id'], $prod_total_price);
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
        $this->resetModal();

        $this->resetPage();

        $this->reset([
            'request_order_id'
        ]);
    }

    public function isSearchByIdSet()
    {
        if ($this->search) {
            $searched_order_id = (int) $this->search;
            $this->request_order_id = (int) $this->search;
        } else {
            $searched_order_id = $this->request_order_id;
        }

        if ($searched_order_id != 0) $this->resetPage();

        return $searched_order_id;
    }

    public function render()
    {
        try {
            $data = Orders::getOrdersForView(
                orderBy: 'desc',
                sellerId: $this->seller_id,
                orderId: $this->isSearchByIdSet(),
            );
            return view('livewire.sellers.orders-livewire', compact('data'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            $data = [];
            return view('livewire.sellers.orders-livewire', compact('data'));
        }
    }
}
