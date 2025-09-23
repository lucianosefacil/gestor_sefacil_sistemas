<?php if(!empty($receipt_details->repair_checklist_label) || !empty($receipt_details->checked_repair_checklist)): ?>
	<div class="col-xs-12">
		<br>
		<?php if(!empty($receipt_details->repair_checklist_label)): ?>
			<b <?php if($receipt_details->design != 'classic'): ?> class="color-555" <?php endif; ?>>
				<?php echo $receipt_details->repair_checklist_label; ?>

			</b>
		<?php endif; ?> <br>
		<?php if(!empty($receipt_details->repair_checklist)): ?>
			<?php
                $checked_repair_checklist = json_decode($receipt_details->checked_repair_checklist, true);
            ?>
		<?php endif; ?>
		<div class="row">
            <?php $__currentLoopData = $receipt_details->repair_checklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-xs-4">
                    <?php if($checked_repair_checklist[$check] == 'yes'): ?>
                        <i class="fas fa-check-square text-success"></i>
                    <?php elseif($checked_repair_checklist[$check] == 'no'): ?>
                    	<i class="fas fa-window-close text-danger"></i>
                    <?php endif; ?>
                    <span <?php if($receipt_details->design != 'classic'): ?> class="color-555" <?php endif; ?>>
                    	<?php echo e($check, false); ?>

                    </span>
                    <br>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
	</div>
<?php endif; ?>

<?php if(!empty($receipt_details->defects_label) || !empty($receipt_details->repair_defects)): ?>
	<div class="col-xs-12">
		<br>
		<p <?php if($receipt_details->design != 'classic'): ?> class="color-555" <?php endif; ?>>
			<?php if(!empty($receipt_details->defects_label)): ?>
				<strong><?php echo $receipt_details->defects_label; ?></strong><br>
			<?php endif; ?>
			<?php echo e($receipt_details->repair_defects, false); ?>

		</p>
	</div>
<?php endif; ?>
<!-- /.col --><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/sale_pos/receipts/partial/common_repair_invoice.blade.php ENDPATH**/ ?>