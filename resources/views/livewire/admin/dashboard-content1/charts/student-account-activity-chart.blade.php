<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Student Account Activity</h3>
    </div>
    <div class="card-body">
        <canvas id="studentActivityChart" height="250"></canvas>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('studentActivityChart').getContext('2d');

        const labels = @json($labels);
        const added = @json($added);
        const updated = @json($updated);
        const deleted = @json($deleted);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Added',
                        data: added,
                        // backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        stack: 'actions'
                    },
                    {
                        label: 'Updated',
                        data: updated,
                        // backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        stack: 'actions'
                    },
                    {
                        label: 'Deleted',
                        data: deleted,
                        // backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        stack: 'actions'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Student Count'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
