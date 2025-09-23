<div class="pos-tab-content">
	<div class="row">
    <?php if(!empty($modules)): ?>
    <h4><?php echo app('translator')->get('lang_v1.enable_disable_modules'); ?></h4>
    <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(!in_array($v['name'], $not_in_package)): ?>
    <div class="col-sm-4">
      <div class="form-group">
        <div class="checkbox">
          <br>
          <label>
            <?php echo Form::checkbox('enabled_modules[]', $k,  in_array($k, $enabled_modules), 
            ['class' => 'input-icheck']); ?> <?php echo e($v['name'], false); ?>

          </label>
          <?php if(!empty($v['tooltip'])): ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . $v['tooltip'] . '" data-html="true" data-trigger="hover"></i>';
            }
            ?> <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
  </div>
  <div class="row">

    <h4>Módulos não inclusos neste plano</h4>
    <?php $__currentLoopData = $not_in_package; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-sm-4">
      <div class="form-group">
        <div class="">
          <br>
          <label>
            <?php echo e($v, false); ?>

          </label>
        </div>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/business/partials/settings_modules.blade.php ENDPATH**/ ?>