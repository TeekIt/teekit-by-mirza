<?php

namespace App\Http\Livewire\Common;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\GophrDelivery;
use App\Enums\PaymentIntentStatusEnum;
use App\Models\OrdersFromOtherSeller;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\GophrServices;
use App\Services\OrderServices;
use App\Services\StripeServices;
use App\Services\StuartDeliveryServices;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use stdClass;

class OrdersHeader extends Component
{
    use WithPagination;

    public
        $orderId,
        $currentProdQty,
        $customerName,
        $phoneNumber,
        $orderItem,
        $nearbySellers,
        $selectedNearbySeller,
        $selectedOrder,
        $customOrderId,
        $additionalParcelDescription,
        $selectedDeliveryDetails,
        $priceBySeller;

    public $order;

    protected $sellerId;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshThisComponent' => '$refresh',
    ];

    public function mount(Orders $order)
    {
        $this->sellerId = auth()->user()->id;
        $this->order = $order;
    }
    /* 
     * Custom Helpers
     */
    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'orderId',
            'currentProdQty',
            'customerName',
            'phoneNumber',
            'orderItem',
            'nearbySellers',
            'selectedNearbySeller',
            'selectedOrder',
            'customOrderId',
            'additionalParcelDescription',
            'selectedDeliveryDetails',
            'priceBySeller',
        ]);
    }

    // public function renderStuartModal($orderId)
    // {
    //     $this->orderId = $orderId;
    // }

    public function renderOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function renderCustomProductOrderModal($orderId)
    {
        $this->resetModal();

        $this->selectedOrder = Orders::getById($orderId);

        $this->dispatchBrowserEvent('show-modal', ['id' => 'acceptCustomProductOrderModal']);
    }

    public function renderTrackGophrDeliveryModal($orderId)
    {
        try {
            $gophrDelivery = GophrDelivery::getByOrderId((new Orders)->getMorphClass(), $orderId, ['job_id']);

            $response = GophrServices::getJob($gophrDelivery->job_id);
            if (isset($response->errors)) {
                $this->dispatchBrowserEvent('close-modal', ['id' => 'trackGophrDeliveryModal']);

                Log::error($response->errors);

                throw new Exception(json_encode($response->errors[0]->message));
            }

            $this->selectedDeliveryDetails = json_decode(
                json_encode($response),
                true
            );
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function getSellersOfSameCity()
    {
        return Cache::remember(
            'getSellersOfSameCity' . $this->sellerId,
            Carbon::now()->addDay(),
            fn() => User::getParentAndChildSellersByCity(auth()->user()->city)
        );
    }

    public function noNearBySellers($orderId)
    {
        $this->orderId = $orderId;
        $this->dispatchBrowserEvent('show-modal', ['id' => 'noOtherSellersModal']);
    }

    public function capturePayment($currentTotal = null)
    {
        $totalWeight = $this->selectedOrder->order_items[0]->product->weight;

        $currentDeliveryCharges = OrderServices::getTotalDeliveryCharges(
            $this->selectedOrder->seller->lat,
            $this->selectedOrder->seller->lon,
            $this->selectedOrder->buyer->lat,
            $this->selectedOrder->buyer->lon,
            $totalWeight,
        );

        $currentTotal ??= $this->selectedOrder->current_total;

        $currentTotalAmount = round($currentTotal + $this->selectedOrder->service_charges + $currentDeliveryCharges);
        $initialTotalAmount = round($this->selectedOrder->initial_total + $this->selectedOrder->service_charges + $this->selectedOrder->delivery_charges);

        if ($currentTotalAmount <= $initialTotalAmount) {
            $response = StripeServices::capturePaymentIntent(
                $this->selectedOrder->payment_intent_id,
                bcmul($currentTotalAmount, 100),
            );
            if (isset($response->error)) {
                throw new Exception($response->error->message);
            }
        } else {
            throw new Exception('Your current order total should be equal to or less than the initial order total amount');
        }

        return $response;
    }
    /* 
     * CRUD Methods
     */
    public function assignToGophrDriver()
    {
        try {
            /* Perform some operation */
            $order = Orders::getById($this->orderId);

            $parcelDescription = $this->additionalParcelDescription ?? "Please pickup your order ASAP";

            $response = GophrServices::createJob($order, $parcelDescription);
            if (isset($response->errors)) {
                $this->dispatchBrowserEvent('close-modal', ['id' => 'gophrModal']);

                Log::error($response->errors);

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
            $this->emitSelf('refreshThisComponent');
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

    public function sendCustomProductOrderToAnOtherSeller($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::getById($orderId);

            $orderTotalPrice = $this->selectedOrder->order_items[0]->product_price * $this->selectedOrder->order_items[0]->product_qty;
            /* Get sellers who belongs to the city of this store owner */
            $sellersOfTheSameCity = $this->getSellersOfSameCity();
            /* Get sellers who are nearby to the order placing buyer */
            $nearbySellers = GoogleMapServices::getNearBySellers(
                $this->selectedOrder->customer_lat,
                $this->selectedOrder->customer_lon,
                $sellersOfTheSameCity,
                $this->sellerId,
            );

            if (empty($nearbySellers))
                return $this->noNearBySellers($orderId);

            $randomIndex = array_rand($nearbySellers, 1);

            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->selectedOrder->created_by_type,
                $this->selectedOrder->created_by_id,
                $nearbySellers[$randomIndex]['id'],
                $this->selectedOrder->order_items[0]->product_belongs_to_type,
                $this->selectedOrder->order_items[0]->product_belongs_to_id,
                $this->selectedOrder->order_items[0]->product_price,
                $this->selectedOrder->order_items[0]->product_qty,
                $orderTotalPrice,
                (float) $this->selectedOrder->customer_lat ?? null,
                (float) $this->selectedOrder->customer_lon ?? null,
                $this->selectedOrder->customer_name,
                $this->selectedOrder->phone_number,
                $this->selectedOrder->address,
                $this->selectedOrder->house_no,
                $this->selectedOrder->flat,
                $this->selectedOrder->country,
                $this->selectedOrder->state,
                $this->selectedOrder->city,
                $this->selectedOrder->postcode,
                $this->selectedOrder->payment_intent_id,
                $this->selectedOrder->driver_charges,
                $this->selectedOrder->delivery_charges,
                $this->selectedOrder->service_charges,
                $this->selectedOrder->device,
                $this->selectedOrder->type,
                $this->selectedOrder->description,
                $this->selectedOrder->payment_status,
                $this->selectedOrder->offloading,
                $this->selectedOrder->offloading_charges,
                now(),
                $this->selectedOrder->created_at,
            );
            /* Remove the whole order in case of custom product order's */
            $removed = Orders::remove($this->selectedOrder->id);

            info('The current order has been sent to seller: ' . $nearbySellers[$randomIndex]['id']);
            /* Operation finished */
            sleep(1);
            $this->emitSelf('refreshThisComponent');

            if ($removed) {
                session()->flash('success', config('constants.SENT_TO_OTHER_STORE_SUCCESS'));
            } else {
                session()->flash('error', config('constants.SENT_TO_OTHER_STORE_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function customProductOrderIsAccepted()
    {
        $this->validate([
            'priceBySeller' => [
                'required',
                'numeric',
                'max:' . $this->selectedOrder->order_items[0]->product_price,
                'min:1',
            ],
        ]);

        try {
            /* Perform some operation */
            Orders::isViewed($this->selectedOrder->id);

            $newOrderTotal = $this->priceBySeller * $this->selectedOrder->order_items[0]->product_qty;

            $response = $this->capturePayment($newOrderTotal);
        
            $updated = Orders::updateInfo(
                id: $this->selectedOrder->id,
                currentTotal: $newOrderTotal,
                orderStatus: OrderStatusEnum::ACCEPTED
            );
            /* Operation finished */
            sleep(1);
            $this->emitSelf('refreshThisComponent');
            $this->dispatchBrowserEvent('close-modal', ['id' => 'acceptCustomProductOrderModal']);

            if ($updated && $response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            $this->dispatchBrowserEvent('close-modal', ['id' => 'acceptCustomProductOrderModal']);

            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function orderIsAccepted($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::isViewed($orderId);

            $response = $this->capturePayment();

            if ($this->selectedOrder->type == OrderTypeEnum::SELF_PICKUP->value) {
                /**
                 * Remove bugs related to "sendPickupYourOrderMail()"
                 */
                EmailServices::sendPickupYourOrderMail($this->selectedOrder);
            }

            $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::ACCEPTED);
            /* Operation finished */
            sleep(1);
            $this->emitSelf('refreshThisComponent');

            if ($updated && $response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    // public function orderIsCompleted($id)
    // {
    //     try {
    //         /* Perform some operation */
    //         $updated = Orders::updateOrderStatus($id, OrderStatusEnum::COMPLETE);
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
            $this->emitSelf('refreshThisComponent');

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

    public function render()
    {
        return view('livewire.common.orders-header');
    }
}
