@extends('layouts.app')

@section('title', 'Panel de Administración - Cloud Storage')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Panel de Administración</h2>
    
    <!-- Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-metric border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Usuarios</h6>
                            <h3 class="mb-0">{{ $totalUsers }}</h3>
                            <small class="text-success">{{ $activeUsers }} activos</small>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card card-metric border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Almacenamiento Usado</h6>
                            <h3 class="mb-0">{{ number_format($totalStorageUsed / (1024**3), 2) }} GB</h3>
                            <small class="text-muted">de {{ number_format($totalStorageQuota / (1024**3), 2) }} GB</small>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
                            <i class="bi bi-hdd"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card card-metric border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">CPU</h6>
                            <h3 class="mb-0">{{ $metrics->cpu_usage ?? 'N/A' }}%</h3>
                            <small class="text-muted">Uso del procesador</small>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-cpu"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card card-metric border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Memoria RAM</h6>
                            <h3 class="mb-0">{{ $metrics->memory_usage_percent ?? 'N/A' }}%</h3>
                            <small class="text-muted">{{ $metrics->memory_used_human ?? 'N/A' }}</small>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem;">
                            <i class="bi bi-memory"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Server Status -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-server"></i> Estado del Servidor</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Disco</span>
                            <span>{{ $metrics->disk_usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $metrics->disk_usage_percent ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $metrics->disk_used_human ?? 'N/A' }} de {{ $metrics->disk_total_human ?? 'N/A' }}
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Memoria</span>
                            <span>{{ $metrics->memory_usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                 style="width: {{ $metrics->memory_usage_percent ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $metrics->memory_used_human ?? 'N/A' }} de {{ $metrics->memory_total_human ?? 'N/A' }}
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">1 min</small>
                            <strong>{{ number_format($metrics->load_average_1 ?? 0, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">5 min</small>
                            <strong>{{ number_format($metrics->load_average_5 ?? 0, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">15 min</small>
                            <strong>{{ number_format($metrics->load_average_15 ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Última actualización: 
                            {{ $metrics->recorded_at ? $metrics->recorded_at->diffForHumans() : 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    @forelse($recentActivity as $activity)
                        <div class="activity-log-item border-{{ $activity->action_color }}">
                            <div class="d-flex align-items-start">
                                <i class="bi {{ $activity->action_icon }} text-{{ $activity->action_color }} me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>{{ $activity->user->name ?? 'Sistema' }}</strong>
                                    <p class="mb-0 text-muted small">{{ $activity->description }}</p>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No hay actividad reciente</p>
                    @endforelse
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm btn-outline-primary">
                            Ver todo el registro
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-plus"></i><br>
                                <span>Nuevo Usuario</span>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-people"></i><br>
                                <span>Gestionar Usuarios</span>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.metrics') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-graph-up"></i><br>
                                <span>Ver Métricas</span>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-clock-history"></i><br>
                                <span>Registro de Actividad</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
