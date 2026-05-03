<?php

namespace App\Livewire\Company;

use App\Models\User;
use App\Models\Van;
use Illuminate\View\View;
use Livewire\Component;

class StockValueByVanLivewire extends Component
{
    public array $stockByVan = [];
    public int $totalValue = 0;

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $companyId = User::getAuthUser()->id;

        $this->stockByVan = Van::getStockValueByVan($companyId)->toArray();
        $this->totalValue = collect($this->stockByVan)->sum('stock_value');
    }

    public function render(): View
    {
        return view('livewire.company.stock-value-by-van-livewire');
    }

    public function scripts()
    {
        return <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var canvas = document.getElementById('stockValueChart');
        if (canvas && typeof Chart !== 'undefined') {
            var ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {$this->getChartLabels()},
                    datasets: [{
                        label: 'Stock Value (£)',
                        data: {$this->getChartValues()},
                        backgroundColor: '#3a4b83'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }, 500);
});

function getChartLabels() {
    return {$this->getChartLabels()};
}

function getChartValues() {
    return {$this->getChartValues()};
}
</script>
HTML;
    }

    private function getChartLabels(): string
    {
        $labels = array_column($this->stockByVan, 'van_name');
        return json_encode($labels);
    }

    private function getChartValues(): string
    {
        $values = array_column($this->stockByVan, 'stock_value');
        return json_encode($values);
    }
}
