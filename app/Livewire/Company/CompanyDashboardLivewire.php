<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use App\Models\VanProduct;
use Livewire\Component;

class CompanyDashboardLivewire extends Component
{
    public function render()
    {
        $companyId = User::getAuthUser()->id;

        $totalVans = Van::getVansCountByCompanyId($companyId);

        
        $totalStock = Van::getTotalStockByCompanyId($companyId);

        $activeOperatives = Van::getActiveOperativesCountToday($companyId);

        $totalStockValue = Van::getTotalStockValue($companyId);

        $lowStockAlerts = Van::getLowStockAlertsCount($companyId);

        $pendingOrders = Van::getPendingOrdersCount($companyId);

        return view('livewire.company.company-dashboard-livewire', [
            'totalVans' => $totalVans,
            'totalStock' => $totalStock,
            'activeOperatives' => $activeOperatives,
            'totalStockValue' => $totalStockValue,
            'lowStockAlerts' => $lowStockAlerts,
            'pendingOrders' => $pendingOrders,
        ]);
    }
}
