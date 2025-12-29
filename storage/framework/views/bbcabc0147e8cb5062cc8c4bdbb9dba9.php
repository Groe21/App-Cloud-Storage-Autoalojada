<?php $__env->startSection('title', 'Mis Archivos - Cloud Storage'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <?php if(count($breadcrumbs) > 0): ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo e(route('dashboard')); ?>"><i class="bi bi-house-door"></i> Inicio</a>
                </li>
                <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('dashboard', ['folder' => $crumb['id']])); ?>">
                            <?php echo e($crumb['name']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </nav>
    <?php else: ?>
        <h2 class="mb-4"><i class="bi bi-folder2-open"></i> Mis Archivos</h2>
    <?php endif; ?>
    
    <!-- Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                <i class="bi bi-folder-plus"></i> Nueva Carpeta
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload"></i> Subir Archivo
            </button>
        </div>
        <div>
            <form action="<?php echo e(route('files.search')); ?>" method="GET" class="d-flex">
                <input type="search" name="q" class="form-control me-2" placeholder="Buscar archivos..." 
                       value="<?php echo e(request('q')); ?>" required>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>
    
    <div class="row">
        <!-- Folders -->
        <?php $__empty_1 = true; $__currentLoopData = $folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $folder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="folder-item" onclick="window.location='<?php echo e(route('dashboard', ['folder' => $folder->id])); ?>'">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-folder-fill text-warning folder-icon"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?php echo e($folder->name); ?></h6>
                            <small class="text-muted"><?php echo e($folder->files_count ?? 0); ?> archivos</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" 
                                    data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="<?php echo e(route('folders.destroy', $folder)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('¿Eliminar esta carpeta y todo su contenido?')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if($files->isEmpty()): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3 text-muted">No hay archivos ni carpetas</h4>
                        <p class="text-muted">Comienza subiendo tu primer archivo o creando una carpeta</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Files -->
        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="file-item">
                    <div class="d-flex align-items-start">
                        <i class="bi <?php echo e($file->icon); ?> file-icon text-primary"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo e(Str::limit($file->original_name, 30)); ?></h6>
                            <small class="text-muted d-block"><?php echo e($file->human_size); ?></small>
                            <small class="text-muted"><?php echo e($file->created_at->diffForHumans()); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('files.download', $file)); ?>">
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(route('files.destroy', $file)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('¿Eliminar este archivo?')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    
    <!-- Pagination -->
    <?php if($files->hasPages()): ?>
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($files->links()); ?>

        </div>
    <?php endif; ?>
</div>

<!-- New Folder Modal -->
<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-folder-plus"></i> Nueva Carpeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('folders.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <input type="hidden" name="parent_id" value="<?php echo e($folderId); ?>">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Nombre de la carpeta</label>
                        <input type="text" class="form-control" id="folderName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-folder-plus"></i> Crear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/emilio/Escritorio/desarrollador/App Cloud Storage Autoalojada/resources/views/dashboard.blade.php ENDPATH**/ ?>