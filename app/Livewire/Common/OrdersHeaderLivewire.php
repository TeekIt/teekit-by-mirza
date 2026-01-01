<?php

namespace App\Livewire\Common;

use App\Actions\Orders\MoveOrderToOtherNearBySellersAction;
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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersHeaderLivewire extends Component
{
    use WithPagination;

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

    public $isOrderFromOtherSeller;

    public $order;

    public $sellerId;

    public $moveOrderToOtherNearBySellersAction;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshThisComponent' => '$refresh',
    ];

    public function mount(Orders|OrdersFromOtherSeller $order)
    {
        $this->sellerId = Auth::user()->id;
        $this->isOrderFromOtherSeller = $this->isOrderFromOtherSeller($order);
        $this->order = $order;
    }

    /* Handle Order prop updates */
    public function updatedOrder(Orders|OrdersFromOtherSeller $order)
    {
        $this->isOrderFromOtherSeller = $this->isOrderFromOtherSeller($order);
        $this->order = $order;
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

    /* public function renderStuartModal($orderId)
    {
        $this->orderId = $orderId;
    } */

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

    public function getProductPrice(null|Orders|OrdersFromOtherSeller $order = null): ?float
    {
        if ($order instanceof Orders) {
            return $order->order_items[0]->product_price;
        }

        return $order?->product_price;
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

    public function renderCustomProductOrderModal($orderId)
    {
        $this->resetComponent();

        $this->selectedOrder = $this->isOrderFromOtherSeller ? OrdersFromOtherSeller::getById($orderId) : Orders::getById($orderId);

        $this->dispatch('show-modal', ['id' => 'acceptCustomProductOrderModal']);
    }

    public function renderTrackGophrDeliveryModal($orderId)
    {
        try {
            $gophrDelivery = GophrDelivery::getByOrderId((new Orders)->getMorphClass(), $orderId, ['job_id']);

            $response = GophrDeliveryServices::getJob($gophrDelivery->job_id);
            if (isset($response->errors)) {
                $this->dispatch('close-modal', ['id' => 'trackGophrDeliveryModal']);

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

    public function getSellersOfSameCityAndCategory()
    {
        return Cache::remember(
            'getSellersOfSameCityAndCategory' . $this->sellerId,
            Carbon::now()->addDay(),
            fn() => User::getActiveAndBlockedParentAndChildSellersByCityAndCategory(
                auth()->user()->city,
                $this->getProductCategoryId($this->selectedOrder),
                $this->sellerId,
            )
        );
    }

    public function noNearBySellers($orderId)
    {
        $this->orderId = $orderId;
        $this->dispatch('show-modal', ['id' => 'noOtherSellersModal']);
    }

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

    /*
     * CRUD Methods
     */
    public function assignToGophrDriver()
    {
        try {
            /* Perform some operation */
            $order = Orders::getById($this->orderId);

            $parcelDescription = $this->additionalParcelDescription ?? 'Please pickup your order ASAP';

            $response = GophrDeliveryServices::createJob(
                $this->prepareGophrJobArray($order, $parcelDescription)
            );
            if (isset($response->errors)) {
                $this->dispatch('close-modal', ['id' => 'gophrModal']);

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
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->dispatch('close-modal', ['id' => 'gophrModal']);

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
            $stuartMessage = StuartDeliveryServices::createJobForLivewire(
                $this->orderId,
                $this->customOrderId
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch('close-modal', ['id' => 'stuartModal']);

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

            // $orderTotalPrice = $this->selectedOrder->order_items[0]->product_price * $this->selectedOrder->order_items[0]->product_qty;
            // /* Get sellers who belongs to the city of this store owner */
            // $sellersOfTheSameCityAndCategory = $this->getSellersOfSameCityAndCategory();
            // /* Get sellers who are nearby to the order placing buyer */
            // $nearbySellers = GoogleMapServices::getNearBySellers(
            //     $this->selectedOrder->customer_lat,
            //     $this->selectedOrder->customer_lon,
            //     $sellersOfTheSameCityAndCategory,
            //     $this->sellerId,
            //     nearByMiles: 3,
            // );

            // if (empty($nearbySellers)) {
            //     return $this->noNearBySellers($orderId);
            // }

            // /* Send this product to all nearby sellers */
            // foreach ($nearbySellers as $singleIndex) {
            //     OrdersFromOtherSeller::add(
            //         $this->selectedOrder->created_by_type,
            //         $this->selectedOrder->created_by_id,
            //         $singleIndex['id'],
            //         $this->selectedOrder->id,
            //         $this->selectedOrder->order_items[0]->product_belongs_to_type,
            //         $this->selectedOrder->order_items[0]->product_belongs_to_id,
            //         $this->selectedOrder->order_items[0]->product_price,
            //         $this->selectedOrder->order_items[0]->product_qty,
            //         $orderTotalPrice,
            //         (float) $this->selectedOrder->customer_lat ?? null,
            //         (float) $this->selectedOrder->customer_lon ?? null,
            //         $this->selectedOrder->customer_name,
            //         $this->order->country_code,
            //         $this->selectedOrder->phone_number,
            //         $this->selectedOrder->address,
            //         $this->selectedOrder->house_no,
            //         $this->selectedOrder->flat,
            //         $this->selectedOrder->country,
            //         $this->selectedOrder->state,
            //         $this->selectedOrder->city,
            //         $this->selectedOrder->postcode,
            //         $this->selectedOrder->payment_intent_id,
            //         $this->selectedOrder->driver_charges,
            //         $this->selectedOrder->delivery_charges,
            //         $this->selectedOrder->service_charges,
            //         $this->selectedOrder->device,
            //         $this->selectedOrder->type,
            //         $this->selectedOrder->description,
            //         $this->selectedOrder->payment_status,
            //         $this->selectedOrder->offloading,
            //         $this->selectedOrder->offloading_charges,
            //         now(),
            //         $this->selectedOrder->created_at,
            //     );
            // }

            // /* Remove the whole order in case of custom product order's */
            // $removed = Orders::remove($this->selectedOrder->id);

            // info('The current order has been sent to these nearby sellers', $nearbySellers);

            $removed = (new MoveOrderToOtherNearBySellersAction())->execute($this->selectedOrder, Auth::user());
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();

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
                'max:' . $this->getProductPrice($this->selectedOrder),
                'min:1',
            ],
        ]);

        try {
            /* Perform some operation */
            if ($this->isOrderFromOtherSeller) {
                OrdersFromOtherSeller::isViewed($this->selectedOrder->id);

                $newOrderTotal = $this->priceBySeller * $this->getProductQty($this->selectedOrder);

                $response = $this->capturePayment($newOrderTotal);

                $updated = OrdersFromOtherSeller::updateInfo(
                    id: $this->selectedOrder->id,
                    currentTotal: $newOrderTotal,
                    orderStatus: OrderStatusEnum::ACCEPTED,
                );

                OrdersFromOtherSeller::disableThisOrderForOthers(
                    parentOrderId: $this->selectedOrder->parent_order_id,
                    exceptSellerId: $this->sellerId,
                );
            } else {
                Orders::isViewed($this->selectedOrder->id);

                $newOrderTotal = $this->priceBySeller * $this->getProductQty($this->selectedOrder);

                $response = $this->capturePayment($newOrderTotal);

                $updated = Orders::updateInfo(
                    id: $this->selectedOrder->id,
                    currentTotal: $newOrderTotal,
                    orderStatus: OrderStatusEnum::ACCEPTED,
                );
            }
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->dispatch('close-modal', ['id' => 'acceptCustomProductOrderModal']);

            if ($updated && $response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            $this->dispatch('close-modal', ['id' => 'acceptCustomProductOrderModal']);

            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function generalOrderIsAccepted($orderId)
    {
        try {
            /* Perform some operation */
            $this->selectedOrder = Orders::isViewed($orderId);

            // $response = $this->capturePayment();

            if ($this->selectedOrder->type == OrderTypeEnum::SELF_PICKUP->value) {
                /**
                 * Remove bugs related to "sendPickupYourOrderMail()"
                 */
                EmailServices::sendPickupYourOrderMail($this->selectedOrder);
            }

            $updated = Orders::updateOrderStatus($orderId, OrderStatusEnum::ACCEPTED);
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();

            // if ($updated && $response?->status === PaymentIntentStatusEnum::SUCCEEDED->value) {
            //     session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            // } else {
            //     session()->flash('error', config('constants.UPDATION_FAILED'));
            // }

            if ($updated == 1) {
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
            $this->dispatch(event: 'refreshThisComponent')->self();

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
        return view('livewire.common.orders-header-livewire');
    }
}
