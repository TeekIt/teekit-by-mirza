<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use Livewire\Component;
use App\Models\VanInventoryOrder;
use App\Enums\OrderStatusEnum;
use Illuminate\View\View;

class CompanyDashboardLivewire extends Component
{
    public int $companyId;

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->companyId = User::getAuthUser()->id;
    }

    public function render(): View
    {
        $totalVans = Van::getVansCountByCompanyId($this->companyId);
        $totalStock = Van::getTotalStockByCompanyId($this->companyId);
        $activeOperatives = Van::getActiveOperativesCount($this->companyId, now()->toDateString());
        $totalStockValue = Van::getTotalStockValue($this->companyId);
        $lowStockAlerts = Van::getLowStockAlertsCount($this->companyId);
        $pendingOrders = VanInventoryOrder::getOrdersCount($this->companyId, OrderStatusEnum::PENDING);

        return view('livewire.company.company-dashboard-livewire', compact(
            'totalVans',
            'totalStock',
            'activeOperatives',
            'totalStockValue',
            'lowStockAlerts',
            'pendingOrders'
        ));
    }
}
