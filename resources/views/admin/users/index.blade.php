@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Cloud Storage')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Gestión de Usuarios</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Almacenamiento</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 35px; height: 35px;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <strong>{{ $user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger">Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Usuario</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px; min-width: 150px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $user->storage_usage_percentage }}%"
                                             aria-valuenow="{{ $user->storage_usage_percentage }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ number_format($user->storage_usage_percentage, 1) }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ number_format($user->storageQuota->used_gb, 2) }} / 
                                        {{ number_format($user->storageQuota->quota_gb, 2) }} GB
                                    </small>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"
                                                        onclick="return confirm('¿Eliminar este usuario?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
