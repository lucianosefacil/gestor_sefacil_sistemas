<?php $__env->startSection('title', __('superadmin::lang.pricing')); ?>

<?php $__env->startSection('content'); ?>

<div class="container">
    <?php echo $__env->make('superadmin::layouts.partials.currency', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('layouts.partials.logo', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title text-center">Planos</h3>
            </div>

            <div class="box-body">
                <?php echo $__env->make('superadmin::subscription.partials.packages', ['action_type' => 'register'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#change_lang').change( function(){
            window.location = "<?php echo e(route('pricing'), false); ?>?lang=" + $(this).val();
        });
    })
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/Modules/Superadmin/Providers/../Resources/views/pricing/index.blade.php ENDPATH**/ ?>