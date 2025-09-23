<?php $__env->startSection('title', __('product.edit_product')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo app('translator')->get('product.edit_product'); ?></h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
      <?php echo Form::open(['url' => action('ProductController@update' , [$product->id] ), 'method' => 'PUT', 'id' => 'product_add_form',
      'class' => 'product_form', 'files' => true ]); ?>

      <input type="hidden" id="product_id" value="<?php echo e($product->id, false); ?>">

      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('name', __('product.product_name') . ':*'); ?>

            <?php echo Form::text('name', $product->name, ['class' => 'form-control', 'required',
            'placeholder' => __('product.product_name')]); ?>

          </div>
        </div>

        <div class="col-sm-2 <?php if(!(session('business.enable_category') && session('business.enable_sub_category'))): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('sku', __('product.sku')  . ':*'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.sku') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::text('sku', $product->sku, ['class' => 'form-control',
            'placeholder' => __('product.sku'), 'required']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('barcode_type', __('product.barcode_type') . ':*'); ?>

            <?php echo Form::select('barcode_type', $barcode_types, $product->barcode_type, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('codigo_barras', 'Código de barras' . ':'); ?>

            <?php echo Form::text('codigo_barras', $product->codigo_barras, ['class' => 'form-control',
            'placeholder' => 'Código de barras']); ?>

          </div>
        </div>

        <!-- <div class="clearfix"></div> -->

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('unit_id', __('product.unit') . ':*'); ?>

            <div class="input-group">
              <?php echo Form::select('unit_id', $units, $product->unit_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); ?>

              <span class="input-group-btn">
                <button type="button" <?php if(!auth()->user()->can('unit.create')): ?> disabled <?php endif; ?> class="btn btn-default bg-white btn-flat quick_add_unit btn-modal" data-href="<?php echo e(action('UnitController@create', ['quick_add' => true]), false); ?>" title="<?php echo app('translator')->get('unit.add_unit'); ?>" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
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

            <select name="sub_unit_ids[]" class="form-control select2" multiple id="sub_unit_ids">
              <?php $__currentLoopData = $sub_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub_unit_id => $sub_unit_value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($sub_unit_id, false); ?>"
              <?php if(is_array($product->sub_unit_ids) &&in_array($sub_unit_id, $product->sub_unit_ids)): ?>   selected
              <?php endif; ?>
              ><?php echo e($sub_unit_value['name'], false); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>

        <div class="col-sm-4 <?php if(!session('business.enable_brand')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('brand_id', __('product.brand') . ':'); ?>

            <div class="input-group">
              <?php echo Form::select('brand_id', $brands, $product->brand_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

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

            <?php echo Form::select('category_id', $categories, $product->category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-4 <?php if(!(session('business.enable_category') && session('business.enable_sub_category'))): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('sub_category_id', __('product.sub_category')  . ':'); ?>

            <?php echo Form::select('sub_category_id', $sub_categories, $product->sub_category_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('product_locations', __('business.business_locations') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.product_location_help') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); ?>

          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              <?php echo Form::checkbox('enable_stock', 1, $product->enable_stock, ['class' => 'input-icheck', 'id' => 'enable_stock']); ?> <strong><?php echo app('translator')->get('product.manage_stock'); ?></strong>
            </label><?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.enable_stock') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?> <p class="help-block"><i><?php echo app('translator')->get('product.enable_stock_help'); ?></i></p>
          </div>
        </div>
        <div class="col-sm-4" id="alert_quantity_div" <?php if(!$product->enable_stock): ?> style="display:none" <?php endif; ?>>
          <div class="form-group">
            <?php echo Form::label('alert_quantity', __('product.alert_quantity') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.alert_quantity') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
            <?php echo Form::number('alert_quantity', $product->alert_quantity, ['class' => 'form-control',
            'placeholder' => __('product.alert_quantity') , 'min' => '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('image', __('lang_v1.product_image') . ':'); ?>

            <?php echo Form::file('image' ,['id' => 'upload_image', 'accept' => 'image/*']); ?>

            <small><p class="help-block"><?php echo app('translator')->get('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]); ?>. <?php echo app('translator')->get('lang_v1.aspect_ratio_should_be_1_1'); ?> <?php if(!empty($product->image)): ?> <br> <?php echo app('translator')->get('lang_v1.previous_image_will_be_replaced'); ?> <?php endif; ?></p></small>
          </div>
          <?php if($product->image): ?>
          <center><img src="<?php echo e($product->image_url, false); ?>" width="150"></center>
          <center><small><p class="help-block">Imagem atual</p></small></center>
          <?php endif; ?>
        </div>
        
        <?php if(!empty($common_settings['enable_product_warranty'])): ?>
        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('warranty_id', __('lang_v1.warranty') . ':'); ?>

            <?php echo Form::select('warranty_id', $warranties, $product->warranty_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); ?>

          </div>
        </div>
        <?php endif; ?>
        <!-- include module fields -->
        

      </div>
      <?php echo $__env->renderComponent(); ?>

      
      <?php echo e($product->nuvemshop_id, false); ?>

      <?php if(in_array('nuvemshop', $enabled_modules) && auth()->user()->can('ecommerce.view')): ?>
      <div class="box <?php if(!empty($class)): ?> <?php echo e($class, false); ?> <?php else: ?> box-primary <?php endif; ?>" id="accordionNuvem">
        <div class="box-header with-border" style="cursor: pointer;">
          <h3 class="box-title">
            <a data-toggle="collapse" data-parent="#accordionNuvem" href="#collapseFilter1">
              Nuvem Shop
            </a>
          </h3>
        </div>
        <div id="collapseFilter1" class="panel-collapse active <?php if(!$product->ecommerce): ?> collapse <?php endif; ?>" aria-expanded="true">
          <div class="box-body">
            <div class="row">
              <div class="col-sm-2">
                <div class="form-group">
                  <label>
                    <?php echo Form::checkbox('ecommerce', 1, !(empty($product)) ? $product->ecommerce : false, ['class' => 'input-icheck']); ?> <strong>Nuvem Shop</strong>
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

                  <?php echo Form::text('weight', !empty($product->weight) ? $product->weight : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight'), 'data-mask="000000.000"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('altura',  'Altura' . ':'); ?>

                  <?php echo Form::text('altura', $product->altura, ['class' => 'form-control', 'placeholder' => 'Altura', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('largura',  'Largura' . ':'); ?>

                  <?php echo Form::text('largura', $product->largura, ['class' => 'form-control', 'placeholder' => 'Largura', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('comprimento',  'Comprimento' . ':'); ?>

                  <?php echo Form::text('comprimento', $product->comprimento, ['class' => 'form-control', 'placeholder' => 'Comprimento', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>

              <div class="col-sm-2">
                <div class="form-group">
                  <?php echo Form::label('valor_ecommerce',  'Valor Nuvem Shop' . ':'); ?>

                  <?php echo Form::text('valor_ecommerce', $product->valor_ecommerce, ['class' => 'form-control', 'placeholder' => 'Valor ecommerce', 'data-mask="000000,00"', 'data-mask-reverse="true"']); ?>

                </div>
              </div>
              <div class="clearfix"></div>
              <div class="col-sm-8">
                <div class="form-group">
                  <?php echo Form::label('product_description', __('lang_v1.product_description') . ':'); ?>

                  <?php echo Form::textarea('product_description', $product->product_description, ['class' => 'form-control']); ?>

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
              <?php
              $disabled = false;
              $disabled_period = false;
              if( empty($product->expiry_period_type) || empty($product->enable_stock) ){
                $disabled = true;
              }
              if( empty($product->enable_stock) ){
                $disabled_period = true;
              }
              ?>
              <?php echo Form::label('expiry_period', __('product.expires_in') . ':'); ?><br>
              <?php echo Form::text('expiry_period', number_format($product->expiry_period, 2, ',', '.'), ['class' => 'form-control pull-left input_number',
              'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;', 'disabled' => $disabled]); ?>

              <?php echo Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], $product->expiry_period_type, ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type', 'disabled' => $disabled_period]); ?>

            </div>
          </div>
        </div>
        <?php endif; ?>
        <div class="col-sm-4">
          <div class="checkbox">
            <label>
              <?php echo Form::checkbox('enable_sr_no', 1, $product->enable_sr_no, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.enable_imei_or_sr_no'); ?></strong>
            </label>
            <?php
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
              <?php echo Form::checkbox('not_for_selling', 1, $product->not_for_selling, ['class' => 'input-icheck']); ?> <strong><?php echo app('translator')->get('lang_v1.not_for_selling'); ?></strong>
            </label> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.tooltip_not_for_selling') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
          </div>
        </div>



        <div class="clearfix"></div>

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



            <?php if(!empty($rack_details[$id])): ?>
            <?php if(session('business.enable_racks')): ?>
            <?php echo Form::text('product_racks_update[' . $id . '][rack]', $rack_details[$id]['rack'], ['class' => 'form-control', 'id' => 'rack_' . $id]); ?>

            <?php endif; ?>

            <?php if(session('business.enable_row')): ?>
            <?php echo Form::text('product_racks_update[' . $id . '][row]', $rack_details[$id]['row'], ['class' => 'form-control']); ?>

            <?php endif; ?>

            <?php if(session('business.enable_position')): ?>
            <?php echo Form::text('product_racks_update[' . $id . '][position]', $rack_details[$id]['position'], ['class' => 'form-control']); ?>

            <?php endif; ?>
            <?php else: ?>
            <?php echo Form::text('product_racks[' . $id . '][rack]', null, ['class' => 'form-control', 'id' => 'rack_' . $id, 'placeholder' => __('lang_v1.rack')]); ?>


            <?php echo Form::text('product_racks[' . $id . '][row]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); ?>


            <?php echo Form::text('product_racks[' . $id . '][position]', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); ?>

            <?php endif; ?>

          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>



        <div class="clearfix"></div>
        <?php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
        $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
        $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
        $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
        ?>
        <!--custom fields-->
        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field1',  $product_custom_field1 . ':'); ?>

            <?php echo Form::text('product_custom_field1', $product->product_custom_field1, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field2',  $product_custom_field2 . ':'); ?>

            <?php echo Form::text('product_custom_field2', $product->product_custom_field2, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field3',  $product_custom_field3 . ':'); ?>

            <?php echo Form::text('product_custom_field3', $product->product_custom_field3, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('product_custom_field4',  $product_custom_field4 . ':'); ?>

            <?php echo Form::text('product_custom_field4', $product->product_custom_field4, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); ?>

          </div>
        </div>
        <!--custom fields-->
        <?php echo $__env->make('layouts.partials.module_form_part', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
      <?php echo $__env->renderComponent(); ?>

      <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
      <div class="row">
        <div class="col-sm-3 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('tax', __('product.applicable_tax') . ':'); ?>

            <?php echo Form::select('tax', $taxes, $product->tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); ?>

          </div>
        </div>

        <div class="col-sm-3 <?php if(!session('business.enable_price_tax')): ?> hide <?php endif; ?>">
          <div class="form-group">
            <?php echo Form::label('tax_type', __('product.selling_price_tax_type') . ':*'); ?>

            <?php echo Form::select('tax_type',['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], $product->tax_type,
            ['class' => 'form-control select2', 'required']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="">Cód Benefício fiscal:</label>
            <input value="<?php echo e($product->cBenef, false); ?>" class="form-control" placeholder="" data-mask="AAAAAAAAAA" name="cBenef" data-mask-reverse="true" type="text" id="cBenef">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">% Red BC:</label>
            <input value="<?php echo e($product->pRedBC, false); ?>" class="form-control" placeholder="%Red BC" data-mask="000.00" data-mask-reverse="true" name="pRedBC" type="text" id="pRedBC">
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
            <?php echo Form::select('type', $product_types, $product->type, ['class' => 'form-control select2',
            'required','disabled', 'data-action' => 'edit', 'data-product_id' => $product->id ]); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%ICMS:</label>
            <input class="form-control" value="<?php echo e($product->perc_icms, false); ?>" data-mask="00.00" data-mask-reverse="true" placeholder="%ICMS" name="perc_icms" type="text" id="perc_icms">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%PIS:</label>
            <input class="form-control" value="<?php echo e($product->perc_pis, false); ?>" data-mask="00.00" data-mask-reverse="true" placeholder="%PIS" name="perc_pis" type="text" id="perc_pis">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%COFINS:</label>
            <input class="form-control" value="<?php echo e($product->perc_cofins, false); ?>" data-mask-reverse="true" data-mask="00.00" placeholder="%COFINS" name="perc_cofins" type="text" id="perc_cofins">
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">%IPI:</label>
            <input class="form-control" value="<?php echo e($product->perc_ipi, false); ?>" data-mask="00.00" data-mask-reverse="true" placeholder="%IPI" name="perc_ipi" type="text" id="perc_ipi">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <?php echo Form::label('cst_csosn', 'CST/CSOSN' . ':*'); ?>

            <?php echo Form::select('cst_csosn', $listaCSTCSOSN, $product->cst_csosn, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <?php echo Form::label('cst_pis', 'CST/PIS' . ':*'); ?> 
            <?php echo Form::select('cst_pis', $listaCST_PIS_COFINS, $product->cst_pis, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cst_cofins', 'CST/COFINS' . ':*'); ?> 
            <?php echo Form::select('cst_cofins', $listaCST_PIS_COFINS, $product->cst_cofins, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cst_ipi', 'CST/IPI' . ':*'); ?> 
            <?php echo Form::select('cst_ipi', $listaCST_IPI, $product->cst_ipi, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('cenq_ipi', 'Cód. Enq. IPI' . ':*'); ?>

            <?php echo Form::select('cenq_ipi', $listaCenqIPI, $product->cenq_ipi, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">CFOP Estadual*:</label>
            <input  required="required" value="<?php echo e($product->cfop_interno, false); ?>" class="form-control" data-mask="0000" placeholder="CFOP Estadual" name="cfop_interno" type="text" id="cfop_interno">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">CFOP Inter estadual*:</label>
            <input required="required" value="<?php echo e($product->cfop_externo, false); ?>"  class="form-control" data-mask="0000" placeholder="CFOP Inter estadual" name="cfop_externo" type="text" id="cfop_externo">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">NCM*:</label>
            <input required="required" value="<?php echo e($product->ncm, false); ?>"  class="form-control" data-mask="0000.00.00" placeholder="NCM" name="ncm" type="text" id="ncm">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label for="product_custom_field2">CEST:</label>
            <input class="form-control" value="<?php echo e($product->cest, false); ?>" placeholder="CEST" name="cest" type="number" id="cest">
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <?php echo Form::label('origem', 'Origem' . ':'); ?>

            <?php echo Form::select('origem', App\Models\Product::listaOrigem(), $product->origem, ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-5">
          <div class="form-group">
            <?php echo Form::label('codigo_anp', 'ANP' . ':'); ?>

            <?php echo Form::select('codigo_anp', App\Models\Product::lista_ANP(), $product->codigo_anp, ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_glp', '% GLP' . ':'); ?>

            <?php echo Form::text('perc_glp', $product->perc_glp, ['class' => 'form-control',
            'placeholder' => '% GLP', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_gnn', '% GNn' . ':'); ?>

            <?php echo Form::text('perc_gnn', $product->perc_gnn, ['class' => 'form-control',
            'placeholder' => '% GNn', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_gni', '% GNi' . ':'); ?>

            <?php echo Form::text('perc_gni', $product->perc_gni, ['class' => 'form-control',
            'placeholder' => '% GNi', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('valor_partida', 'Valor partida' . ':'); ?>

            <?php echo Form::text('valor_partida', $product->valor_partida, ['class' => 'form-control',
            'placeholder' => 'Valor partida', 'data-mask="000000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('unidade_tributavel', 'Un. tributável' . ':'); ?>

            <?php echo Form::text('unidade_tributavel', $product->unidade_tributavel, ['class' => 'form-control',
            'placeholder' => 'Un. tributável', 'data-mask="AAAA"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('quantidade_tributavel', 'Qtd. tributável' . ':'); ?>

            <?php echo Form::text('quantidade_tributavel', $product->quantidade_tributavel, ['class' => 'form-control',
            'placeholder' => 'Qtd. tributável']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('tipo', 'Tipo' . ':'); ?>

            <?php echo Form::select('tipo', ['normal' => 'Normal', 'veiculo' => 'Veiculo'] , $product->tipo, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_icms_interestadual', '%ICMS interestadual' . ':'); ?>

            <?php echo Form::tel('perc_icms_interestadual', $product->perc_icms_interestadual, ['class' => 'form-control',
            'placeholder' => '%ICMS interestadual', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_icms_interno', '%ICMS interno' . ':'); ?>

            <?php echo Form::tel('perc_icms_interno', $product->perc_icms_interno, ['class' => 'form-control',
            'placeholder' => '%ICMS interno', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('perc_fcp_interestadual', '%FCP interestadual' . ':'); ?>

            <?php echo Form::tel('perc_fcp_interestadual', $product->perc_fcp_interestadual, ['class' => 'form-control',
            'placeholder' => '%FCP interestadual', 'data-mask="000.00"', 'data-mask-reverse="true"']); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('modBC', 'Modalidade Det.' . ':'); ?>

            <?php echo Form::select('modBC', App\Models\Product::modalidadesDeterminacao(), $product->modBC, ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <?php echo Form::label('modBCST', 'Modalidade Det. ST' . ':'); ?>

            <?php echo Form::select('modBCST', App\Models\Product::modalidadesDeterminacaoST(), $product->modBCST, ['class' => 'form-control select2']); ?>

          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <?php echo Form::label('pICMSST', '%ICMS ST' . ':'); ?>

            <?php echo Form::tel('pICMSST', $product->pICMSST, ['class' => 'form-control',
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

              <?php echo Form::text('veicProd', $product->veicProd, ['class' => 'form-control',
              'placeholder' => 'Detalhamento de Veículo']); ?>

            </div>
          </div>
          
          
          

          <div class="col-sm-4">
            <div class="form-group">
              <?php echo Form::label('tpOp', 'Tipo da operação' . ':'); ?>

              <?php echo Form::select('tpOp', App\Models\Veiculo::tiposOperacao() , $product->tpOp, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('chassi', 'Chassi' . ':'); ?>

              <?php echo Form::text('chassi', $product->chassi, ['class' => 'form-control',
              'placeholder' => 'Chassi']); ?>

            </div>
          </div>
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cCor', 'Código da cor' . ':'); ?>

              <?php echo Form::select('cCor',  App\Models\Veiculo::cores(), $product->cCor, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

              
              
            </div>
          </div>
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('xCor', 'Descrição da cor' . ':'); ?>

              <?php echo Form::text('xCor', $product->xCor, ['class' => 'form-control',
              'placeholder' => 'Descrição da cor']); ?>

            </div>
          </div>
          
                 
          
          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cCorDENATRAN', 'Cor' . ':'); ?>

              <?php echo Form::select('cCorDENATRAN', App\Models\Veiculo::cores(), $product->cCorDENATRAN, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          
          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpPint', 'Tipo de pintura' . ':'); ?>

              <?php echo Form::select('tpPint', App\Models\Veiculo::tiposPintura() , $product->tpPint, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
           
          

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cilin', 'Cilindradas' . ':'); ?>

              <?php echo Form::text('cilin', $product->cilin, ['class' => 'form-control',
              'placeholder' => 'Cilindradas']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pesoL', 'Peso líquido' . ':'); ?>

              <?php echo Form::text('pesoL', $product->pesoL, ['class' => 'form-control',
              'placeholder' => 'Peso líquido']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pesoB', 'Peso bruto' . ':'); ?>

              <?php echo Form::text('pesoB', $product->pesoB, ['class' => 'form-control',
              'placeholder' => 'Peso bruto']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('nSerie', 'Nº série' . ':'); ?>

              <?php echo Form::text('nSerie', $product->nSerie, ['class' => 'form-control',
              'placeholder' => 'Nº série']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpComb', 'Tipo de combustível' . ':'); ?>

              <?php echo Form::select('tpComb', App\Models\Veiculo::tiposCompustivel() , $product->tpComb, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('nMotor', 'Nº motor' . ':'); ?>

              <?php echo Form::text('nMotor', $product->nMotor, ['class' => 'form-control',
              'placeholder' => 'Nº série']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('CMT', 'Capacidade Máxima de Tração' . ':'); ?>

              <?php echo Form::text('CMT', $product->nMotor, ['class' => 'form-control',
              'placeholder' => 'Capacidade Máxima de Tração']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('dist', 'Distância entre eixos' . ':'); ?>

              <?php echo Form::text('dist', $product->dist, ['class' => 'form-control',
              'placeholder' => 'Distância entre eixos']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('anoMod', 'Ano Modelo de Fab' . ':'); ?>

              <?php echo Form::text('anoMod', $product->anoMod, ['class' => 'form-control',
              'placeholder' => 'Ano Modelo de Fabricação ']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('anoFab', 'Ano de Fabricação' . ':'); ?>

              <?php echo Form::text('anoFab', $product->anoFab, ['class' => 'form-control',
              'placeholder' => 'Ano de Fabricação ']); ?>

            </div>
          </div>



          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('tpVeic', 'Tipo do veiculo' . ':'); ?>

              <?php echo Form::select('tpVeic', App\Models\Veiculo::tipos(), $product->tpVeic, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('espVeic', 'Espécie' . ':'); ?>

              <?php echo Form::select('espVeic', App\Models\Veiculo::especies(), $product->espVeic, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('VIN', 'Condição do VIN' . ':'); ?>

              <?php echo Form::select('VIN', ['R' => 'Remarcado', 'N' => 'Normal'], $product->VIN, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('condVeic', 'Condição do Veículo' . ':'); ?>

              <?php echo Form::select('condVeic', ['1' => 'Acabado', '2' => 'Inacabado', '3' => 'Semiacabado'], $product->condVeic, ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('cMod', 'Código Marca Modelo' . ':'); ?>

              <?php echo Form::text('cMod', $product->cMod, ['class' => 'form-control',
              'placeholder' => 'Código Marca Modelo']); ?>

            </div>
          </div>
          
                 
          
          
           <div class="col-sm-2">
            <div class="form-group">
              <?php echo Form::label('pot', 'Potência Motor (CV)' . ':'); ?>

              <?php echo Form::text('pot', $product->pot, ['class' => 'form-control',
              'placeholder' => 'Potência Motor (CV)']); ?>

            </div>
          </div>
          
                  

          <div class="col-sm-3">
            <div class="form-group">
              <?php echo Form::label('lota', 'Capacidade de lotação' . ':'); ?>

              <?php echo Form::text('lota', $product->lota, ['class' => 'form-control',
              'placeholder' => 'Capacidade de lotação']); ?>

            </div>
          </div>

          <div class="col-sm-3">
            <div class="form-group">
              <?php echo Form::label('tpRest', 'Tipo de restrição' . ':'); ?>

              <?php echo Form::select('tpRest', App\Models\Veiculo::restricoes(), '0', ['class' => 'form-control select2', 'style' => 'width: 100%']); ?>

            </div>
          </div>
          
          
          

          <?php echo $__env->renderComponent(); ?>

        </div>

        <div class="form-group col-sm-12" id="product_form_part"></div>
        <input type="hidden" id="variation_counter" value="0">
        <input type="hidden" id="default_profit_percent" value="<?php echo e($default_profit_percent, false); ?>">
      </div>
      <?php echo $__env->renderComponent(); ?>


      <input style="visibility: hidden" value="<?php echo e($product->image, false); ?>" id="image_temp">


      <div class="row">
        <input type="hidden" name="submit_type" id="submit_type">
        <div class="col-sm-12">
          <div class="text-center">
            <div class="btn-group">
              <?php if($selling_price_group_count): ?>
              <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form"><?php echo app('translator')->get('lang_v1.save_n_add_selling_price_group_prices'); ?></button>
              <?php endif; ?>

              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.opening_stock')): ?>
              <button type="submit" <?php if(empty($product->enable_stock)): ?> disabled="true" <?php endif; ?> id="opening_stock_button"  value="update_n_edit_opening_stock" class="btn bg-purple submit_product_form"><?php echo app('translator')->get('lang_v1.update_n_edit_opening_stock'); ?></button>
              <?php endif; ?>

              <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form"><?php echo app('translator')->get('lang_v1.update_n_add_another'); ?></button>

              <button type="submit" value="submit" class="btn btn-primary submit_product_form"><?php echo app('translator')->get('messages.update'); ?></button>
            </div>
          </div>
        </div>
      </div>
      <?php echo Form::close(); ?>

    </section>
    <!-- /.content -->

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('javascript'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    
    <script src="<?php echo e(asset('js/product.js?v=' . $asset_v), false); ?>"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        let tipo = $('#tipo').val();
        if(tipo == 'veiculo'){
          $('.veiculo').css('display', 'block')
        }
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

    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/product/edit.blade.php ENDPATH**/ ?>