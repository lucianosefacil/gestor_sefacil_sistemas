<?php $__env->startSection('title', 'Importação de XML'); ?>

<?php $__env->startSection('content'); ?>

<style type="text/css">
input[type='file'] {
  display: none
}

/* Aparência que terá o seletor de arquivo */
label {
  background-color: #3498db;
  border-radius: 5px;
  color: #fff;
  cursor: pointer;
  margin: 10px;
  padding: 16px 40px
}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Compra
        <small>Nova Importação de XML</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => 'Importação de XML']); ?>

    <form method="post" action="" enctype='multipart/form-data'>
        <?php echo csrf_field(); ?>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="business_logo">Selecione um arquivo XML &#187;</label>
                <input accept=".xml" name="file" type="file" id="business_logo" onchange="form.submit()">
            </div>
        </div>
    </form>


    <?php echo $__env->renderComponent(); ?>

    <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/purchase_xml/index.blade.php ENDPATH**/ ?>