<?php $__env->startSection('title', 'Alterar Data Registro'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Alterar Data Registro</h1>
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            <?php $__env->startComponent('components.widget'); ?>

            <?php echo Form::open(['url' => action('NfeController@salvarAlteracaoData'), 'method' => 'post', 'id' => 'nfe_add_form' ]); ?>

            
            <input type="hidden" id="token" value="<?php echo e(csrf_token(), false); ?>" name="">

            <input type="hidden" id="id" value="<?php echo e($nfe->id, false); ?>" name="nfe_id">

            <div class="col-md-12">
                <h4>Data Registro Atual: <strong><?php echo e($nfe->transaction_date, false); ?></strong></h4>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-3">
                <div class="form-group">
                    <?php echo Form::label('nova_data_regitro', 'Nova Data Registro' . ':*'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type="date" id="currentDate" name="nova_data">
                        <input type="time" id="currentTime" name="nova_hora">
                        
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button id="" type="submit" class="btn btn-info pull-right"><?php echo app('translator')->get( 'messages.save' ); ?> Data</button>
                </div>
            </div>

            <?php echo $__env->renderComponent(); ?>
        </div>

    </div>


    <?php echo Form::close(); ?>


    <br>
    <div class="row" id="action" style="display: none">
        <div class="col-md-12">
            <?php $__env->startComponent('components.widget'); ?>
            <div class="info-box-content">
                <div class="col-md-4 col-md-offset-4">

                    <span class="info-box-number total_purchase">
                        <strong id="acao"></strong>
                        <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i></span>
                </div>
            </div>
            <?php echo $__env->renderComponent(); ?>

        </div>
    </div>

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">
        const getTwoDigits = (value) => value < 10 ? `0${value}` : value;

        const formatDate = (date) => {
            const day = getTwoDigits(date.getDate());
            const month = getTwoDigits(date.getMonth() + 1); // add 1 since getMonth returns 0-11 for the months
            const year = date.getFullYear();

            return `${year}-${month}-${day}`;
        }

        const formatTime = (date) => {
            const hours = getTwoDigits(date.getHours());
            const mins = getTwoDigits(date.getMinutes());

            return `${hours}:${mins}`;
        }

        const date = new Date();
        document.getElementById('currentDate').value = formatDate(date);
        document.getElementById('currentTime').value = formatTime(date);

        // swal("Good job!", "You clicked the button!", "success");
        var path = window.location.protocol + '//' + window.location.host

    </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/gestor/public_html/gestor_sefacil_sistemas/resources/views/nfe/alterar_data_emissao.blade.php ENDPATH**/ ?>