<?php

namespace App\Http\Controllers;

use App\Services\UberDeliveryServices;
use Illuminate\Http\Request;

class UberDeliveryController extends Controller
{
    // protected $uber;

    // public function __construct(UberDeliveryServices $uber)
    // {
    //     $this->uber = $uber;
    // }

    // public function createDelivery(Request $request)
    // {
    //     $payload = [
    //         "pickup" => [
    //             "address" => "123 Main Street, City",
    //             "contact" => [
    //                 "first_name" => "John",
    //                 "last_name" => "Doe",
    //                 "phone" => "+1234567890",
    //                 "email" => "pickup@example.com"
    //             ]
    //         ],
    //         "dropoff" => [
    //             "address" => "456 Another Street, City",
    //             "contact" => [
    //                 "first_name" => "Jane",
    //                 "last_name" => "Smith",
    //                 "phone" => "+0987654321",
    //                 "email" => "dropoff@example.com"
    //             ]
    //         ],
    //         "items" => [
    //             [
    //                 "title" => "Pizza",
    //                 "quantity" => 1,
    //                 "price" => 15.00
    //             ]
    //         ],
    //         "external_id" => uniqid("delivery_")
    //     ];

    //     $result = $this->uber->createJob($payload);

    //     return response()->json($result);
    // }

    // public function trackDelivery($deliveryId)
    // {
    //     $status = $this->uber->getJob($deliveryId);
    //     return response()->json($status);
    // }
}
