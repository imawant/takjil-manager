@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Persebaran Donasi</h1>
</div>

<div class="auth-card">
    <canvas class="donationChart" id="donationChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chart;

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('donationChart').getContext('2d');
        const data = @json($data);
        const avgNasi = {{ $avgNasi }};
        const avgSnack = {{ $avgSnack }};

        const labels = data.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });

        const nasiData = data.map(item => item.total_nasi);
        const snackData = data.map(item => item.total_snack);
        
        // Create array of average values matching the length of data
        const avgNasiData = new Array(data.length).fill(avgNasi);
        const avgSnackData = new Array(data.length).fill(avgSnack);

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nasi',
                        data: nasiData,
                        borderColor: '#0F766E',
                        backgroundColor: '#0F766E',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        label: 'Snack',
                        data: snackData,
                        borderColor: '#0EA5E9',
                        backgroundColor: '#0EA5E9',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 3
                    },
                    {
                        label: 'Rata-rata Nasi',
                        data: avgNasiData,
                        type: 'line',
                        borderColor: '#134E4A', // Darker teal
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false,
                        hidden: false, // Initially visible
                        order: 0
                    },
                    {
                        label: 'Rata-rata Snack',
                        data: avgSnackData,
                        type: 'line',
                        borderColor: '#0369A1', // Darker blue
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false,
                        hidden: false, // Initially visible
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                barPercentage: 0.9,
                categoryPercentage: 0.9,
                plugins: {
                    legend: {
                        position: 'top',
                        onClick: function(e, legendItem, legend) {
                            const index = legendItem.datasetIndex;
                            const ci = legend.chart;
                            
                            // Default toggle behavior
                            if (ci.isDatasetVisible(index)) {
                                ci.hide(index);
                                legendItem.hidden = true;
                            } else {
                                ci.show(index);
                                legendItem.hidden = false;
                            }
                            
                            // Sync logic
                            // 0: Nasi, 1: Snack, 2: Avg Nasi, 3: Avg Snack
                            if (index === 0) { // If Nasi toggled
                                if (ci.isDatasetVisible(0)) {
                                    ci.show(2); // Show Avg Nasi
                                } else {
                                    ci.hide(2); // Hide Avg Nasi
                                }
                            } else if (index === 1) { // If Snack toggled
                                if (ci.isDatasetVisible(1)) {
                                    ci.show(3); // Show Avg Snack
                                } else {
                                    ci.hide(3); // Hide Avg Snack
                                }
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += Math.round(context.parsed.y * 10) / 10; // Round to 1 decimal
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Box'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
