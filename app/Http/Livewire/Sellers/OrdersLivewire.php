<?php

namespace App\Http\Livewire\Sellers;

use App\Enums\OrderStatusEnum;
use App\Models\OrdersFromOtherSeller;
use App\OrderItems;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
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
        $order_id,
        $current_prod_id,
        $current_prod_qty,
        $receiver_name,
        $phone_number,
        $order,
        $order_item,
        $nearby_sellers,
        $selected_nearby_seller,
        $search,
        $custom_order_id,
        $request_order_id;

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
        $this->resetAllErrors();
        $this->reset([
            'order_id',
            'current_prod_id',
            'current_prod_qty',
            'receiver_name',
            'phone_number',
            'order_item',
            'nearby_sellers',
            'selected_nearby_seller',
            'search',
            'custom_order_id',
        ]);
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function resetAllPaginators()
    {
        $this->resetPage('sap_products_page');
    }

    public function renderStuartModal($order_id)
    {
        $this->order_id = $order_id;
    }

    public function renderSAPModal($order_id, $current_prod_id, $current_prod_qty, $receiver_name, $phone_number)
    {
        /* Details of the current product & cutomer who has placed the order */
        $this->order_id = $order_id;
        $this->current_prod_id = $current_prod_id;
        $this->current_prod_qty = $current_prod_qty;
        $this->receiver_name = $receiver_name;
        $this->phone_number = $phone_number;
    }

    public function renderSTOSModal($order, $order_item)
    {
        $this->order = $order;
        $this->order_item = $order_item;

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

    public function renderCustomerContactModal($receiver_name, $phone_number)
    {
        $this->receiver_name = $receiver_name;
        $this->phone_number = $phone_number;
    }

    public function assignToStuartDriver()
    {
        try {
            /* Perform some operation */
            $stuart_message = StuartDeliveryServices::stuartJobCreationLivewire($this->order_id, $this->custom_order_id);
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
            $orderTotalPrice = $this->order_item['product_price'] * $this->order_item['product_qty'];
            $selectedSeller = User::getStoreByBusinessName($this->selected_nearby_seller);

            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->order['created_by_id'],
                $selectedSeller->id,
                $this->order_item['product_id'],
                $this->order_item['product_price'],
                $this->order_item['product_qty'],
                $orderTotalPrice,
                isset($this->order['lat']) ? (float) $this->order['lat'] : null,
                isset($this->order['lon']) ? (float) $this->order['lon'] : null,
                $this->order['customer_name'],
                $this->order['phone_number'],
                $this->order['address'],
                $this->order['house_no'],
                $this->order['flat'],
                $this->order['driver_charges'],
                $this->order['delivery_charges'],
                $this->order['service_charges'],
                $this->order['device'],
                $this->order['type'],
                $this->order['description'],
                $this->order['payment_status'],
                $this->order['offloading'],
                $this->order['offloading_charges']
            );

            /* Remove the item from current order items */
            $removed = OrderItems::removeItem($this->order_item['id']);
            /* Subtract the total price of this product/order_item from the current order's total */
            $subtracted = Orders::subFromOrderTotal($this->order_item['order_id'], $orderTotalPrice);
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
            $order_details = Orders::isViewed($id);
            $updated = Orders::updateOrderStatus($id, OrderStatusEnum::ACCEPTED);
            if ($order_details->type == 'self-pickup') {
                EmailServices::sendPickupYourOrderMail($order_details);
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

    // public function orderIsReady($order)
    // {
    //     try {
    //         /* Perform some operation */
    //         $updated = Orders::updateOrderStatus($order['id'], 'ready');
    //         if ($order['type'] == 'self-pickup') {
    //             $order_details = Orders::getById($order['id']);
    //             EmailServices::sendPickupYourOrderMail($order_details);
    //         }
    //         /* Operation finished */
    //         sleep(1);
    //         if ($updated) {
    //             session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
    //         } else {
    //             session()->flash('error', config('constants.UPDATION_FAILED'));
    //         }
    //     } catch (Exception $error) {
    //         report($error);
    //         session()->flash('error', $error->getMessage());
    //     }
    // }

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
            $orderDetails = Orders::getById($orderId);
            // dd($orderDetails);
            StripeServices::refundCustomer($orderDetails);

            $cancelled = Orders::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);

            $message = "Hello " . $orderDetails->user->name . " .
            Your order from " . $orderDetails->store->name . " was unsuccessful.
            Unfortunately " . $orderDetails->store->name . " is unable to complete your order. But don't worry 
            you have not been charged.
            If you need any kind of assistance, please contact us via email at:
            admin@teekit.co.uk";

            // TwilioSmsService::sendSms($orderDetails->user->phone, $message);
            // EmailServices::sendOrderHasBeenCancelledMail($orderDetails);

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
            $searched_order_id = (int)$this->search;
            $this->request_order_id = (int)$this->search;
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
