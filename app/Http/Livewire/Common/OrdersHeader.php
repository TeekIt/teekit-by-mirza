<?php

namespace App\Http\Livewire\Common;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\GophrDelivery;
use App\Enums\PaymentIntentStatusEnum;
use App\Models\OrdersFromOtherSeller;
use App\OrderItems;
use App\Orders;
use App\Services\EmailServices;
use App\Services\GophrServices;
use App\Services\OrderServices;
use App\Services\StripeServices;
use App\Services\StuartDeliveryServices;
use App\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersHeader extends Component
{
    use WithPagination;

    public
        $orderId,
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
        $selectedDeliveryDetails,
        $priceBySeller;

    protected $paginationTheme = 'bootstrap';

    public function mount(Orders $order)
    {
        $this->order = $order;
    }

    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'orderId',
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

    // public function renderSTOSModal($orderId)
    // {
    //     $this->resetModal();

    //     $this->order = Orders::getById($orderId);
    //     $this->orderItem = $this->order->order_items[0];

    //     $sellers = User::getParentAndChildSellersByCity(auth()->user()->city);
    //     $this->nearbySellers = GoogleMapServices::findDistanceByMakingChunks(
    //         auth()->user()->lat,
    //         auth()->user()->lon,
    //         $sellers,
    //         25
    //     );
    // }

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
            $this->emit('refreshChildComponent');
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

            $orderTotalPrice = $this->orderItem->product_price * $this->orderItem->product_qty;
            /* Send this product to another seller */
            OrdersFromOtherSeller::add(
                $this->order->created_by_type,
                $this->order->created_by_id,
                $selectedSeller->id,
                $this->orderItem->product_belongs_to_type,
                $this->orderItem->product_belongs_to_id,
                $this->orderItem->product_price,
                $this->orderItem->product_qty,
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
            $removed = OrderItems::removeItem($this->orderItem->id);
            /* Subtract the total price of this product/order_item from the current order's total */
            $subtracted = Orders::subFromOrderTotal($this->orderItem->order_id, $orderTotalPrice);
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
            $updated = Orders::updateInfo(
                id: $this->selectedOrder->id,
                currentTotal: $newOrderTotal,
                orderStatus: OrderStatusEnum::ACCEPTED
            );
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'acceptOrderModal']);

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

    public function orderIsAccepted($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::isViewed($orderId);

            if ($this->selectedOrder->type === OrderTypeEnum::DELIVERY->value) {

                $totalWeight = $this->selectedOrder->order_items[0]->product->weight;

                $currentDeliveryCharges = OrderServices::getTotalDeliveryCharges(
                    $this->selectedOrder->seller->lat,
                    $this->selectedOrder->seller->lon,
                    $this->selectedOrder->buyer->lat,
                    $this->selectedOrder->buyer->lon,
                    $totalWeight,
                );

                $currentTotalAmount = round($this->selectedOrder->current_total + $this->selectedOrder->service_charges + $currentDeliveryCharges);
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
            }

            if ($this->selectedOrder->type == OrderTypeEnum::SELF_PICKUP->value) {
                /**
                 * Remove bugs related to "sendPickupYourOrderMail()"
                 */
                EmailServices::sendPickupYourOrderMail($this->selectedOrder);
            }

            $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::ACCEPTED);
            /* Operation finished */
            sleep(1);
            $this->emit('refreshChildComponent');

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
            $this->emit('refreshChildComponent');

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
