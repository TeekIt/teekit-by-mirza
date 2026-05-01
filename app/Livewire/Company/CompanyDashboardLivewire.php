<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use App\Models\VanProduct;
use Livewire\Component;
use App\Models\VanInventoryOrder;
use App\Enums\OrderStatusEnum;

class CompanyDashboardLivewire extends Component
{
    public $companyId;

    public function mount()
    {
        $this->companyId = User::getAuthUser()->id;
    }

    public function render()
    {

<<<<<<< HEAD
        $totalVans = Van::getVansCountByCompanyId($companyId);
=======
        $totalVans = Van::getVansCountByCompanyId($this->companyId);

>>>>>>> 68e97ca (Worked on the PR points)
        
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
