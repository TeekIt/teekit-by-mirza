<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use Illuminate\View\View;
use Livewire\Component;

class StockUsageLivewire extends Component
{
    public array $vans = [];
    public int $selectedVanId = 0;
    public string $selectedPeriod = 'daily';
    public array $usageData = [];
    public float $totalUsageValue = 0.0;
    public string $vanName = '';

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->vans = Van::getByCompanyId(
            companyId: User::getAuthUser()->id,
            columns: ['id', 'user_name', 'operative', 'number_plate']
        )->toArray();

        if (!empty($this->vans)) {
            $this->selectedVanId = $this->vans[0]['id'];
            $this->loadUsageData();
        }
    }

    public function updatedSelectedVanId(): void
    {
        $this->loadUsageData();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->loadUsageData();
    }

    private function loadUsageData(): void
    {
        if (empty($this->selectedVanId)) {
            return;
        }

        $this->usageData = Van::getStockUsageByVan(
            vanId: $this->selectedVanId,
            period: $this->selectedPeriod
        )->toArray();

        $this->totalUsageValue = Van::getTotalUsageValue($this->selectedVanId);

        /* Get van name for display */
        $van = collect($this->vans)->firstWhere('id', $this->selectedVanId);
        $this->vanName = $van ? $van['operative'] . ' (' . $van['user_name'] . ')' : '';
        $this->dispatch('usageDataUpdated', ['usageData' => $this->usageData]);
    }

    public function render(): View
    {
        return view('livewire.company.stock-usage-livewire');
    }
}
