
@section('content_header_title', $header_title)
@section('content_header_subtitle', $subtitle)


    <div class="container-fluid">
        <div class="row chart-card-equal-height">
            <div class="col-md-6">
                @livewire('admin.dashboard-content.charts.programme-student-stats')
            </div>
            <div class="col-md-6">
                @livewire('admin.dashboard-content.charts.complaint-resolution-chart')
            </div>
        </div>

        <div class="row chart-card-equal-height mt-3">
            
            <div class="col-md-6">
                @livewire('admin.dashboard-content.charts.complaints-escalated-chart')
            </div>
            <div class="col-md-6">
                @livewire('admin.dashboard-content.charts.student-account-activity-chart')
            </div>
        </div>
    </div>


@push('css')
    <style>
        
        .chart-card-equal-height .card-body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
        }
    </style>
@endpush