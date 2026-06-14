<?php

namespace Tests\Feature\Livewire\Company;

use App\Livewire\Company\OrderVanInventoryLivewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class OrderVanInventoryLivewireTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(OrderVanInventoryLivewire::class)->assertStatus(200);
    }
}
