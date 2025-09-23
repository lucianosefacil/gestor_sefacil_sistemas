<?php $__env->startSection('title', 'Lista de Devoluções'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Devoluções
        <small>Lista</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">


    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => 'NFe Lista Devolução']); ?>

    <div class="box-header">


        <div class="box-tools">
            <a class="btn btn-block btn-primary" href="<?php echo e(route('devolucao.index'), false); ?>">
                <i class="fa fa-plus"></i> Nova Devolução</a>
            </div>
        </div>


        <form action="<?php echo e(route('devolucao.filtro'), false); ?>" method="get">
            <div class="row">
                <div class="col-sm-2 col-lg-3">
                    <div class="form-group">
                        <label for="product_custom_field2">Data inicial:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input class="form-control start-date-picker" required placeholder="Data inicial" value="<?php echo e(isset($data_inicio) ? $data_inicio : ''); ?>" data-mask="00/00/0000" name="data_inicio" type="text" id="">
                        </div>

                    </div>
                </div>
                <div class="col-sm-2 col-lg-3">
                    <div class="form-group">
                        <label for="product_custom_field2">Data final:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input class="form-control start-date-picker" required placeholder="Data final" data-mask="00/00/0000" name="data_final" type="text" value="<?php echo e(isset($data_final) ? $data_final : ''); ?>">
                        </div>

                    </div>
                </div>

                <?php if(is_null($default_location)): ?>

                <div class="col-sm-2 col-lg-3">
                    <br>
                    <div class="form-group" style="margin-top: 8px;">

                        <?php echo Form::select('select_location_id', $business_locations, $select_location_id, ['class' => 'form-control input-sm', 'placeholder' => 'Todas','id' => 'select_location_id', '', 'autofocus'], $bl_attributes); ?>


                    </div>

                </div>
                <?php endif; ?>

                <div class="col-sm-2 col-lg-3">
                    <div class="form-group"><br>
                        <button style="margin-top: 5px;" class="btn btn-block btn-primary">Filtrar</button>
                    </div>
                </div>

            </div>
        </form>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('user.view')): ?>


        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="users_table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor</th>
                        <th>Valor Integral</th>
                        <th>Valor Devolvido</th>
                        <th>Estado</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $devolucoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i:s'), false); ?></td>

                        <td><?php echo e($d->contact ? $d->contact->name : '--', false); ?></td>
                        <td><?php echo e(number_format($d->valor_integral, 2, ',', '.'), false); ?></td>
                        <td><?php echo e(number_format($d->valor_devolvido, 2, ',', '.'), false); ?></td>
                        <td><?php echo e($d->estado(), false); ?></td>
                        <td>
                            <?php if($d->estado == 3): ?>
                            <a class="btn" title="Ver" href="<?php echo e(route('devolucao.ver', [$d->id]), false); ?>">
                                <i class="fas fa-arrow-right text-success"></i>
                            </a>

                            <a class="btn" title="Imprimir" target="_blank" href="<?php echo e(route('devolucao.imprimir-cancelamento', [$d->id]), false); ?>">
                                <i class="fa fa-print text-danger" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>


                            <?php if($d->estado == 0 || $d->estado == 2): ?>
                            <form id="devolucao<?php echo e($d->id, false); ?>" method="POST" action="<?php echo e(route('devolucao.destroy', $d->id), false); ?>">
                                <?php echo method_field('delete'); ?>
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-link btn-delete" type="button" title="Remover" >
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </button>

                                <a class="btn btn-clear" title="Ver" href="<?php echo e(route('devolucao.edit', [$d->id]), false); ?>">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>

                                <a class="btn btn-clear" title="Ver" href="<?php echo e(route('devolucao.ver', [$d->id]), false); ?>">
                                    <i class="fas fa-arrow-right text-success"></i>
                                </a>
                            </form>
                            <?php endif; ?>

                            <?php if($d->estado == 1): ?>

                            <a class="btn" title="Ver" target="_blank" href="<?php echo e(route('devolucao.ver', [$d->id]), false); ?>">
                                <i class="fas fa-arrow-right text-success"></i>
                            </a>

                            <a class="btn" title="Baixar XML Aprovado" target="_blank" href="<?php echo e(route('devolucao.baixar-xml', [$d->id]), false); ?>">
                                <i class="fa fas fa-arrow-circle-down text-success"></i>
                            </a>

                            <a class="btn" title="Imprimir" target="_blank" href="<?php echo e(route('devolucao.imprimir', [$d->id]), false); ?>">
                                <i class="fa fa-print" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>

                            <a class="btn" title="Alterar estado fiscal" href="<?php echo e(route('devolucao.edit-fiscal', [$d->id]), false); ?>">
                                <i class="fas fa-file text-info"></i>
                            </a>


                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>


        </div>


        <?php endif; ?>
        <?php echo $__env->renderComponent(); ?>

        <div class="modal fade user_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">


</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/devolucao/lista.blade.php ENDPATH**/ ?>