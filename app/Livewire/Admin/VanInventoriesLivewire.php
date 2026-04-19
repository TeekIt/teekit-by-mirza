<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Collection;
use Livewire\Component;

class VanInventoriesLivewire extends Component
{
    public string $search = '';

    public ?int $inventoryId = null;

    public string $productName = '';

    public string $price = '';

    public string $quantity = '';

    public string $threshold = '';

    public int $vanId = 0;

    protected function rules()
    {
        return [
            
        ];
    }

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->vanId = request()->query('vanId');
    }

    /**
     * Reset to the first page in future if pagination is introduced.
     */
    public function updatingSearch(): void
    {
        // Intentionally left blank for now. Kept to mirror other admin components.
    }

    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'inventoryId',
            'productName',
            'price',
            'quantity',
            'threshold',
        ]);
    }

    public function renderEditVanInventoryModal(int $id): void
    {
        $allInventories = $this->getDummyInventories();
        $inventory = $allInventories->firstWhere('id', $id);

        if ($inventory) {
            $this->inventoryId = $inventory['id'];
            $this->productName = $inventory['name'];
            $this->price = $inventory['price'];
            $this->quantity = $inventory['qty'];
            $this->threshold = $inventory['threshold'];
        }
    }

    public function addVanInventory(): void
    {
        // Placeholder for add inventory logic
        // TODO: Implement database insertion when Inventory model is created
        $this->resetComponent();
    }

    protected function getDummyInventories(): Collection
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Power Drill Pro 20V',
                'price' => '£89.99',
                'qty' => 15,
                'threshold' => 5,
            ],
            [
                'id' => 2,
                'name' => 'Cordless Impact Driver',
                'price' => '£65.50',
                'qty' => 8,
                'threshold' => 3,
            ],
        ]);
    }

    public function render()
    {
        $searchValue = trim(mb_strtolower($this->search));

        $data = $this->getDummyInventories()->filter(function (array $inventory) use ($searchValue): bool {
            if ($searchValue === '') {
                return true;
            }

            return str_contains(mb_strtolower($inventory['name']), $searchValue)
                || str_contains(mb_strtolower($inventory['price']), $searchValue);
        })->values();

        return view('livewire.admin.van-inventories-livewire', compact('data'));
    }
}
