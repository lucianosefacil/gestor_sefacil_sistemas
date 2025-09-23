<?php $__env->startSection('title', __('home.home')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header content-header-custom">
    <!-- <h1><?php echo e(__('home.welcome_message', ['name' => Session::get('user.first_name')]), false); ?>

    </h1> -->
</section>
<?php if(auth()->user()->can('dashboard.data')): ?>
<!-- Main content -->
<section class="content content-custom no-print">
  <br>
	<div class="row">
    <div class="col-md-4 col-xs-12">
      <?php if(count($all_locations) > 1): ?>
        <?php echo Form::select('dashboard_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'dashboard_location']); ?>

      <?php endif; ?>
    </div>
		<div class="col-md-8 col-xs-12">
			<div class="btn-group pull-right" data-toggle="buttons">
				<label class="btn btn-info active">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e(date('Y-m-d'), false); ?>" 
    				data-end="<?php echo e(date('Y-m-d'), false); ?>"
    				checked> <?php echo e('Hoje', false); ?>

  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e($date_filters['this_week']['start'], false); ?>" 
    				data-end="<?php echo e($date_filters['this_week']['end'], false); ?>"
    				> Esta Semana
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e($date_filters['this_month']['start'], false); ?>" 
    				data-end="<?php echo e($date_filters['this_month']['end'], false); ?>"
    				> Este Mês
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter" 
    				data-start="<?php echo e($date_filters['this_fy']['start'], false); ?>" 
    				data-end="<?php echo e($date_filters['this_fy']['end'], false); ?>" 
    				> Este Ano
  				</label>
            </div>
		</div>
	</div>
	<br>
	<div class="row row-custom">
    	<div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

	        <div class="info-box-content">
	          <span class="info-box-text"><?php echo e(__('home.total_purchase'), false); ?></span>
	          <span class="info-box-number total_purchase"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

	        <div class="info-box-content">
	          <span class="info-box-text"><?php echo e(__('home.total_sell'), false); ?></span>
	          <span class="info-box-number total_sell"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-yellow">
	        	<i class="fa fa-dollar"></i>
				<i class="fa fa-exclamation"></i>
	        </span>

          <div class="info-box-content">
	          <span class="info-box-text"><?php echo e(__('Contas a Receber'), false); ?></span>
	          <span class="info-box-number open_revenues"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->

	    <!-- fix for small devices only -->
	    <!-- <div class="clearfix visible-sm-block"></div> -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-yellow">
	        	<i class="ion ion-ios-paper-outline"></i>
	        	<i class="fa fa-exclamation"></i>
	        </span>

          <div class="info-box-content">
	          <span class="info-box-text"><?php echo e(__('Contas a Pagar'), false); ?></span>
	          <span class="info-box-number open_expenses"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
  	</div>

  	

    <?php if(!empty($widgets['after_sale_purchase_totals'])): ?>
      <?php $__currentLoopData = $widgets['after_sale_purchase_totals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $widget; ?>

      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    <?php if(!empty($all_locations)): ?>
  	<!-- sales chart start -->
  	<div class="row">
  		<div class="col-sm-12">
            <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_last_30_days')]); ?>
              <?php echo $sells_chart_1->container(); ?>

            <?php echo $__env->renderComponent(); ?>
  		</div>
  	</div>
    <?php endif; ?>
    <?php if(!empty($widgets['after_sales_last_30_days'])): ?>
      <?php $__currentLoopData = $widgets['after_sales_last_30_days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $widget; ?>

      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    <?php if(!empty($all_locations)): ?>
  	<div class="row">
  		<div class="col-sm-12">
            <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')]); ?>
              <?php echo $sells_chart_2->container(); ?>

            <?php echo $__env->renderComponent(); ?>
  		</div>
  	</div>
    <?php endif; ?>
  	<!-- sales chart end -->
    <?php if(!empty($widgets['after_sales_current_fy'])): ?>
      <?php $__currentLoopData = $widgets['after_sales_current_fy']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $widget; ?>

      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
  	<!-- products less than alert quntity -->
  	<div class="row">

      <div class="col-sm-6">
        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('lang_v1.sales_payment_dues'), false); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_sales_payment_dues') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="sales_payment_dues_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'contact.customer' ); ?></th>
                <th><?php echo app('translator')->get( 'sale.invoice_no' ); ?></th>
                <th><?php echo app('translator')->get( 'home.due_amount' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>
      </div>

  		<div class="col-sm-6">

        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('lang_v1.purchase_payment_dues'), false); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.payment_dues') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="purchase_payment_dues_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'purchase.supplier' ); ?></th>
                <th><?php echo app('translator')->get( 'purchase.ref_no' ); ?></th>
                        <th><?php echo app('translator')->get( 'home.due_amount' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>

  		</div>
    </div>

    <div class="row">
      
      <div class="col-sm-6">
        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('home.product_stock_alert'), false); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.product_stock_alert') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="stock_alert_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'sale.product' ); ?></th>
                <th><?php echo app('translator')->get( 'business.location' ); ?></th>
                        <th><?php echo app('translator')->get( 'report.current_stock' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>
      </div>
      <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
        <?php if(session('business.enable_product_expiry') == 1): ?>
          <div class="col-sm-6">
              <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
                  <?php $__env->slot('icon'); ?>
                    <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                  <?php $__env->endSlot(); ?>
                  <?php $__env->slot('title'); ?>
                    <?php echo e(__('home.stock_expiry_alert'), false); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.stock_expiry_alert', [ 'days' =>session('business.stock_expiry_alert_days', 30) ]) . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                  <?php $__env->endSlot(); ?>
                  <input type="hidden" id="stock_expiry_alert_days" value="<?php echo e(\Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d'), false); ?>">
                  <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                    <thead>
                      <tr>
                          <th><?php echo app('translator')->get('business.product'); ?></th>
                          <th><?php echo app('translator')->get('business.location'); ?></th>
                          <th><?php echo app('translator')->get('report.stock_left'); ?></th>
                          <th><?php echo app('translator')->get('product.expires_in'); ?></th>
                      </tr>
                    </thead>
                  </table>
              <?php echo $__env->renderComponent(); ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
  	</div>

    <?php if(!empty($widgets['after_dashboard_reports'])): ?>
      <?php $__currentLoopData = $widgets['after_dashboard_reports']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $widget; ?>

      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script src="<?php echo e(asset('js/home.js?v=' . $asset_v), false); ?>"></script>
    <?php if(!empty($all_locations)): ?>
      <?php echo $sells_chart_1->script(); ?>

      <?php echo $sells_chart_2->script(); ?>

    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<!--Chat ad when page loads-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style> 
.btns{
    display:flex;
    bottom:36px;
    
    bottom:0;
    left:0;
}

.zap {
  position:absolute;
  display:inline-block; 
  align-items:center;
  cursor:pointer;
  right:16px;
 
}


#lbl{
    position:fixed;
    right:80px;
    margin-top:7px;
}

@media  screen and (max-width:900px) {
    #lbl {
        display: none;
    }
}

.form-check-label{
margin-top:8px; 
display:block;

}

</style>


<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">NOVIDADES PRA VOCE!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <video id="videotrailer" width="100%" height="60%" muted>
        <source src="../../../public/videos/infinity_web.mp4" type="video/mp4">
         <source src="movie.ogg" type="video/ogg">
        Your browser does not support the video tag.
        </video>
      </div>
      <div class="modal-footer">
          
          <div class="form-check zap">
         <label id="lbl" class="form-check-label" for="flexCheckChecked">
             Quero Saber Mais
        </label>      
        <a class="btn btn-success" target="_blank" href="https://wa.me//5562985584911?text=Olá! Quero saber mais sobre o InfinityWebChat multiatendimento."><i style="font-size:2.2rem" class="fa-brands fa-whatsapp"></i> </a>
        </div>
          
        <div class="btns">
            
             <button type="button" onclick="play()" class="btn btn-success"><i class="fa fa-play"></i> Assistir</button>   
          
        <button type="button" onclick="stop()" class="btn btn-danger" data-dismiss="modal">Ok, Sair</button>
        </div>  
       
      </div>
    </div>
  </div>
</div>



<script>

let videotrailer = document.getElementById('videotrailer');
let exampleModalCenter = document.getElementById('exampleModalCenter');

//When load page verify if user whatch the video of the Advertising
document.addEventListener("DOMContentLoaded", function () {
    verifyPlay();
});

//Stop the video and set key in localStorage for don't show no more the video
function stop() {
    localStorage.setItem("videochatwatched", "true");
    videotrailer.currentTime = 0;
    videotrailer.muted = true;
}

//Play video Advertising
function play() {
    videotrailer.play();
    videotrailer.muted = false;
    showWhatsapp()
}

function verifyPlay() {
    let valueGetPlay = localStorage.getItem("videochatwatched");
    if (valueGetPlay === 'true') {
        $(exampleModalCenter).modal('hide');
    } else {
        $(exampleModalCenter).modal('show');
    }
}

</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>




<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/home/index.blade.php ENDPATH**/ ?>