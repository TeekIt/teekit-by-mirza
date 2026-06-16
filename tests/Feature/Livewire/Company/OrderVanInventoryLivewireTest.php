<?php

namespace Tests\Feature\Livewire\Company;

use App\Livewire\Company\OrderVanInventoryLivewire;
use App\Models\Categories;
use App\Models\Products;
use App\Models\User;
use App\Models\Van;
use App\Enums\OrderTypeEnum;
use Google\Service\Books\Category;
use Livewire\Livewire;
use Tests\TestCase;

class OrderVanInventoryLivewireTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        // 1. Create a company user via the existing factory state
        $company = User::factory()->company()->create();

        // 2. Create a van that belongs to this company
        $van = Van::factory()->forCompany($company)->create();

        // 3. Act as the company user and test the Livewire component
        Livewire::actingAs($company)
            ->test(OrderVanInventoryLivewire::class)
            ->assertViewHas('vans', function ($vans) use ($van) {
                return $vans->contains('id', $van->id);
            })
            ->assertViewHas('cartItems', [])
            ->assertViewHas('cartItemsCount', 0)
            ->assertViewHas('cartTotal', 0.0)
            ->assertStatus(config('constants.HTTP_OK'));
    }

    public function test_addDirectlyToVan_successfully(): void
    {
        // 1. Create a company user via the existing factory state
        $company = User::factory()->company()->create();

        // 2. Create a van that belongs to this company
        $van = Van::factory()->forCompany($company)->create();

        // 3. Create a real product so addToCart can look it up
        $product = Products::factory()->create();

        // 4. Act as the company user, add the product to cart via the component
        Livewire::actingAs($company)
            ->test(OrderVanInventoryLivewire::class)
            ->set('vanId', $van->id)
            ->call('addToCart', $product->id)    
            ->call('addDirectlyToVan')
            ->assertDontSeeText('Error!');
    }

    public function test_checkout_successfully(): void
    {
        // 1. Create a company user via the existing factory state
        $company = User::factory()->company()->create();

        // 2. Create a van that belongs to this company
        $van = Van::factory()->forCompany($company)->create();

        // 3. Create a real product so addToCart can look it up
        $product = Products::factory()->create();
     
        // 4. Act as the company user, add the product to cart via the component
        Livewire::actingAs($company)
            ->test(OrderVanInventoryLivewire::class)
            ->set([
                'nearBySellerId' => $product->seller_id,
                'vanId' => $van->id,
                'vanAddress' => fake()->address(),
                'vanCountry' => fake()->country(),
                'vanState' => fake()->state(),
                'vanCity' => fake()->city(),
                'vanPostcode' => fake()->postcode(),
                'vanLat' => fake()->latitude(),
                'vanLon' => fake()->longitude(),
            ])
            ->call('addToCart', $product->id)    
            ->call('checkout', OrderTypeEnum::COD)
            ->assertDontSeeText('Error!');
    }
}
