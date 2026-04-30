<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use Livewire\Component;

class StockUsageLivewire extends Component
{
    public $vans = [];
    public $selectedVanId = '';
    public $selectedPeriod = 'daily';
    public $usageData = [];
    public $totalUsageValue = 0;
    public $vanName = '';

    public function mount()
    {
        $companyId = User::getAuthUser()->id;
        $this->vans = Van::getVansForCompany($companyId)->toArray();
        
        if (!empty($this->vans)) {
            $this->selectedVanId = $this->vans[0]['id'];
            $this->loadUsageData();
        }
    }

    public function updatedSelectedVanId()
    {
        $this->loadUsageData();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadUsageData();
    }

    private function loadUsageData()
    {
        if (empty($this->selectedVanId)) {
            return;
        }

        $this->usageData = Van::getStockUsageByVan(
            (int) $this->selectedVanId, 
            $this->selectedPeriod
        )->toArray();

        $this->totalUsageValue = Van::getTotalUsageValue((int) $this->selectedVanId);
        
        // Get van name for display
        $van = collect($this->vans)->firstWhere('id', $this->selectedVanId);
        $this->vanName = $van ? $van['operative'] . ' (' . $van['user_name'] . ')' : '';
    }

    public function render()
    {
        return view('livewire.company.stock-usage-livewire');
    }
}