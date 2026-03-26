<?php

namespace App\Livewire\Common;

use App\Actions\Orders\MoveOrderToOtherNearBySellersAction;
use App\Enums\GophrCancellationReasonEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentIntentStatusEnum;
use App\Models\GophrDelivery;
use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\User;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\Services\GophrDeliveryServices;
use App\Services\OrderServices;
use App\Services\StripeServices;
use App\Services\StuartDeliveryServices;
use App\Services\UUIDServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersHeaderLivewire extends Component
{
    use WithPagination;

    public $sellerId;

    public $isOrderFromOtherSeller;

    #[Reactive]
    public $order;

    public $orderId;

    public $currentProdQty;

    public $customerName;

    public $phoneNumber;

    public $orderItem;

    public $nearbySellers;

    public $selectedNearbySeller;

    public $selectedOrder;

    public $customOrderId;

    public $additionalParcelDescription;

    public $selectedDeliveryDetails;

    public $priceBySeller;

    public $deliveryJob;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshThisComponent' => '$refresh',
    ];

    public function mount(Orders|OrdersFromOtherSeller $order)
    {
        $this->sellerId = User::getAuthUser()->id;
        $this->isOrderFromOtherSeller = $this->isOrderFromOtherSeller($order);
        $this->order = $order;
    }

    /* Handle Order prop updates */
    public function updatedOrder(Orders|OrdersFromOtherSeller|null $order)
    {
        if ($order !== null) {
            $this->isOrderFromOtherSeller = $this->isOrderFromOtherSeller($order);
            $this->order = $order;
        }
    }

    /*
     * Custom Helpers
     */
    public function resetComponent()
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

    /* 
    public function renderStuartModal($orderId)
    {
        $this->orderId = $orderId;
    }
    */

    public function renderOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function isOrderFromOtherSeller($order): bool
    {
        return $order instanceof OrdersFromOtherSeller;
    }

    public function getProductBelongsToType(Orders|OrdersFromOtherSeller $order): ?string
    {
        /* Orders model keeps product info on first order_item */
        if ($order instanceof Orders) {
            return $order->order_items[0]->product_belongs_to_type;
        }

        /* OrdersFromOtherSeller keeps product info on the order record */
        return $order->product_belongs_to_type;
    }

    public function getProductQty(Orders|OrdersFromOtherSeller $order): int
    {
        if ($order instanceof Orders) {
            return $order->order_items[0]->product_qty;
        }

        return $order->product_qty;
    }

    public function getProductCategoryId(Orders|OrdersFromOtherSeller $order): int
    {
        if ($order instanceof Orders) {
            return $order->order_items[0]->product->category_id;
        }

        /* OrdersFromOtherSeller has morph relation "product" */
        return $order->product->category_id;
    }

    public function getUniqueModalId(string $modalBaseId): string
    {
        return $modalBaseId . $this->order->id;
    }

    public function getProductPrice(Orders|OrdersFromOtherSeller|null $order = null): ?float
    {
        if ($order instanceof Orders) {
            /**
             * This function only works for "Custom Product Orders a.k.a ProductsByBuyer Orders" 
             * Hence, this kind of order always has only one order item 
             */
            return $order->order_items[0]->product_price;
        }

        return $order?->product_price;
    }

    public function renderCustomProductOrderModal($orderId)
    {
        $this->resetComponent();

        $this->selectedOrder = $this->isOrderFromOtherSeller ? OrdersFromOtherSeller::getById($orderId) : Orders::getById($orderId);

        $this->dispatch('show-modal', ['id' => $this->getUniqueModalId('acceptCustomProductOrderModal')]);
    }

    public function renderTrackGophrDeliveryModal($orderId)
    {
        try {
            $gophrDelivery = GophrDelivery::getByOrderId((new Orders)->getMorphClass(), $orderId, ['job_id']);

            $response = GophrDeliveryServices::getJob($gophrDelivery->job_id);
            if (isset($response->errors)) {
                $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('trackGophrDeliveryModal')]);

                logger()->error($response->errors);

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
            fn() => User::getActiveParentAndChildSellersByCity(User::getAuthUser()->city)
        );
    }

    // public function noNearBySellers($orderId)
    // {
    //     $this->orderId = $orderId;
    //     $this->dispatch('show-modal', ['id' => $this->getUniqueModalId('noOtherSellersModal')]);
    // }

    public function capturePayment($currentTotal = null)
    {
        $totalWeight = OrderServices::getTotalWeight($this->selectedOrder);

        $currentDeliveryCharges = OrderServices::getTotalDeliveryCharges(
            $this->selectedOrder->seller->lat,
            $this->selectedOrder->seller->lon,
            $this->selectedOrder->customer_lat,
            $this->selectedOrder->customer_lon,
            $totalWeight,
        );

        $currentTotal ??= $this->selectedOrder->current_total;

        $currentTotalAmount = round($currentTotal + $this->selectedOrder->service_charges + $currentDeliveryCharges);
        $initialTotalAmount = round($this->selectedOrder->initial_total + $this->selectedOrder->service_charges + $this->selectedOrder->delivery_charges);

        if ($currentTotalAmount <= $initialTotalAmount) {
            $response = StripeServices::capturePaymentIntent(
                $this->selectedOrder->payment_intent_id,
                StripeServices::calculateCharge($currentTotalAmount),
            );
            if (isset($response->error)) {
                throw new Exception($response->error->message);
            }
            /* Adding only total amount into seller's wallet without service charges & delivery charges */
            User::addIntoWallet($this->sellerId, $currentTotal);
        } else {
            throw new Exception('Your current order total should be equal to or less than the initial order total amount');
        }

        return $response;
    }

    public function prepareGophrJobArray(Orders|OrdersFromOtherSeller $order, string $parcelDescription): array
    {
        return GophrDeliveryServices::prepareJobArray(
            externalId: UUIDServices::generateUUID(),
            pickupAddress: $order->seller->full_address,
            pickupCity: $order->seller->city,
            pickupPostcode: $order->seller->postcode,
            pickupLat: (float) $order->seller->lat,
            pickupLon: (float) $order->seller->lon,
            pickupPersonName: $order->seller->name,
            pickupMobileNumber: $order->seller->business_phone,
            parcelExternalId: UUIDServices::generateUUID(),
            parcelReferenceNumber: UUIDServices::generateUUID(),
            parcelDescription: $parcelDescription,
            width: OrderServices::getTotalWidth($order),
            length: OrderServices::getTotalLength($order),
            height: OrderServices::getTotalHeight($order),
            weight: OrderServices::getTotalWeight($order),
            dropoffAddress: $order->address,
            dropoffCity: $order->city,
            dropoffPostcode: $order->postcode,
            dropoffLat: (float) $order->customer_lat,
            dropoffLon: (float) $order->customer_lon,
            dropoffPersonName: $order->customer_name,
            dropoffEmail: $order->buyer->email,
            dropoffMobileNumber: $order->phone_number,
        );
    }

    public function cancelGophrJob(): void
    {
        try {
            /* 1st check if the delivery job has been created? then cancel */
            if (isset($this->deliveryJob->data)) {
                GophrDeliveryServices::cancelJob(
                    $this->deliveryJob->data->job_id,
                    GophrCancellationReasonEnum::TECHNICAL_ISSUES
                );
            }
        } catch (Exception $error) {
            report($error);
            logger()->error(
                'Failed to cancel Gophr job #' . ($this->deliveryJob->data->job_id) . ' - ' . $error->getMessage()
            );
        }
    }
    /*
     * CRUD Methods
     */
    public function assignToGophrDriver()
    {
        try {
            /* Perform some operation */
            DB::beginTransaction();

            if ($this->isOrderFromOtherSeller) {
                $order = OrdersFromOtherSeller::getById($this->orderId);

                $updated = OrdersFromOtherSeller::updateOrderStatus($this->orderId, OrderStatusEnum::ON_THE_WAY);
            } else {
                $order = Orders::getById($this->orderId);

                $updated = Orders::updateOrderStatus($this->orderId, OrderStatusEnum::ON_THE_WAY);
            }

            $parcelDescription = $this->additionalParcelDescription ?? 'Please pickup your order ASAP';

            $this->deliveryJob = GophrDeliveryServices::createJob(
                $this->prepareGophrJobArray($order, $parcelDescription)
            );
            if (isset($this->deliveryJob->errors)) {
                throw new Exception(json_encode($this->deliveryJob->errors[0]->message));
            }

            GophrDelivery::add(
                (new Orders)->getMorphClass(),
                $this->orderId,
                $this->deliveryJob->data->job_id
            );

            DB::commit();
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('gophrModal')]);

            if ($updated && isset($this->deliveryJob->data)) {
                session()->flash('success', config('constants.DELIVERY_SUCCESS'));
            } else {
                session()->flash('error', config('constants.DELIVERY_FAILED'));
            }
        } catch (Exception $error) {
            DB::rollBack();

            $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('gophrModal')]);

            $this->cancelGophrJob();

            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    /** @deprecated */
    public function assignToStuartDriver()
    {
        try {
            /* Perform some operation */
            $stuartMessage = StuartDeliveryServices::createJobForLivewire(
                $this->orderId,
                $this->customOrderId
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('stuartModal')]);

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

            $moved = (new MoveOrderToOtherNearBySellersAction())->execute($this->selectedOrder, User::getAuthUser());
            /* Operation finished */
            sleep(1);

            if ($moved) {
                $this->redirectRoute('seller.orders');
                // session()->flash('success', config('constants.SENT_TO_OTHER_STORE_SUCCESS'));
            } else {
                // $this->dispatch(event: 'refreshThisComponent')->self();
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
                'max:' . $this->getProductPrice($this->selectedOrder),
                'min:1',
            ],
        ]);

        try {
            /* Perform some operation */
            DB::beginTransaction();

            if ($this->isOrderFromOtherSeller) {
                OrdersFromOtherSeller::isViewed($this->selectedOrder->id);

                OrdersFromOtherSeller::disableThisOrderForOthers(
                    parentOrderId: $this->selectedOrder->parent_order_id,
                    exceptSellerId: $this->sellerId,
                );

                $newOrderTotal = $this->priceBySeller * $this->getProductQty($this->selectedOrder);

                $updated = OrdersFromOtherSeller::updateInfo(
                    id: $this->selectedOrder->id,
                    currentTotal: $newOrderTotal,
                    orderStatus: OrderStatusEnum::ACCEPTED,
                );
            } else {
                Orders::isViewed($this->selectedOrder->id);

                $newOrderTotal = $this->priceBySeller * $this->getProductQty($this->selectedOrder);

                $updated = Orders::updateInfo(
                    id: $this->selectedOrder->id,
                    currentTotal: $newOrderTotal,
                    orderStatus: OrderStatusEnum::ACCEPTED,
                );
            }

            $response = $this->capturePayment($newOrderTotal);

            DB::commit();
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('acceptCustomProductOrderModal')]);

            if ($updated && $response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            DB::rollBack();

            $this->dispatch('close-modal', ['id' => $this->getUniqueModalId('acceptCustomProductOrderModal')]);

            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function generalOrderIsAccepted($orderId)
    {
        try {
            /* Perform some operation */
            DB::beginTransaction();

            if ($this->isOrderFromOtherSeller) {
                $this->selectedOrder = OrdersFromOtherSeller::isViewed($orderId);

                OrdersFromOtherSeller::updateOrderStatus($orderId, OrderStatusEnum::ACCEPTED);

                OrdersFromOtherSeller::disableThisOrderForOthers(
                    parentOrderId: $this->selectedOrder->parent_order_id,
                    exceptSellerId: $this->sellerId,
                );
            } else {
                $this->selectedOrder = Orders::isViewed($orderId);

                Orders::updateOrderStatus($orderId, OrderStatusEnum::ACCEPTED);
            }

            $response = $this->capturePayment();

            DB::commit();

            if ($this->selectedOrder->type == OrderTypeEnum::SELF_PICKUP->value) {
                /**
                 * Remove bugs related to "sendPickupYourOrderMail()"
                 */
                EmailServices::sendPickupYourOrderMail($this->selectedOrder);
            }
            /* Operation finished */
            sleep(1);

            if ($response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                $this->dispatch(event: 'refreshThisComponent')->self();
                $this->dispatch(event: 'callParentRenderMethod');
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            DB::rollBack();

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
            DB::beginTransaction();

            if ($this->isOrderFromOtherSeller) {
                $this->selectedOrder = OrdersFromOtherSeller::getById($orderId);

                $cancelled = OrdersFromOtherSeller::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);
            } else {
                $this->selectedOrder = Orders::getById($orderId);

                $cancelled = Orders::updateOrderStatus($orderId, OrderStatusEnum::CANCELLED);
            }

            $refunded = StripeServices::refundPaymentIntent($this->selectedOrder->payment_intent_id);
            if (isset($refunded->error)) {
                throw new Exception($refunded->error->message);
            }

            DB::commit();

            EmailServices::sendOrderHasBeenCancelledMail($this->selectedOrder);
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();

            if ($cancelled && $refunded->status === PaymentIntentStatusEnum::CANCELED->value) {
                session()->flash('success', config('constants.ORDER_CANCELLATION_SUCCESS'));
            } else {
                session()->flash('error', config('constants.ORDER_CANCELLATION_FAILED'));
            }
        } catch (Exception $error) {
            DB::rollBack();

            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.common.orders-header-livewire');
    }
}
