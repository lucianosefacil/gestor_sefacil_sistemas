<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('sku_prefix', 'Prefixo SKU:'); ?>

                <?php echo Form::text('sku_prefix', $business->sku_prefix, ['class' => 'form-control text-uppercase']); ?>

            </div>
        </div>
        
        <div class="col-sm-4">
            <?php echo Form::label('enable_product_expiry', __( 'product.enable_product_expiry' ) . ':'); ?>

            <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_enable_expiry') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>

            <div class="input-group">
                <span class="input-group-addon">
                    <?php echo Form::checkbox('enable_product_expiry', 1, $business->enable_product_expiry ); ?> 
                </span>

                <select class="form-control" id="expiry_type"
                name="expiry_type" 
                <?php if(!$business->enable_product_expiry): ?> disabled <?php endif; ?>>
                <option value="add_expiry" <?php if($business->expiry_type == 'add_expiry'): ?> selected <?php endif; ?>>
                    <?php echo e(__('lang_v1.add_expiry'), false); ?>

                </option>
                <option value="add_manufacturing" <?php if($business->expiry_type == 'add_manufacturing'): ?> selected <?php endif; ?>><?php echo e(__('lang_v1.add_manufacturing_auto_expiry'), false); ?></option>
            </select>
        </div>
    </div>

    <div class="col-sm-4 <?php if(!$business->enable_product_expiry): ?> hide <?php endif; ?>" id="on_expiry_div">
        <div class="form-group">
            <div class="multi-input">
                <?php echo Form::label('on_product_expiry', __('lang_v1.on_product_expiry') . ':'); ?>

                <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_on_product_expiry') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                <br>

                <?php echo Form::select('on_product_expiry',     ['keep_selling'=>__('lang_v1.keep_selling'), 'stop_selling'=>__('lang_v1.stop_selling') ], $business->on_product_expiry, ['class' => 'form-control pull-left', 'style' => 'width:60%;']); ?>


                <?php
                $disabled = '';
                if($business->on_product_expiry == 'keep_selling'){
                $disabled = 'disabled';
            }
            ?>

            <?php echo Form::number('stop_selling_before', $business->stop_selling_before, ['class' => 'form-control pull-left', 'placeholder' => 'stop n days before', 'style' => 'width:40%;', $disabled, 'required', 'id' => 'stop_selling_before']); ?>

        </div>
    </div>
</div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <div class="checkbox">
              <label>
                <?php echo Form::checkbox('enable_brand', 1, $business->enable_brand, 
                [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_brand' ), false); ?>

            </label>
        </div>
    </div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_category', 1, $business->enable_category, [ 'class' => 'input-icheck', 'id' => 'enable_category']); ?> <?php echo e(__( 'lang_v1.enable_category' ), false); ?>

        </label>
    </div>
</div>
</div>

<div class="col-sm-4 enable_sub_category <?php if($business->enable_category != 1): ?> hide <?php endif; ?>">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_sub_category', 1, $business->enable_sub_category, [ 'class' => 'input-icheck', 'id' => 'enable_sub_category']); ?> <?php echo e(__( 'lang_v1.enable_sub_category' ), false); ?>

        </label>
    </div>
</div>
</div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <div class="checkbox">
              <label>
                <?php echo Form::checkbox('enable_price_tax', 1, $business->enable_price_tax, [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_price_tax' ), false); ?>

            </label>
        </div>
    </div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <?php echo Form::label('default_unit', 'Unidade padrão:'); ?>

        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fa-balance-scale"></i>
            </span>
            <?php echo Form::select('default_unit', $units_dropdown, $business->default_unit, ['class' => 'form-control select2', 'style' => 'width: 100%;' ]); ?>

        </div>
    </div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_sub_units', 1, $business->enable_sub_units, [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_sub_units' ), false); ?>

        </label>
        <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.sub_units_tooltip') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
    </div>
</div>
</div>

<div class="clearfix"></div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_racks', 1, $business->enable_racks, [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_racks' ), false); ?>

        </label>
        <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_enable_racks') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
    </div>
</div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_row', 1, $business->enable_row, [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_row' ), false); ?>

        </label>
    </div>
</div>
</div>



<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('enable_position', 1, $business->enable_position, [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_position' ), false); ?>

        </label>
    </div>
</div>
</div>

<div class="clearfix"></div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
          <label>
            <?php echo Form::checkbox('common_settings[enable_product_warranty]', 1, !empty($common_settings['enable_product_warranty']) ? true : false, 
            [ 'class' => 'input-icheck']); ?> <?php echo e(__( 'lang_v1.enable_product_warranty' ), false); ?>

        </label>
    </div>
</div>
</div>

<div class="clearfix"></div>


<div class="col-sm-6">
    <div class="form-group">
        <?php echo Form::label('cst_csosn_padrao', 'CST/CSOSN Padrão' . ':*'); ?>

        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fas fa-circle"></i>
            </span>
            <?php echo Form::select('cst_csosn_padrao', $listaCSTCSOSN, $business->cst_csosn_padrao, ['class' => 'form-control',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

        </div>
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
        <?php echo Form::label('cst_pis_padrao', 'CST/PIS Padrão' . ':*'); ?>

        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fas fa-circle"></i>
            </span>
            <?php echo Form::select('cst_pis_padrao', $listaCST_PIS_COFINS, $business->cst_pis_padrao, ['class' => 'form-control',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

        </div>
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
        <?php echo Form::label('cst_cofins_padrao', 'CST/COFINS Padrão' . ':*'); ?>

        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fas fa-circle"></i>
            </span>
            <?php echo Form::select('cst_cofins_padrao', $listaCST_PIS_COFINS, $business->cst_cofins_padrao, ['class' => 'form-control',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

        </div>
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
        <?php echo Form::label('cst_ipi_padrao', 'CST/IPI Padrão' . ':*'); ?>

        <div class="input-group">
            <span class="input-group-addon">
                <i class="fa fas fa-circle"></i>
            </span>
            <?php echo Form::select('cst_ipi_padrao', $listaCST_IPI, $business->cst_ipi_padrao, ['class' => 'form-control',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

        </div>
    </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('perc_icms_padrao', '%ICMS Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('perc_icms_padrao', $business->perc_icms_padrao, ['class' => 'form-control text-uppercase', 'data-mask="00.00"', 'data-mask-reverse="true"']); ?>

       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('perc_pis_padrao', '%PIS Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('perc_pis_padrao', $business->perc_pis_padrao, ['class' => 'form-control text-uppercase', 'data-mask="00.00"', 'data-mask-reverse="true"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('perc_cofins_padrao', '%COFINS Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('perc_cofins_padrao', $business->perc_cofins_padrao, ['class' => 'form-control text-uppercase', 'data-mask="00.00"', 'data-mask-reverse="true"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('perc_ipi_padrao', '%IPI Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('perc_ipi_padrao', $business->perc_ipi_padrao, ['class' => 'form-control text-uppercase', 'data-mask="00.00"', 'data-mask-reverse="true"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('ncm_padrao', 'NCM Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('ncm_padrao', $business->ncm_padrao, ['class' => 'form-control text-uppercase', 'data-mask="0000.00.00"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('cfop_saida_estadual_padrao', 'CFOP saida estadual Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('cfop_saida_estadual_padrao', $business->cfop_saida_estadual_padrao, ['class' => 'form-control text-uppercase', 'data-mask="0000"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('cfop_saida_inter_estadual_padrao', 'CFOP saida inter estadual Padrão' . ':*'); ?>

        <div class="">
           <?php echo Form::text('cfop_saida_inter_estadual_padrao', $business->cfop_saida_inter_estadual_padrao, ['class' => 'form-control text-uppercase', 'data-mask="0000"']); ?>


       </div>
   </div>
</div>

<div class="col-sm-3">
    <div class="form-group">
        <?php echo Form::label('pCredSN', '% Cred. ICMS' . ':*'); ?>

        <div class="">
           <?php echo Form::text('pCredSN', $business->pCredSN, ['class' => 'form-control text-uppercase', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>


       </div>
   </div>
</div>


</div>
</div><?php /**PATH /home/gestor/public_html/gestor_sefacil_sistemas/resources/views/business/partials/settings_product.blade.php ENDPATH**/ ?>