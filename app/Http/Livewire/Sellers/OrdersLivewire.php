<?php

namespace App\Http\Livewire\Sellers;

use App\Drivers;
use App\Mail\OrderIsReadyMail;
use App\Orders;
use App\Services\EmailManagement;
use App\Services\StripeServices;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersLivewire extends Component
{
    use WithPagination;
    public
        $seller_id,
        $name,
        $l_name,
        $email,
        $phone,
        $address_1,
        $lat,
        $lon,
        $user_img,
        $last_login,
        $email_verified_at,
        $pending_withdraw,
        $total_withdraw,
        $is_online,
        $application_fee,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->seller_id = Auth::id();
    }

    public function resetModal()
    {
        $this->resetAllErrors();
        $this->reset([
            'name',
            'l_name',
            'email',
            'phone',
            'address_1',
            'lat',
            'lon',
            'user_img',
            'last_login',
            'email_verified_at',
            'pending_withdraw',
            'total_withdraw',
            'is_online',
            'application_fee',
        ]);
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function renderInfoModal($id)
    {
        $data = Drivers::getUserByID($id);
        $this->name = $data->name;
        $this->l_name = $data->l_name;
        $this->email = $data->email;
        $this->phone = $data->phone;
        $this->address_1 = $data->address_1;
        $this->lat = $data->lat;
        $this->lon = $data->lon;
        $this->user_img = $data->user_img;
        $this->last_login = $data->last_login;
        $this->email_verified_at = $data->email_verified_at;
        $this->pending_withdraw = $data->pending_withdraw;
        $this->total_withdraw = $data->total_withdraw;
        $this->is_online = $data->is_online;
        $this->application_fee = $data->application_fee;
    }

    public function orderIsReady($order)
    {
        try {
            /* Perform some operation */
            Orders::isViewed($order['id']);
            $updated = Orders::updateOrderStatus($order['id'], 'ready');
            if ($order['type'] == 'self-pickup') {
                $order_details = Orders::getOrderById($order['id']);
                EmailManagement::sendPickUpYouOrderMail($order_details);
            }
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

    public function cancelOrder($order)
    {
        try {
            /* Perform some operation */
            $order_details = Orders::getOrderById($order['id']);
            // dd($order_details->order_status);

            Orders::updateOrderStatus($order['id'], 'cancelled');

            // $order_details->order_status = 'cancelled';
            // $order_details->save();
            dd($order_details);

            StripeServices::refundCustomer($order_details);

            $message = "Hello " . $order->user->name . " .
            Your order from " . $order->store->name . " was unsuccessful.
            Unfortunately " . $order->store->name . " is unable to complete your order. But don't worry 
            you have not been charged.
            If you need any kinda of assistance, please contact us via email at:
            admin@teekit.co.uk";
            $sms = new TwilioSmsService();
            $sms->sendSms($order->user->phone, $message);
            Mail::to([$order->user->email])
                ->send(new OrderIsCanceledMail($order));
            flash('Order is successfully cancelled')->success();
            return back();

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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $data = Orders::getOrdersForView(null, $this->seller_id);
        return view('livewire.sellers.orders-livewire', compact('data'));
    }
}
