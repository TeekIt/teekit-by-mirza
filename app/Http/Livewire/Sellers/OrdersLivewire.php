<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\GophrDelivery;
use App\Enums\PaymentIntentStatusEnum;
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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersLivewire extends Component
{
    use WithPagination;

    public
        $sellerId,
        $orderId,
        $currentProdId,
        $currentProdQty,
        $customerName,
        $phoneNumber,
        $order,
        $orderItem,
        $nearbySellers,
        $selectedNearbySeller,
        $selectedOrder,
        $search,
        $customOrderId,
        $requestOrderId,
        $additionalParcelDescription,
        $selectedDeliveryDetails;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'alternativeProductIncluded' => 'render',
        'callParentResetModal' => 'resetModal',
        'askParentToRefreshChildComponent' => '$refresh',
    ];

    public function mount(Request $request)
    {
        $this->sellerId = auth()->id();
        $this->requestOrderId = $request->requestOrderId;

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
            'orderItem',
            'nearbySellers',
            'selectedNearbySeller',
            'selectedOrder',
            'search',
            'customOrderId',
            'additionalParcelDescription',
            'selectedDeliveryDetails',
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
        $this->orderItem = $this->order->order_items[0];

        $sellersOfTheSameCity = User::getParentAndChildSellersByCity(auth()->user()->city);
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $this->order->customer_lat,
            $this->order->customer_lon,
            $sellersOfTheSameCity,
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
            json_encode(GophrServices::getJob($gophrDelivery->job_id)),
            true
        );
    }


    public function assignToGophrDriver()
    {
        try {
            /* Perform some operation */
            $order = Orders::getById($this->orderId);

            $parcelDescription = $this->additionalParcelDescription ?? "Please pickup your order ASAP";

            $response = GophrServices::createJob($order, $parcelDescription);

            if (isset($response->errors)) {
                Log::error($response->errors);

                $this->dispatchBrowserEvent('close-modal', ['id' => 'gophrModal']);

                throw new Exception(json_encode($response->errors[0]->message));
            }

            GophrDelivery::add(
                (new Orders)->getMorphClass(),
                $this->orderId,
                $response->data->job_id
            );

            $updated = Orders::updateOrderStatus($this->orderId, OrderStatusEnum::ON_THE_WAY);
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'gophrModal']);

            if ($updated && isset($response->data)) {
                session()->flash('success', config('constants.DELIVERY_SUCCESS'));
            } else {
                session()->flash('error', config('constants.DELIVERY_FAILED'));
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
            $stuartMessage = StuartDeliveryServices::stuartJobCreationLivewire(
                $this->orderId,
                $this->customOrderId
            );
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'stuartModal']);

            if ($stuartMessage === 'JobCreated') {
                session()->flash('success', config('constants.STUART_DELIVERY_SUCCESS'));
            } else {
                session()->flash('error', $stuartMessage);
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function sendItemToAnOtherStore()
    {
        $this->validate([
            'selectedNearbySeller' => 'required|string'
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
                $this->orderItem->product_belongs_to_type,
                $this->orderItem->product_belongs_to_id,
                $this->orderItem->product_price,
                $this->orderItem->product_qty,
                $productTotalPrice,
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

    public function orderIsAccepted($id)
    {
        try {
            /* Perform some operation */
            $order = Orders::isViewed($id);

            $updated = Orders::updateOrderStatus($id, OrderStatusEnum::ACCEPTED);

            /**
             * Remove bugs related to "sendPickupYourOrderMail()"
             */
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
            $this->selectedOrder = Orders::getById($orderId);

            $refunded = StripeServices::refundPaymentIntent($this->selectedOrder->payment_intent_id);
            if (isset($refunded->error)) {
                throw new Exception($refunded->error->message);
            }

            $cancelled = Orders::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);

            EmailServices::sendOrderHasBeenCancelledMail($this->selectedOrder);
            /* Operation finished */
            sleep(1);

            if ($cancelled && $refunded->status === PaymentIntentStatusEnum::CANCELED->value) {
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
        $this->resetModal();

        $this->resetPage();

        $this->reset([
            'requestOrderId'
        ]);
    }

    public function isSearchByIdSet()
    {
        if ($this->search) {
            $searchedOrderId = (int) $this->search;
            $this->requestOrderId = (int) $this->search;
        } else {
            $searchedOrderId = $this->requestOrderId;
        }

        if ($searchedOrderId != 0) $this->resetPage();

        return $searchedOrderId;
    }

    public function render()
    {
        try {
            $data = Orders::getOrdersForView(
                orderBy: 'desc',
                sellerId: $this->sellerId,
                orderId: $this->isSearchByIdSet(),
            );

            return view('livewire.sellers.orders-livewire', compact('data'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.SEARCH_FAILED'));

            $data = [];

            return view('livewire.sellers.orders-livewire', compact('data'));
        }
    }
}
