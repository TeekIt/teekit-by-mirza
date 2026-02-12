<?php

namespace Tests\Feature;

use App\Mail\ProductByBuyerOrderDetailsToNearBySellersMail;
use App\Models\Orders;
use App\Services\EmailServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductByBuyerOrderDetailsToNearBySellersMailTest extends TestCase
{
    //  use RefreshDatabase;

    public function testEmailContainsOrderData(): void
    {
        Mail::fake();

        $order = Orders::factory()->create();

        EmailServices::sendProductByBuyerOrderDetailsToNearBySellersMail(
            ['seller@example.com'],
            $order
        );

        Mail::assertSent(ProductByBuyerOrderDetailsToNearBySellersMail::class);
    }
}
