<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cloud Storage')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #dee2e6;
            padding: 1rem 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar-brand {
            padding: 0 1rem 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            color: #495057;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .sidebar-nav-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        
        .sidebar-nav-item.active {
            background-color: #e7f1ff;
            color: #0d6efd;
            border-right: 3px solid #0d6efd;
        }
        
        .sidebar-nav-item i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .navbar-custom {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }
        
        .storage-progress {
            margin: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }
        
        .file-item, .folder-item {
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .file-item:hover, .folder-item:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }
        
        .file-icon, .folder-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        
        .btn-upload {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            z-index: 999;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .btn-upload {
                bottom: 1rem;
                right: 1rem;
            }
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .card-metric {
            border-left: 3px solid #0d6efd;
        }
        
        .activity-log-item {
            padding: 0.75rem;
            border-left: 3px solid #dee2e6;
            margin-bottom: 0.5rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4 class="mb-0"><i class="bi bi-cloud"></i> Cloud Storage</h4>
        </div>
        
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door"></i>
                        <span>Mis Archivos</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-nav-item" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i>
                        <span>Subir Archivo</span>
                    </a>
                </li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="mt-3 px-3">
                            <small class="text-muted text-uppercase">Administración</small>
                        </li>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users') }}" class="sidebar-nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <i class="bi bi-people"></i>
                                <span>Usuarios</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.metrics') }}" class="sidebar-nav-item {{ request()->routeIs('admin.metrics') ? 'active' : '' }}">
                                <i class="bi bi-graph-up"></i>
                                <span>Métricas</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.activity-logs') }}" class="sidebar-nav-item {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i>
                                <span>Registro</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </nav>
        
        <!-- Storage Info -->
        @auth
            <div class="storage-progress">
                <small class="text-muted d-block mb-2">Almacenamiento</small>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: {{ auth()->user()->storage_usage_percentage }}%" 
                         aria-valuenow="{{ auth()->user()->storage_usage_percentage }}" 
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <small class="text-muted">
                    {{ number_format(auth()->user()->storageQuota->used_gb, 2) }} GB de 
                    {{ number_format(auth()->user()->storageQuota->quota_gb, 2) }} GB
                </small>
            </div>
        @endauth
        
        <!-- User Info -->
        @auth
            <div class="mt-auto p-3 border-top">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <strong class="d-block">{{ auth()->user()->name }}</strong>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        @endauth
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Mobile Menu Toggle -->
        <button class="btn btn-primary d-md-none mb-3" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload"></i> Subir Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="folder_id" value="{{ request('folder') }}">
                        <div class="mb-3">
                            <label for="file" class="form-label">Seleccionar archivo</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="form-text">
                                Tamaño máximo: {{ round(config('storage.max_file_size') / (1024 * 1024), 2) }} MB
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Subir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>
