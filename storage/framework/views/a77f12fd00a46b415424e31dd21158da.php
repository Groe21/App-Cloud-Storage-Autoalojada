<?php $__env->startSection('title', 'Gestión de Usuarios - Cloud Storage'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Gestión de Usuarios</h2>
        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary">
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
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 35px; height: 35px;">
                                            <?php echo e(substr($user->name, 0, 1)); ?>

                                        </div>
                                        <strong><?php echo e($user->name); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo e($user->email); ?></td>
                                <td>
                                    <?php if($user->isAdmin()): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Usuario</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px; min-width: 150px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo e($user->storage_usage_percentage); ?>%"
                                             aria-valuenow="<?php echo e($user->storage_usage_percentage); ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo e(number_format($user->storage_usage_percentage, 1)); ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(number_format($user->storageQuota->used_gb, 2)); ?> / 
                                        <?php echo e(number_format($user->storageQuota->quota_gb, 2)); ?> GB
                                    </small>
                                </td>
                                <td>
                                    <?php if($user->is_active): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('admin.users.edit', $user)); ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if($user->id !== auth()->id()): ?>
                                            <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" 
                                                  method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger"
                                                        onclick="return confirm('¿Eliminar este usuario?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($users->hasPages()): ?>
                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($users->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/emilio/Escritorio/desarrollador/App Cloud Storage Autoalojada/resources/views/admin/users/index.blade.php ENDPATH**/ ?>