<?php

namespace App\Http\Livewire\Sellers;

use App\Drivers;
use App\Models\OrdersFromOtherSeller;
use App\OrderItems;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\StripeServices;
use App\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;
use stdClass;

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
        $search;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [ 
        'alternativeProductIncluded' => 'render',
        'callParentResetModal' => 'resetModal'
    ];

    public function mount()
    {
        $this->seller_id = auth()->id();
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
            'search'
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

    public function renderInfoModal($id)
    {
        $data = Drivers::getUserByID($id);
        // $this->name = $data->name;
        // $this->l_name = $data->l_name;
        // $this->email = $data->email;
        // $this->phone = $data->phone;
        // $this->address_1 = $data->address_1;
        // $this->lat = $data->lat;
        // $this->lon = $data->lon;
        // $this->user_img = $data->user_img;
        // $this->last_login = $data->last_login;
        // $this->email_verified_at = $data->email_verified_at;
        // $this->pending_withdraw = $data->pending_withdraw;
        // $this->total_withdraw = $data->total_withdraw;
        // $this->is_online = $data->is_online;
        // $this->application_fee = $data->application_fee;
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
        $this->nearby_sellers = GoogleMapServices::findDistanceByMakingChunks(auth()->user()->lat, auth()->user()->lon, $sellers, 25);
    }

    public function sendItemToAnOtherStore()
    {
        $this->validate([
            'selected_nearby_seller' => 'required|string'
        ]);
        // dd($this->selected_nearby_seller);
        try {
            /* Perform some operation */
            $prod_total_price = $this->order_item['product_price'] * $this->order_item['product_qty'];
            $selected_seller = User::getStoreByBusinessName($this->selected_nearby_seller);
            // dd($this->order);
            // dd($this->order_item);
            
            /* Send this product to another store */
            $order_from_other_seller = OrdersFromOtherSeller::insertOrderFromOtherSeller(
                $this->order['user_id'],
                $selected_seller->id,
                $this->order['order_total'],
                $this->order['total_items'],
                isset($this->order['lat']) ? (float) $this->order['lat'] : null,
                isset($this->order['lon']) ? (float) $this->order['lon'] : null,
                $this->order['receiver_name'],
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

            OrderItems::insertOrderItem(
                $order_from_other_seller->id, 
                $this->order_item['product_id'], 
                $this->order_item['product_price'], 
                $this->order_item['product_qty'], 
                $this->order_item['user_choice']
            );

            // // /* Remove the item from current order items */
            // $removed = OrderItems::removeItem($this->order_item['id']);
            // // /* Subtract the total price of this product/order_item from the current order's total */
            // $subtracted = Orders::subFromOrderTotal($this->order_item['order_id'], $prod_total_price);
            // // dd($order_from_other_seller);

            /* Operation finished */
            sleep(1);
            if (true) {
                session()->flash('success', config('constants.SENT_TO_OTHER_STORE_SUCCESS'));
            } else {
                session()->flash('error', config('constants.SENT_TO_OTHER_STORE_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function renderRemoveItemModal($order_item)
    {
        $this->order_item = $order_item;
    }

    public function renderCustomerContactModel($receiver_name, $phone_number)
    {
        $this->receiver_name = $receiver_name;
        $this->phone_number = $phone_number;
    }

    public function orderIsReady($order)
    {
        try {
            /* Perform some operation */
            Orders::isViewed($order['id']);
            $updated = Orders::updateOrderStatus($order['id'], 'ready');
            if ($order['type'] == 'self-pickup') {
                $order_details = Orders::getOrderById($order['id']);
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

    public function cancelOrder($order)
    {
        try {
            /* Perform some operation */
            $order_details = Orders::getOrderById($order['id']);
            // dd($order_details);
            // Orders::updateOrderStatus($order['id'], 'cancelled');
            StripeServices::refundCustomer($order_details);


            $message = "Hello " . $order_details->user->name . " .
            Your order from " . $order_details->store->name . " was unsuccessful.
            Unfortunately " . $order_details->store->name . " is unable to complete your order. But don't worry 
            you have not been charged.
            If you need any kinda of assistance, please contact us via email at:
            admin@teekit.co.uk";

            // TwilioSmsService::sendSms($order_details->user->phone, $message);
            // EmailServices::sendOrderHasBeenCancelledMail($order_details);

            /* Operation finished */
            sleep(1);
            session()->flash('success', config('constants.ORDER_CANCELLATION_SUCCESS'));

            // if ($cancelled) {
            //     session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            // } else {
            //     session()->flash('error', config('constants.UPDATION_FAILED'));
            // }
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
    }

    public function isSearchByIdSet()
    {
        $searched_order_id = (int)$this->search;
        if ($searched_order_id != 0) $this->resetPage();
        return $searched_order_id;
    }

    public function render()
    {
        try {
            $data = Orders::getOrdersForView($this->isSearchByIdSet(), $this->seller_id, 'desc');
            return view('livewire.sellers.orders-livewire', compact('data'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
            $data = [];
            return view('livewire.sellers.orders-livewire', compact('data'));
        }
    }
}