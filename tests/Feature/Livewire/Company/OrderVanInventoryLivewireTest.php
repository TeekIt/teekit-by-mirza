<?php

namespace Tests\Feature\Livewire\Company;

use App\Livewire\Company\OrderVanInventoryLivewire;
use App\Models\Products;
use App\Models\User;
use App\Models\Van;
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

        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
            'category_name' => 'Test Category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // 3. Create a real product so addToCart can look it up
        $product = Products::factory()->create(['category_id' => $categoryId]);

        // 4. Act as the company user, add the product to cart via the component,
        //    then call addDirectlyToVan — all within the same Livewire session.
        Livewire::actingAs($company)
            ->test(OrderVanInventoryLivewire::class)
            ->set('vanId', $van->id)
            ->call('addToCart', $product->id)    
            ->call('addDirectlyToVan')         
            ->assertSessionMissing('error')
            ->assertSessionHas('success', config('constants.PAY_AS_YOU_GO_ORDER_PLACED_SUCCESSFULLY'));
    }
}
