@extends('layouts.app')

@section('title', 'Métricas del Servidor - Cloud Storage')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-graph-up"></i> Métricas del Servidor</h2>
    
    <!-- Current Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-cpu"></i> CPU y Memoria</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Uso de CPU</span>
                            <strong>{{ $currentMetrics->cpu_usage ?? 'N/A' }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $currentMetrics->cpu_usage ?? 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Uso de Memoria RAM</span>
                            <strong>{{ $currentMetrics->memory_usage_percent ?? 'N/A' }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                 style="width: {{ $currentMetrics->memory_usage_percent ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $currentMetrics->memory_used_human ?? 'N/A' }} de 
                            {{ $currentMetrics->memory_total_human ?? 'N/A' }}
                        </small>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Load Average 1m</small>
                                <h4 class="mb-0">{{ number_format($currentMetrics->load_average_1 ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Load Average 5m</small>
                                <h4 class="mb-0">{{ number_format($currentMetrics->load_average_5 ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Load Average 15m</small>
                                <h4 class="mb-0">{{ number_format($currentMetrics->load_average_15 ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-hdd"></i> Almacenamiento</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Uso del Disco</span>
                            <strong>{{ $currentMetrics->disk_usage_percent ?? 'N/A' }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $currentMetrics->disk_usage_percent ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $currentMetrics->disk_used_human ?? 'N/A' }} de 
                            {{ $currentMetrics->disk_total_human ?? 'N/A' }}
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-hdd text-primary" style="font-size: 2rem;"></i>
                                <h5 class="mt-2 mb-0">{{ $currentMetrics->disk_total_human ?? 'N/A' }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-hdd-fill text-success" style="font-size: 2rem;"></i>
                                <h5 class="mt-2 mb-0">{{ $currentMetrics->disk_free ? number_format($currentMetrics->disk_free / (1024**3), 2) . ' GB' : 'N/A' }}</h5>
                                <small class="text-muted">Disponible</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Última actualización: 
                            {{ $currentMetrics->recorded_at ? $currentMetrics->recorded_at->diffForHumans() : 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Historical Data -->
    @if($historicalMetrics->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Histórico (últimas 24 horas)</h5>
            </div>
            <div class="card-body">
                <canvas id="metricsChart" height="80"></canvas>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    @if($historicalMetrics->count() > 0)
    const ctx = document.getElementById('metricsChart').getContext('2d');
    const metricsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($historicalMetrics->pluck('recorded_at')->map(fn($date) => $date->format('H:i'))) !!},
            datasets: [
                {
                    label: 'CPU %',
                    data: {!! json_encode($historicalMetrics->pluck('cpu_usage')) !!},
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Memoria %',
                    data: {!! json_encode($historicalMetrics->pluck('memory_usage_percent')) !!},
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Disco %',
                    data: {!! json_encode($historicalMetrics->pluck('disk_usage_percent')) !!},
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection
