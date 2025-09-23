<?php $__env->startSection('title', __('superadmin::lang.superadmin') . ' | Business'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo app('translator')->get( 'superadmin::lang.all_business' ); ?>
        <small><?php echo app('translator')->get( 'superadmin::lang.manage_business' ); ?></small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
            <h3 class="box-title">&nbsp;</h3>
            <div class="box-tools">

                <a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\BusinessController@create'), false); ?>" 
                class="btn btn-block btn-primary">
                <i class="fa fa-plus"></i> <?php echo app('translator')->get( 'messages.add' ); ?></a>
            </div>
        </div>

        <div class="col-md-12">
            <?php $__env->startComponent('components.filters', ['title' => __('report.filters')]); ?>

            <?php echo Form::open(['url' => action('\Modules\Superadmin\Http\Controllers\BusinessController@search'), 'method' => 'get', 'id' => 'business_form' ]); ?>


            <div class="col-md-2">
                <div class="form-group">
                    <?php echo Form::label('type_search', 'Tipo da pesquisa:'); ?>

                    <?php echo Form::select('type_search', ['razao_social' => 'Razão Social', 'nome_fantasia' => 'Nome Fantasia', 'cnpj' => 'CNPJ'], isset($type_search) ? $type_search : '', ['class' => 'form-control']); ?>

                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('search', 'Pesquisar:'); ?>

                    <?php echo Form::text('search', isset($search) ? $search : '', ['placeholder' => 'Pesquisar', 'class' => 'form-control', 'id' => 'search']); ?>

                </div>
            </div>

            <div class="col-md-4">
                <br>
                <button class="btn btn-primary">
                    <i class="fa fa-search"></i>
                    Filtrar
                </button>
                <a class="btn btn-info" href="<?php echo e(route('business.certificados'), false); ?>">
                    <i class="fa fa-file"></i>
                    Relatório de certificados
                </a>


            </div>

            <?php echo Form::close(); ?>



            <?php echo $__env->renderComponent(); ?>
        </div>


        <div class="box-body">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('superadmin')): ?>

            <?php $__currentLoopData = $businesses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $business): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $address = $business->locations->first();
            ?>
            <?php if($loop->index % 3 == 0): ?>
            <div class="row">
                <?php endif; ?>

                <div class="col-md-4">

                    <div class="box box-widget widget-user-2">

                        <div class="widget-user-header bg-yellow">
                          <div class="widget-user-image">
                            <?php if(!empty($business->logo)): ?>
                            <img class="img-circle" src="<?php echo e(url( 'uploads/business_logos/' . $business->logo ), false); ?>" alt="Business Logo">
                            <?php endif; ?>
                        </div>
                        <!-- /.widget-user-image -->
                        <h4 class="widget-user-username"><?php echo e($business->name, false); ?></h4>
                        
                        
                        
                        
                        <h5 class="widget-user-desc"><i class="fa fa-user-secret" title="Owner"></i> <?php echo e(optional($business->owner)->first_name . ' ' . optional($business->owner)->last_name ?? 'Não informado', false); ?></h5>
                        
                        <h5 class="widget-user-desc"><i class="fa fa-file" title="Owner"></i> <?php echo e($business->cnpj, false); ?></h5>
                        <h5 class="widget-user-desc"><i class="fa fa-envelope" title="Owner Email"></i> <?php echo e($business->owner ? $business->owner->email : '--', false); ?></h5>
                        <h5 class="widget-user-desc"><i class="fa fa-mobile" title="Owner Contact"></i> <?php echo e($business->owner ? $business->owner->contact_no : '--', false); ?></h5>
                        <h5 class="widget-user-desc"><i class="fa fa-phone" title="Business Contact"></i> <?php echo e(implode([", ", $address->mobile, $address->alternate_number]), false); ?></h5>
                        <address class="widget-user-desc">
                          <?php
                          $address_array = [];
                          $city_landmark = '';
                          if(!empty($address->city)){
                            $city_landmark = $address->city;
                        }
                        if(!empty($address->landmark)){
                            $city_landmark .= ', ' . $address->landmark;
                        }
                        if(!empty($city_landmark)){
                            $address_array[] = $city_landmark;
                        }

                        $state_country = '';
                        if(!empty($address->state)){
                            $state_country = $address->state;
                        }
                        if(!empty($address->country)){
                            $state_country .= ' (' . $address->country . ')';
                        }
                        if(!empty($state_country)){
                            $address_array[] = $state_country;
                        }
                        if(!empty($address->zip_code)){
                            $address_array[] = __('business.zip_code') . ': ' .$address->zip_code;
                        }
                        ?>
                        <?php echo strip_tags(implode('<br>', $address_array), '<br>'); ?>

                    </address>
                    <h5 class="widget-user-desc">
                        <i class="fa fa-credit-card" title="Active Package"></i> 
                        <?php
                        $package = !empty($business->subscriptions[0]) ? optional($business->subscriptions[0])->package : '';
                        ?>

                        <?php if(!empty($package)): ?>
                        <?php echo e($package->name, false); ?> 
                        <?php endif; ?>
                    </h5>
                    <?php if(!empty($business->subscriptions[0])): ?>
                    <h5 class="widget-user-desc">
                        <i class="fas fa-clock"></i> 
                        <?php echo app('translator')->get('superadmin::lang.remaining', ['days' => \Carbon::today()->diffInDays($business->subscriptions[0]->end_date)]); ?>
                    </h5>
                    <?php endif; ?>
                </div>
                <div class="box-footer">
                    <a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\BusinessController@show', [$business->id]), false); ?>"
                        class="btn btn-info btn-xs"><?php echo app('translator')->get('superadmin::lang.manage' ); ?></a>

                        <button type="button" class="btn btn-primary btn-xs btn-modal" data-href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController@create', ['business_id' => $business->id]), false); ?>" data-container=".view_modal">
                            <?php echo app('translator')->get('superadmin::lang.add_subscription' ); ?>
                        </button>

                        <?php if($business->is_active == 1): ?>
                        <a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\BusinessController@toggleActive', [$business->id, 0]), false); ?>"
                            class="btn btn-danger btn-xs link_confirmation">Desativar
                        </a>
                        <?php else: ?>
                        <a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\BusinessController@toggleActive', [$business->id, 1]), false); ?>"
                            class="btn btn-success btn-xs link_confirmation">Ativar
                        </a>
                        <?php endif; ?>

                        <?php if($business_id != $business->id): ?>
                        <a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\BusinessController@destroy', [$business->id]), false); ?>"
                            class="btn btn-danger btn-xs delete_business_confirmation"><?php echo app('translator')->get('messages.delete' ); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($loop->index % 3 == 2): ?>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="col-md-12">
            <?php echo e($businesses->links(), false); ?>

        </div>

        <?php endif; ?>
    </div>

</div>

<div class="modal fade brands_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>

<script type="text/javascript">
    $(document).on('click', 'a.delete_business_confirmation', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            text: "Depois de excluído, você não poderá recuperar esta empresa!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                window.location.href = $(this).attr('href');
            }
        });
    });

    $('#type_search').change(() => {
        $('#search').val('')
        let type = $('#type_search').val()
        if(type == 'cnpj'){
            $('#search').mask('00.000.000/0000-00')
        }else{
            $('#search').unmask();
        }
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/gestor/public_html/gestor_sefacil_sistemas/Modules/Superadmin/Providers/../Resources/views/business/index.blade.php ENDPATH**/ ?>