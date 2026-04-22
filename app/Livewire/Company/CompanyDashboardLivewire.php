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

        $vans = Van::where('company_id', '=', $companyId)->get();
        $totalVans = $vans->count();

        $vanIds = $vans->pluck('id');
        $totalStock = VanProduct::whereIn('van_id', $vanIds)->sum('quantity');

        return view('livewire.company.company-dashboard-livewire', [
            'totalVans' => $totalVans,
            'totalStock' => $totalStock,
        ]);
    }
}
