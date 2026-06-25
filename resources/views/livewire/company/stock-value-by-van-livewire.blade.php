<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-2">
    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-12">
                        <h4 class="py-4 my-1 text-site-primary">Stock Value by Van</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Optional: Summary stats row -->
            <div class="row mb-3">
                <div class="col-12">
                    <h5 class="text-site-primary">Total Stock Value: £{{ number_format($totalValue, 2) }}</h5>
                </div>
            </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title text-site-primary">Stock Value Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="stockValueChart" width="600" height="300"></canvas>
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
        var ctx = document.getElementById('stockValueChart').getContext('2d');
        var labels = {!! json_encode(array_column($stockByVan, 'van_name')) !!};
        var values = {!! json_encode(array_column($stockByVan, 'stock_value')) !!};
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Stock Value (£)',
                    data: values,
                    backgroundColor: '#3a4b83'
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