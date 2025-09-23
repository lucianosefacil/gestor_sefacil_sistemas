<?php 
$colspan = 15;
$custom_labels = json_decode(session('business.custom_labels'), true);
?>
<div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view hide-footer" id="product_table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all-row"></th>
                <th>Foto</th>
                <th><?php echo app('translator')->get('Ações'); ?></th>
                <th><?php echo app('translator')->get('Código'); ?></th>
                    
                <th><?php echo app('translator')->get('sale.product'); ?></th>
                <th><?php echo app('translator')->get('Local Empresa'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.product_business_location_tooltip') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?></th>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_purchase_price')): ?>
                <?php 
                $colspan++;
                ?>
                <th><?php echo app('translator')->get('Valor de Compra'); ?></th>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_default_selling_price')): ?>
                <?php 
                $colspan++;
                ?>
                <th><?php echo app('translator')->get('Valor de Venda'); ?></th>
                <?php endif; ?>
                <th><?php echo app('translator')->get('Estoque'); ?></th>
                <!-- <th><?php echo app('translator')->get('product.product_type'); ?></th> -->
                <th></th>
                <!-- <th><?php echo app('translator')->get('product.brand'); ?></th> -->
                <th></th>
                <!-- <th>NCM</th> -->
                
                <!-- <th>CEST</th> -->
                


            </tr>
        </thead>

    </table>


</div>

<div class="row" style="margin-left: 5px;">
    <tr >
        <td colspan="<?php echo e($colspan, false); ?>">
            <div style="display: flex; width: 100%;">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.delete')): ?>
                <?php echo Form::open(['url' => action('ProductController@massDestroy'), 'method' => 'post', 'id' => 'mass_delete_form' ]); ?>

                <?php echo Form::hidden('selected_rows', null, ['id' => 'selected_rows']); ?>

                <?php echo Form::submit(__('lang_v1.delete_selected'), array('class' => 'btn btn-xs btn-danger', 'id' => 'delete-selected')); ?>

                <?php echo Form::close(); ?>

                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.update')): ?>
                &nbsp;
                <?php echo Form::open(['url' => action('ProductController@bulkEdit'), 'method' => 'post', 'id' => 'bulk_edit_form' ]); ?>

                <?php echo Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); ?>

                <button type="submit" class="btn btn-xs btn-primary" id="edit-selected"> <i class="fa fa-edit"></i><?php echo e(__('lang_v1.bulk_edit'), false); ?></button>
                <?php echo Form::close(); ?>

                &nbsp;
                <button type="button" class="btn btn-xs btn-success update_product_location" data-type="add">Adicionar localização</button>
                &nbsp;
                <button type="button" class="btn btn-xs bg-navy update_product_location" data-type="remove">Remover localização</button>
                <?php endif; ?>
                &nbsp;
                <?php echo Form::open(['url' => action('ProductController@massDeactivate'), 'method' => 'post', 'id' => 'mass_deactivate_form' ]); ?>

                <?php echo Form::hidden('selected_products', null, ['id' => 'selected_products']); ?>

                <?php echo Form::submit('Desativar selecionado', array('class' => 'btn btn-xs btn-warning', 'id' => 'deactivate-selected')); ?>

                <?php echo Form::close(); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . 'Destivar os produtos selecionados' . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            </div>
        </td>
    </tr>
</div><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/product/partials/product_list.blade.php ENDPATH**/ ?>