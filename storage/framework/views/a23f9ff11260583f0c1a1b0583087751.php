<?php $__env->startSection('title', 'Registro de Actividad - Cloud Storage'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-clock-history"></i> Registro de Actividad</h2>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="activity-log-item border-<?php echo e($log->action_color); ?> mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="rounded-circle bg-<?php echo e($log->action_color); ?> bg-opacity-10 p-3">
                                <i class="bi <?php echo e($log->action_icon); ?> text-<?php echo e($log->action_color); ?>" 
                                   style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <?php echo e($log->user->name ?? 'Sistema'); ?>

                                        <span class="badge bg-<?php echo e($log->action_color); ?> ms-2"><?php echo e($log->action); ?></span>
                                    </h6>
                                    <p class="mb-1 text-muted"><?php echo e($log->description); ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo e($log->created_at->format('d/m/Y H:i:s')); ?> 
                                        (<?php echo e($log->created_at->diffForHumans()); ?>)
                                    </small>
                                    <?php if($log->ip_address): ?>
                                        <small class="text-muted ms-3">
                                            <i class="bi bi-globe"></i> <?php echo e($log->ip_address); ?>

                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if($log->metadata): ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#metadata-<?php echo e($log->id); ?>">
                                        <i class="bi bi-info-circle"></i> Ver detalles
                                    </button>
                                    <div class="collapse mt-2" id="metadata-<?php echo e($log->id); ?>">
                                        <div class="card card-body bg-light">
                                            <pre class="mb-0"><code><?php echo e(json_encode($log->metadata, JSON_PRETTY_PRINT)); ?></code></pre>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3 text-muted">No hay registros de actividad</h4>
                </div>
            <?php endif; ?>
            
            <?php if($logs->hasPages()): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($logs->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/emilio/Escritorio/desarrollador/App Cloud Storage Autoalojada/resources/views/admin/activity-logs.blade.php ENDPATH**/ ?>