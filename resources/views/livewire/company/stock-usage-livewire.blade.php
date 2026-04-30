<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-2">
    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12">
                        <h4 class="py-4 my-1 text-site-primary">Stock Usage</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Filters Row -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-site-primary fw-semibold">Select Van</label>
                        <select wire:model="selectedVanId" class="form-select">
                            @foreach($vans as $van)
                            <option value="{{ $van['id'] }}">{{ $van['operative'] }} ({{ $van['user_name'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-site-primary fw-semibold">Period</label>
                        <select wire:model="selectedPeriod" class="form-select">
                            <option value="daily">Daily (Last 30 Days)</option>
                            <option value="weekly">Weekly (Last 12 Weeks)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-site-primary fw-semibold">Total Usage Value</label>
                        <h4 class="text-success">£{{ number_format($totalUsageValue, 2) }}</h4>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title text-site-primary">
                                    Stock Usage - {{ $vanName }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="stockUsageChart" width="600" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Table -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title text-site-primary">Usage Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Total Quantity</th>
                                                <th>Total Value (£)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice(array_reverse($usageData), 0, 10) as $usage)
                                            <tr>
                                                <td>{{ $usage['date'] }}</td>
                                                <td>{{ $usage['total_quantity'] }}</td>
                                                <td>£{{ number_format($usage['total_value'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var canvas = document.getElementById('stockUsageChart');
        if (!canvas) return;
        
        var ctx = canvas.getContext('2d');
        
        var labels = {!! json_encode(array_column(array_reverse($usageData), 'date')) !!};
        var values = {!! json_encode(array_column(array_reverse($usageData), 'total_value')) !!};
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Usage Value (£)',
                    data: values,
                    borderColor: '#3a4b83',
                    backgroundColor: 'rgba(58, 75, 131, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '£' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }, 500);
});
</script>