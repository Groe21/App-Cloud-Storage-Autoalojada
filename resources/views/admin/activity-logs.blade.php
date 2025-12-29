@extends('layouts.app')

@section('title', 'Registro de Actividad - Cloud Storage')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-clock-history"></i> Registro de Actividad</h2>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @forelse($logs as $log)
                <div class="activity-log-item border-{{ $log->action_color }} mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="rounded-circle bg-{{ $log->action_color }} bg-opacity-10 p-3">
                                <i class="bi {{ $log->action_icon }} text-{{ $log->action_color }}" 
                                   style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $log->user->name ?? 'Sistema' }}
                                        <span class="badge bg-{{ $log->action_color }} ms-2">{{ $log->action }}</span>
                                    </h6>
                                    <p class="mb-1 text-muted">{{ $log->description }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $log->created_at->format('d/m/Y H:i:s') }} 
                                        ({{ $log->created_at->diffForHumans() }})
                                    </small>
                                    @if($log->ip_address)
                                        <small class="text-muted ms-3">
                                            <i class="bi bi-globe"></i> {{ $log->ip_address }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            
                            @if($log->metadata)
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#metadata-{{ $log->id }}">
                                        <i class="bi bi-info-circle"></i> Ver detalles
                                    </button>
                                    <div class="collapse mt-2" id="metadata-{{ $log->id }}">
                                        <div class="card card-body bg-light">
                                            <pre class="mb-0"><code>{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3 text-muted">No hay registros de actividad</h4>
                </div>
            @endforelse
            
            @if($logs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
