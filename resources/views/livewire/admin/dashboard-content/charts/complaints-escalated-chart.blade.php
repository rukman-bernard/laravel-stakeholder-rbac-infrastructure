<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Escalated Complaints Over Time</h3>
    </div>
    <div class="card-body">
        <canvas id="escalatedComplaintsChart" height="250"></canvas>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels);
        const data = @json($escalatedCounts);

        const ctx = document.getElementById('escalatedComplaintsChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Escalated Complaints',
                    data: data,
                    // borderColor: 'rgba(221,75,57,1)',
                    // backgroundColor: 'rgba(221,75,57,0.2)',
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Complaints'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time Period'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Escalated: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
