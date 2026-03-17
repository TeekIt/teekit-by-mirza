<?php

namespace App\Livewire\Admin;

use Livewire\Component;

namespace App\Livewire\Admin;

use App\Models\Products;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SearchVanInventoriesLivewire extends Component
{
    use WithPagination;

    public string $vanLocation = '';

    public string $nearBySellerId = '';

    public string $productSearch = '';

    public bool $showInventoryGrid = false;

    protected $paginationTheme = 'bootstrap';

    public function updatedVanLocation(): void
    {
        $this->resetSearchResults();
    }

    public function updatedNearBySellerId(): void
    {
        $this->resetSearchResults();
    }

    public function search(): void
    {
        $this->validate([
            'vanLocation' => 'required|string',
            'nearBySellerId' => 'required|integer|exists:users,id',
        ]);

        $this->resetPage();
        $this->showInventoryGrid = true;
    }

    protected function resetSearchResults(): void
    {
        $this->showInventoryGrid = false;
        $this->resetPage();
    }

    public function render(): View
    {
        $sellers = User::whereRoleIsParentOrChildSeller()
            ->select(User::getSellerCommonColumns())
            ->get();

        $products = null;

        // if ($this->showInventoryGrid && trim($this->vanLocation) !== '' && $this->nearBySellerId !== '') {
        //     $products = Products::getParentSellerProductsForView((int) $this->nearBySellerId, orderBy: 'desc');
        // }

        $products = Products::getParentOrChildSellerProductsForView(
            (int) $this->nearBySellerId,
            search: $this->productSearch,
            orderBy: 'desc'
        );

        return view('livewire.admin.search-van-inventories-livewire', compact('sellers', 'products'));
    }
}
