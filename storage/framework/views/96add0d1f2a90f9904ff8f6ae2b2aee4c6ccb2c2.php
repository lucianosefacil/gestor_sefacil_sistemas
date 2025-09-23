<?php $__env->startSection('title', __('product.add_new_product')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo app('translator')->get('product.add_new_product'); ?></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
      <?php
      $form_class = empty($duplicate_product) ? 'create' : '';
      ?>
      <?php echo Form::open(['url' => action('ProductController@store'), 'method' => 'post',
      'id' => 'product_add_form','class' => 'product_form ' . $form_class, 'files' => true ]); ?>

      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('name', __('product.product_name') . ':*'); ?>

            <?php echo Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, ['class' => 'form-control', 'required',
            'placeholder' => __('product.product_name')]); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('sku', __('product.sku') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.sku') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::text('sku', null, ['class' => 'form-control',
            'placeholder' => __('product.sku')]); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('barcode_type', __('product.barcode_type') . ':*'); ?>

            <?php echo Form::select('barcode_type', $barcode_types, !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default, ['class' => 'form-control select2', 'required']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('codigo_barras', 'Código de barras' . ':'); ?>

            <?php echo Form::text('codigo_barras', null, ['class' => 'form-control',
            'placeholder' => 'Código de barras']); ?>

          </div>
        </div>

        <!-- <div class="clearfix"></div> -->
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('unit_id', __('product.unit') . ':*'); ?>

            <div class="input-group">
              <?php echo Form::select('unit_id', $units, !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'), ['class' => 'form-control select2', 'required']); ?>

              <span class="input-group-btn">
                <button type="button" <?php if(!auth()->user()->can('unit.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat btn-modal" data-href="<?php echo e(action('UnitController@create', ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('unit.add_unit'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
          </div>
        </div>

        <div class="col-sm-4 <?php if(!session('business.enable_sub_units')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.sub_units_tooltip') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>

            <?php echo Form::select('sub_unit_ids[]', [], !empty($duplicate_product->sub_unit_ids) ? $duplicate_product->sub_unit_ids : null, ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids']); ?>

          </div>
        </div>

        <div class="col-sm-4 <?php if(!session('business.enable_brand')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('brand_id', __('product.brand') . ':'); ?>

            <div class="input-group">
              <?php echo Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

              <span class="input-group-btn">
                <button type="button" <?php if(!auth()->user()->can('brand.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat btn-modal" data-href="<?php echo e(action('BrandController@create', ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('brand.add_brand'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4 <?php if(!session('business.enable_category')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('category_id', __('product.category') . ':'); ?>

            <?php echo Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-4 <?php if(!(session('business.enable_category') && session('business.enable_sub_category'))): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('sub_category_id', __('product.sub_category') . ':'); ?>

            <?php echo Form::select('sub_category_id', $sub_categories, !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

          </div>
        </div>

        <?php
        $default_location = null;
        if(count($business_locations) == 1){
          $default_location = array_key_first($business_locations->toArray());
        }
        ?>
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('product_locations', __('business.business_locations') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.product_location_help') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::select('product_locations[]', $business_locations, $default_location, ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); ?>

          </div>
        </div>


        <div class="clearfix"></div>
        
        <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              <?php echo Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, ['class' => 'input-icheck', 'id' => 'enable_stock']); ?> <strong><?php echo app('translator')->get('product.manage_stock'); ?></strong>
            </label><?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.enable_stock') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?> <p class="help-block"><i><?php echo app('translator')->get('product.enable_stock_help'); ?></i></p>
          </div>
        </div>

        <div class="col-sm-4 <?php if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0): ?> hide <?php endif; ?>" id="alert_quantity_div">
          <div class="form-group">
            <?php echo Form::label('alert_quantity',  __('product.alert_quantity') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.alert_quantity') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::number('alert_quantity', !empty($duplicate_product->alert_quantity) ? $duplicate_product->alert_quantity : null , ['class' => 'form-control',
            'placeholder' => __('product.alert_quantity'), 'min' => '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('image', __('lang_v1.product_image') . ':'); ?>

            <?php echo Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*']); ?>

            <small><p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?> <br> <?php echo app('translator')->get('lang_v1.aspect_ratio_should_be_1_1'); ?></p></small>
          </div>
        </div>

        <?php if(!empty($common_settings['enable_product_warranty'])): ?>
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('warranty_id', __('lang_v1.warranty') . ':'); ?>

            <?php echo Form::select('warranty_id', $warranties, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); ?>

          </div>
        </div>
        <?php endif; ?>
        <!-- include module fields -->
        <?php if(!empty($pos_module_data)): ?>
        <?php $__currentLoopData = $pos_module_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(!empty($value['view_path'])): ?>
        <?php if ($__env->exists($value['view_path'], ['view_data' => $value['view_data']])) echo $__env->make($value['view_path'], ['view_data' => $value['view_data']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        <div class="clearfix"></div>
        
      </div>
      <?php echo $__env->renderComponent(); ?>

      

      <?php if(in_array('nuvemshop', $enabled_modules) && auth()->user()->can('ecommerce.view')): ?>
      <div class="box <?php if(!empty($class)): ?> <?php echo e($class, false); ?> <?php else: ?> box-primary <?php endif; ?>" id="accordionNuvem">
        <div class="box-header with-border" style="cursor: pointer;">
          <h3 class="box-title">
            <a data-toggle="collapse" data-parent="#accordionNuvem" href="#collapseFilter1">
              Nuvem Shop
            </a>
          </h3>
        </div>
        <div id="collapseFilter1" class="panel-collapse active collapse" aria-expanded="true">
          <div class="box-body">
            <div class="row">
              <div class="col-sm-2">
                <div class="form-group">
                  <label>
                    <?php echo Form::checkbox('ecommerce', 1, !(empty($duplicate_product)) ? $duplicate_product->ecommerce : false, ['class' => 'input-icheck']); ?> <strong>Nuvem Shop</strong>
                  </label> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . 'Se marcado, o produto será cadastrado na Nuvem Shop. OBS: Se estiver com a configuração completa' . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                </div>
              </div>

              <div class="clearfix"></div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('weight',  __('lang_v1.weight') . ':'); ?>

                  <?php echo Form::text('weight', !empty($duplicate_product->weight) ? $duplicate_product->weight : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight'), 'data-mask="000000.000"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('altura',  'Altura' . ':'); ?>

                  <?php echo Form::text('altura', null, ['class' => 'form-control', 'placeholder' => 'Altura', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('largura',  'Largura' . ':'); ?>

                  <?php echo Form::text('largura', null, ['class' => 'form-control', 'placeholder' => 'Largura', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('comprimento',  'Comprimento' . ':'); ?>

                  <?php echo Form::text('comprimento', null, ['class' => 'form-control', 'placeholder' => 'Comprimento', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('valor_ecommerce',  'Valor Nuvem Shop' . ':'); ?>

                  <?php echo Form::text('valor_ecommerce', null, ['class' => 'form-control', 'placeholder' => 'Valor ecommerce', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>
              <div class="col-sm-8">
                <div class="form-group">
                  <?php echo Form::label('product_description', __('lang_v1.product_description') . ':'); ?>

                  <?php echo Form::textarea('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null, ['class' => 'form-control']); ?>

                </div>
              </div>
             
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
      <div class="row">
        <?php if(session('business.enable_product_expiry')): ?>

        <?php if(session('business.expiry_type') == 'add_expiry'): ?>
        <?php
        $expiry_period = 12;
        $hide = true;
        ?>
        <?php else: ?>
        <?php
        $expiry_period = null;
        $hide = false;
        ?>
        <?php endif; ?>
        <div class="col-sm-4 <?php if($hide): ?> hide <?php endif; ?>">
          <div class="form-group">
            <div class="multi-input">
              <?php echo Form::label('expiry_period', __('product.expires_in') . ':'); ?><br>
              <?php echo Form::text('expiry_period', !empty($duplicate_product->expiry_period) ? number_format($duplicate_product->expiry_period, 2, ',', '.') : $expiry_period, ['class' => 'form-control pull-left input_number',
              'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;']); ?>

              <?php echo Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months', ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type']); ?>

            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              <?php echo Form::checkbox('enable_sr_no', 1, !(empty($duplicate_product)) ? $duplicate_product->enable_sr_no : false, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.enable_imei_or_sr_no'); ?></strong>
            </label> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_sr_no') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              <?php echo Form::checkbox('not_for_selling', 1, !(empty($duplicate_product)) ? $duplicate_product->not_for_selling : false, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.not_for_selling'); ?></strong>
            </label> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_not_for_selling') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          </div>
        </div>



        <!-- Rack, Row & position number -->
        <?php if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position')): ?>
        <div class="col-md-12">
          <h4><?php echo app('translator')->get('lang_v1.rack_details'); ?>:
            <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_rack_details') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          </h4>
        </div>
        <?php $__currentLoopData = $business_locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('rack_' . $id,  $location . ':'); ?>


            <?php if(session('business.enable_racks')): ?>
            <?php echo Form::text('product_racks[' . $id . '][rack]', !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null, ['class' => 'form-control', 'id' => 'rack_' . $id,
            'placeholder' => __('lang_v1.rack')]); ?>

            <?php endif; ?>

            <?php if(session('business.enable_row')): ?>
            <?php echo Form::text('product_racks[' . $id . '][row]', !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); ?>

            <?php endif; ?>

            <?php if(session('business.enable_position')): ?>
            <?php echo Form::text('product_racks[' . $id . '][position]', !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); ?>

            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>


        <?php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
        $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
        $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
        $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
        ?>
        <!--custom fields-->
        <div class="clearfix"></div>
        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field1',  $product_custom_field1 . ':'); ?>

            <?php echo Form::text('product_custom_field1', !empty($duplicate_product->product_custom_field1) ? $duplicate_product->product_custom_field1 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field2',  $product_custom_field2 . ':'); ?>

            <?php echo Form::text('product_custom_field2', !empty($duplicate_product->product_custom_field2) ? $duplicate_product->product_custom_field2 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field3',  $product_custom_field3 . ':'); ?>

            <?php echo Form::text('product_custom_field3', !empty($duplicate_product->product_custom_field3) ? $duplicate_product->product_custom_field3 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field4',  $product_custom_field4 . ':'); ?>

            <?php echo Form::text('product_custom_field4', !empty($duplicate_product->product_custom_field4) ? $duplicate_product->product_custom_field4 : null, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); ?>

          </div>
        </div>
        <!--custom fields-->
        <div class="clearfix"></div>
        <?php echo $__env->make('layouts.partials.module_form_part', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
      <?php echo $__env->renderComponent(); ?>


      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
      <div class="row">

        <div class="col-sm-3 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('tax', __('product.applicable_tax') . ':'); ?>

            <?php echo Form::select('tax', $taxes, !empty($duplicate_product->tax) ? $duplicate_product->tax : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); ?>

          </div>
        </div>

        <div class="col-sm-3 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('tax_type', __('product.selling_price_tax_type') . ':*'); ?>

            <?php echo Form::select('tax_type', ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
            ['class' => 'form-control select2', 'required']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="">Cód Benefício fiscal:</label>
            <input value="<?php echo e($business->cBenef, false); ?>" class="form-control" placeholder="" data-mask="AAAAAAAAAA" name="cBenef" data-mask-reverse="true" type="text" id="cBenef">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">% Red BC:</label>
            <input value="" class="form-control" placeholder="%Red BC" data-mask="000.00" name="pRedBC" data-mask-reverse="true" type="text" id="pRedBC">
          </div>
        </div>

        <div class="col-sm-2" style="visibility: hidden;">
          <div class="form-group">
            <?php echo Form::label('unidade_venda', 'Unidade de Venda' . ':*'); ?>

            <?php echo Form::select('unidade_venda', $unidadesDeMedida, null, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="clearfix"></div>
       
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('type', __('product.product_type') . ':*'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.product_type') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%ICMS:</label>
            <input required value="<?php echo e($business->perc_icms_padrao, false); ?>" class="form-control" placeholder="%ICMS" data-mask="00.00" name="perc_icms" data-mask-reverse="true" type="text" id="perc_icms">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%PIS:</label>
            <input required value="<?php echo e($business->perc_pis_padrao, false); ?>" class="form-control" placeholder="%PIS" data-mask="00.00" name="perc_pis" data-mask-reverse="true" type="text" id="perc_pis">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%COFINS:</label>
            <input required  value="<?php echo e($business->perc_cofins_padrao, false); ?>" class="form-control" placeholder="%COFINS" data-mask="00.00" data-mask-reverse="true" name="perc_cofins" type="text" id="perc_cofins">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%IPI:</label>
            <input required value="<?php echo e($business->perc_ipi_padrao, false); ?>" class="form-control" placeholder="%IPI" data-mask="00.00" data-mask-reverse="true" name="perc_ipi" type="text" id="perc_ipi">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <?php echo Form::label('cst_csosn', 'CST/CSOSN' . ':*'); ?>

            <?php echo Form::select('cst_csosn', $listaCSTCSOSN, $business->cst_csosn_padrao, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <?php echo Form::label('cst_pis', 'CST/PIS' . ':*'); ?> 
            <?php echo Form::select('cst_pis', $listaCST_PIS_COFINS, $business->cst_pis_padrao, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cst_cofins', 'CST/COFINS' . ':*'); ?> 
            <?php echo Form::select('cst_cofins', $listaCST_PIS_COFINS, $business->cst_cofins_padrao, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cst_ipi', 'CST/IPI' . ':*'); ?> 
            <?php echo Form::select('cst_ipi', $listaCST_IPI, $business->cst_ipi_padrao, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cenq_ipi', 'Cód. Enq. IPI' . ':*'); ?>

            <?php echo Form::select('cenq_ipi', $listaCenqIPI, 999, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">CFOP Estadual*:</label>
            <input required="required" value="<?php echo e($business->cfop_saida_estadual_padrao, false); ?>" class="form-control" data-mask="0000" placeholder="CFOP Estadual" name="cfop_interno" type="number" id="cfop_interno">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">CFOP Inter estadual*:</label>
            <input required="required" value="<?php echo e($business->cfop_saida_inter_estadual_padrao, false); ?>" class="form-control" data-mask="0000" placeholder="CFOP Inter estadual" name="cfop_externo" type="number" id="cfop_externo">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">NCM*:</label>
            <input required="required" value="<?php echo e($business->ncm_padrao, false); ?>" class="form-control" data-mask="0000.00.00" placeholder="NCM" name="ncm" type="text" id="ncm">
          </div>
        </div>

        <div class="col-sm-2" >
          <div class="form-group">
            <label for="product_custom_field2">CEST:</label>
            <input class="form-control" placeholder="CEST" name="cest" type="number" id="cest">
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('origem', 'Origem' . ':'); ?>

            <?php echo Form::select('origem', App\Models\Product::listaOrigem(), '', ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-5">
          <div class="form-group">
            <?php echo Form::label('codigo_anp', 'ANP' . ':'); ?>

            <?php echo Form::select('codigo_anp', App\Models\Product::lista_ANP(), '', ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_glp', '% GLP' . ':'); ?>

            <?php echo Form::text('perc_glp', '', ['class' => 'form-control',
            'placeholder' => '% GLP', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_gnn', '% GNn' . ':'); ?>

            <?php echo Form::text('perc_gnn', '', ['class' => 'form-control',
            'placeholder' => '% GNn', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_gni', '% GNi' . ':'); ?>

            <?php echo Form::text('perc_gni', '', ['class' => 'form-control',
            'placeholder' => '% GNi', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('valor_partida', 'Valor partida' . ':'); ?>

            <?php echo Form::text('valor_partida', '', ['class' => 'form-control',
            'placeholder' => 'Valor partida', 'data-mask="000000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('unidade_tributavel', 'Un. tributável' . ':'); ?>

            <?php echo Form::text('unidade_tributavel', '', ['class' => 'form-control',
            'placeholder' => 'Un. tributável', 'data-mask="AAAA"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('quantidade_tributavel', 'Qtd. tributável' . ':'); ?>

            <?php echo Form::text('quantidade_tributavel', '', ['class' => 'form-control',
            'placeholder' => 'Qtd. tributável']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('tipo', 'Tipo' . ':'); ?>

            <?php echo Form::select('tipo', ['normal' => 'Normal', 'veiculo' => 'Veiculo'] , '', ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_icms_interestadual', '%ICMS interestadual' . ':'); ?>

            <?php echo Form::tel('perc_icms_interestadual', '', ['class' => 'form-control',
            'placeholder' => '%ICMS interestadual', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_icms_interno', '%ICMS interno' . ':'); ?>

            <?php echo Form::tel('perc_icms_interno', '', ['class' => 'form-control',
            'placeholder' => '%ICMS interno', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_fcp_interestadual', '%FCP interestadual' . ':'); ?>

            <?php echo Form::tel('perc_fcp_interestadual', '', ['class' => 'form-control',
            'placeholder' => '%FCP interestadual', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('modBC', 'Modalidade Det.' . ':'); ?>

            <?php echo Form::select('modBC', App\Models\Product::modalidadesDeterminacao(), '', ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('modBCST', 'Modalidade Det. ST' . ':'); ?>

            <?php echo Form::select('modBCST', App\Models\Product::modalidadesDeterminacaoST(), '', ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('pICMSST', '%ICMS ST' . ':'); ?>

            <?php echo Form::tel('pICMSST', '', ['class' => 'form-control',
            'placeholder' => '%ICMS ST', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="clearfix"></div>

        <div class="veiculo" style="display: none">
          <?php $__env->startComponent('components.widget', ['class' => 'box-danger']); ?>
          <div class="col-sm-12">
            <h4>Dados Veículo:</h4>
          </div>
          
          

          <div class="col-sm-6">
            <div class="form-group">
              <?php echo Form::label('veicProd', 'Detalhamento de Veículo' . ':'); ?>

              <?php echo Form::text('veicProd', '', ['class' => 'form-control',
              'placeholder' => 'Detalhamento de Veículo']); ?>

            </div>
          </div>
          
          
          

          <div class="col-sm-6">
            <div class="form-group">
              <?php echo Form::label('tpOp', 'Tipo da operação' . ':'); ?>

              <?php echo Form::select('tpOp', App\Models\Veiculo::tiposOperacao() , '', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('chassi', 'Chassi' . ':'); ?>

              <?php echo Form::text('chassi', '', ['class' => 'form-control',
              'placeholder' => 'Chassi']); ?>

            </div>
          </div>
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cCor', 'Código da cor' . ':'); ?>

              <?php echo Form::select('cCor',  App\Models\Veiculo::cores(), '', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

              
              
            </div>
          </div>
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('xCor', 'Descrição da cor' . ':'); ?>

              <?php echo Form::text('xCor', '', ['class' => 'form-control',
              'placeholder' => 'Descrição da cor']); ?>

            </div>
          </div>
          
                 
          
          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cCorDENATRAN', 'Cor' . ':'); ?>

              <?php echo Form::select('cCorDENATRAN', App\Models\Veiculo::cores(), '', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpPint', 'Tipo de pintura' . ':'); ?>

              <?php echo Form::select('tpPint', App\Models\Veiculo::tiposPintura() , 'S', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
           
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cilin', 'Cilindradas' . ':'); ?>

              <?php echo Form::text('cilin', '0', ['class' => 'form-control',
              'placeholder' => 'Cilindradas']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pesoL', 'Peso líquido' . ':'); ?>

              <?php echo Form::text('pesoL', '', ['class' => 'form-control',
              'placeholder' => 'Peso líquido']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pesoB', 'Peso bruto' . ':'); ?>

              <?php echo Form::text('pesoB', '', ['class' => 'form-control',
              'placeholder' => 'Peso bruto']); ?>

            </div>
          </div>

         

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpComb', 'Combustível' . ':'); ?>

              <?php echo Form::select('tpComb', App\Models\Veiculo::tiposCompustivel() , '11', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
           <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('nSerie', 'Nº série' . ':'); ?>

              <?php echo Form::text('nSerie', '0', ['class' => 'form-control',
              'placeholder' => 'Nº série']); ?>

            </div>
          </div>
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('nMotor', 'Nº motor' . ':'); ?>

              <?php echo Form::text('nMotor', '0', ['class' => 'form-control',
              'placeholder' => 'Nº série']); ?>

            </div>
          </div>

          
       
          
          
          
          
              <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('espVeic', 'Espécie' . ':'); ?>

              <?php echo Form::select('espVeic', App\Models\Veiculo::especies(), '2', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
          
          
          
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('dist', 'Distância eixos' . ':'); ?>

              <?php echo Form::text('dist', '0.00', ['class' => 'form-control',
              'placeholder' => 'Distância entre eixos']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('anoMod', 'Ano Modelo de Fab' . ':'); ?>

              <?php echo Form::text('anoMod', '', ['class' => 'form-control',
              'placeholder' => 'Ano Modelo de Fabricação ']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('anoFab', 'Ano Fabricação' . ':'); ?>

              <?php echo Form::text('anoFab', '', ['class' => 'form-control',
              'placeholder' => 'Ano de Fabricação ']); ?>

            </div>
          </div>



          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpVeic', 'Tipo veiculo' . ':'); ?>

              <?php echo Form::select('tpVeic', App\Models\Veiculo::tipos(), '10', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          
          
          
          
            <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpRest', 'Restrição' . ':'); ?>

              <?php echo Form::select('tpRest', App\Models\Veiculo::restricoes(), '0', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('VIN', 'Condição do VIN' . ':'); ?>

              <?php echo Form::select('VIN', ['R' => 'Remarcado', 'N' => 'Normal'], 'N', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
          
          
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('condVeic', 'Condição do Veículo' . ':'); ?>

              <?php echo Form::select('condVeic', ['1' => 'Acabado', '2' => 'Inacabado', '3' => 'Semiacabado'], '', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cMod', 'Código Marca Modelo' . ':'); ?>

              <?php echo Form::text('cMod', '', ['class' => 'form-control',
              'placeholder' => 'Código Marca Modelo']); ?>

            </div>
          </div>
          
                 
          
          
           <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pot', 'Potência Motor (CV)' . ':'); ?>

              <?php echo Form::text('pot', '0', ['class' => 'form-control',
              'placeholder' => 'Potência Motor (CV)']); ?>

            </div>
          </div>
          
                  

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('lota', 'Capacidade lotação' . ':'); ?>

              <?php echo Form::text('lota', '0', ['class' => 'form-control',
              'placeholder' => 'Capacidade de lotação']); ?>

            </div>
          </div>

        
          
          
          
          
               <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('CMT', 'Capacidade Tração' . ':'); ?>

              <?php echo Form::text('CMT', '0.00', ['class' => 'form-control',
              'placeholder' => 'Capacidade Máxima de Tração']); ?>

            </div>
          </div>
          

          <?php echo $__env->renderComponent(); ?>
        </div>

        <div class="form-group col-sm-12" id="product_form_part">
          <?php echo $__env->make('product.partials.single_product_form_part', ['profit_percent' => $default_profit_percent], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <input type="hidden" id="variation_counter" value="1">
        <input type="hidden" id="default_profit_percent"
        value="<?php echo e($default_profit_percent, false); ?>">

      </div>


      <?php echo $__env->renderComponent(); ?>
      <div class="row">
        <div class="col-sm-12">
          <input type="hidden" name="submit_type" id="submit_type">
          <div class="text-center">
            <div class="btn-group">
              <?php if($selling_price_group_count): ?>
              <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_selling_price_group_prices'); ?></button>
              <?php endif; ?>

              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.opening_stock')): ?>
              <button id="opening_stock_button" <?php if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0): ?> disabled <?php endif; ?> type="submit" value="submit_n_add_opening_stock" class="btn bg-purple submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_opening_stock'); ?></button>
              <?php endif; ?>

              <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_another'); ?></button>

              <button type="submit" value="submit" class="btn btn-primary submit_product_form"><?php echo app('translator')->get('messages.save'); ?></button>
            </div>

          </div>
        </div>
      </div>
      <?php echo Form::close(); ?>


    </section>
    <!-- /.content -->

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('javascript'); ?>
    <?php $asset_v = env('APP_VERSION'); ?>
    <script src="<?php echo e(asset('js/product.js?v=' . $asset_v), false); ?>"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                  $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                  console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
              });
      });


      $('#tipo').change(() => {
        let tipo = $('#tipo').val();
        if(tipo == 'veiculo'){
          $('.veiculo').css('display', 'block')
        }else{
          limpaDadosVeiculo()
        }
      })

      function limpaDadosVeiculo(){
        $('.veiculo').css('display', 'none')
      }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/product/create.blade.php ENDPATH**/ ?>