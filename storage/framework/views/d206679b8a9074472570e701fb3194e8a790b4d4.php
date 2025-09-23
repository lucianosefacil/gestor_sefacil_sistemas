<?php if(request()->query('status') === 'locacao'): ?>
<?php $__env->startSection('title', __('Adicionar Locação')); ?>
<?php else: ?>
<?php $__env->startSection('title', __('sale.add_sale')); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
<!-- Content Header (Page header) -->
<section class="content-header">

    <?php if(request()->query('status') === 'locacao'): ?>
        <h1><?php echo app('translator')->get('Adicionar Locação'); ?></h1>
    <?php else: ?>
        <h1><?php echo app('translator')->get('sale.add_sale'); ?></h1>
    <?php endif; ?>

    
</section>
<!-- Main content -->
<section class="content no-print">
    <input type="hidden" id="amount_rounding_method" value="<?php echo e($pos_settings['amount_rounding_method'] ?? '', false); ?>">

    <input type="hidden" name="tipo_venda" id="tipo_venda" value="<?php echo e($tipo_venda, false); ?>">

    <?php if(!empty($pos_settings['allow_overselling'])): ?>
    <input type="hidden" id="is_overselling_allowed">
    <?php endif; ?>
    <?php if(session('business.enable_rp') == 1): ?>
    <input type="hidden" id="reward_point_enabled">
    <?php endif; ?>
    <?php if(is_null($default_location)): ?>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    <?php echo Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm',
                    'placeholder' => __('lang_v1.select_location'),
                    'id' => 'select_location_id',
                    'required', 'autofocus'], $bl_attributes); ?>

                    <span class="input-group-addon">
                        <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.sale_location') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <input type="hidden" id="item_addition_method" value="<?php echo e($business_details->item_addition_method, false); ?>">
    <?php echo Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_sell_form' ]); ?>

    <div class="row">
        <div class="col-md-12 col-sm-12">
            <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
            <?php echo Form::hidden('location_id', !empty($default_location) ? $default_location->id : null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '']); ?>


            <?php if(!empty($price_groups)): ?>
            <?php if(count($price_groups) > 1): ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        <?php
                        reset($price_groups);
                        ?>
                        <?php echo Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']); ?>

                        <?php echo Form::select('price_group', $price_groups, null, ['class' => 'form-control select2', 'id' => 'price_group']); ?>

                        <span class="input-group-addon">
                            <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.price_group_help_text') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <?php
            reset($price_groups);
            ?>
            <?php echo Form::hidden('price_group', key($price_groups), ['id' => 'price_group']); ?>

            <?php endif; ?>
            <?php endif; ?>

            <?php echo Form::hidden('default_price_group', null, ['id' => 'default_price_group']); ?>


            <?php if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
                        </span>
                        <?php echo Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => 'Tipo de serviço']); ?>


                        <?php echo Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']); ?>


                        <span class="input-group-addon">
                            <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . 'Tipo de serviço significa serviços como jantares, encomendas, entrega ao domicílio, entrega a terceiros, etc.' . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                        </span>
                    </div>


                    <small>
                        <p class="help-block hide" id="price_group_text"><?php echo app('translator')->get('lang_v1.price_group'); ?>: <span></span></p>
                    </small>
                </div>
            </div>
            <div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
            <?php endif; ?>

            <?php if(in_array('subscription', $enabled_modules)): ?>
            <div class="col-md-4 pull-right col-sm-6">
                <div class="checkbox">
                    <label>
                        <?php echo Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); ?> <?php echo app('translator')->get('lang_v1.subscribe'); ?>?
                    </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button><?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('lang_v1.recurring_invoice_help') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($isOrdemServico)): ?>
            <input type="hidden" name="ordem_servico_id" value="<?php echo e($ordem->id, false); ?>">
            <?php endif; ?>

            <div class="clearfix"></div>
            <div class="<?php if(!empty($commission_agent)): ?> col-sm-3 <?php else: ?> col-sm-4 <?php endif; ?>">
                <div class="form-group">
                    <?php echo Form::label('contact_id', __('contact.customer') . ':*'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>

                        <?php if(isset($isOrdemServico)): ?>
                        <input type="hidden" id="default_customer_id" 
						value="<?php echo e($ordem->cliente->id, false); ?>" >
						<input type="hidden" id="default_customer_name" 
						value="<?php echo e($ordem->cliente->name, false); ?>">

                        <?php else: ?>

                        <input type="hidden" id="default_customer_id" value="<?php echo e($walk_in_customer['id'], false); ?>">
                        <input type="hidden" id="default_customer_name" value="<?php echo e($walk_in_customer['name'], false); ?>">
                        <?php endif; ?>

                        <?php echo Form::select('contact_id', [], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Entre com nome do cliente', 'required']); ?>

                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>



            <?php if(!empty($commission_agent)): ?>
            <div class="col-sm-3">
                <div class="form-group">
                    <?php echo Form::label('commission_agent', __('lang_v1.commission_agent') . ':'); ?>

                    <?php echo Form::select('commission_agent',
                    $commission_agent, null, ['class' => 'form-control select2']); ?>

                </div>
            </div>
            <?php endif; ?>

            <div class="<?php if(!empty($commission_agent)): ?> col-sm-3 <?php else: ?> col-sm-4 <?php endif; ?>">
                <div class="form-group">
                    
                    <?php echo Form::label('transaction_date', request()->query('status') === 'locacao' ? __('Data da Locação') . ':*' : __('sale.sale_date') . ':*'); ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <?php echo Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); ?>

                    </div>
                </div>
            </div>
            <!-- STATUS SELL -->
            <div class="<?php if(!empty($commission_agent)): ?> col-sm-3 <?php else: ?> col-sm-4 <?php endif; ?>">
                <div class="form-group">
                    <?php echo Form::label('status', __('sale.status') . ':*'); ?>

                    <?php if(request()->query('status') === 'locacao'): ?>
                    <?php echo Form::select('status', ['locacao' => 'Locação'], 'locacao', ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); ?>

                    <?php else: ?>
                    <?php echo Form::select('status', ['final' => 'Final', 'draft' => __('sale.draft'), 'quotation' => __('lang_v1.quotation')], $status ?? 'final', ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); ?>

                    <?php endif; ?>
                </div>
            </div>
            <!-- END STATUS SELL -->


            <div class="col-sm-3">
                <div class="form-group">
                    <?php echo Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':'); ?>

                    <?php echo Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control select2', 'required']); ?>

                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <?php echo Form::label('natureza_id', 'Natureza de Operação'. ':*'); ?>

                    <?php echo Form::select('natureza_id', $naturezas, 'VENDA', ['class' => 'form-control select2', 'required']); ?>


                </div>
            </div>

            <div class="clearfix"></div>
            <!-- Call restaurant module if defined -->
            <?php if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules)): ?>
            <span id="restaurant_module_span">
                <div class="col-md-3"></div>
            </span>
            <?php endif; ?>
            <div class="col-sm-3" style="visibility: hidden">
                <div class="form-group">
                    <div class="multi-input">
                        <?php echo Form::label('pay_term_number', __('contact.pay_term') . ':'); ?> <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.pay_term') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                        <br />
                        <?php echo Form::number('pay_term_number', $walk_in_customer['pay_term_number'], ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); ?>


                        <?php echo Form::select('pay_term_type',
                        ['months' => __('lang_v1.months'),
                        'days' => __('lang_v1.days')],
                        $walk_in_customer['pay_term_type'],
                        ['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); ?>

                    </div>
                </div>
            </div>
            <?php echo $__env->renderComponent(); ?>

            <?php $__env->startComponent('components.widget', [
                'class' => 'box-primary', 
                'title' => request()->query('status') === 'locacao' ? 'Produtos da Locação' : 'Produtos da Venda'
            ]); ?>
            <div class="col-sm-10 col-sm-offset-1">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="<?php echo e(__('lang_v1.configure_product_search'), false); ?>"><i class="fa fa-barcode"></i></button>
                        </div>
                        <?php echo Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
                        'disabled' => is_null($default_location)? true : false,
                        'autofocus' => is_null($default_location)? false : true,
                        ]); ?>

                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="<?php echo e(action('ProductController@quickAdd'), false); ?>" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row col-sm-12 pos_product_div" style="min-height: 0">
                <input type="hidden" name="sell_price_tax" id="sell_price_tax" value="<?php echo e($business_details->sell_price_tax, false); ?>">
                <!-- Keeps count of product rows -->
                <input type="hidden" id="product_row_count" value="0">
                <?php
                $hide_tax = '';
                if( session()->get('business.enable_inline_tax') == 0){
                $hide_tax = 'hide';
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                        <thead>
                            <tr>
                                
                                <th class="text-center">
                                    <?php echo app('translator')->get('sale.product'); ?>
                                </th>
                                <th class="text-center">
                                    <?php echo app('translator')->get('sale.qty'); ?>
                                </th>
                                <?php if(!empty($pos_settings['inline_service_staff'])): ?>
                                <th class="text-center">
                                    <?php echo app('translator')->get('restaurant.service_staff'); ?>
                                </th>
                                <?php endif; ?>
                                <th class="text-center <?php echo e($hide_tax, false); ?>">
                                    <?php echo app('translator')->get('sale.price_inc_tax'); ?>
                                </th>
                                <th class="text-center">
                                    <?php echo app('translator')->get('sale.subtotal'); ?>
                                </th>
                                <th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($ordem)): ?>
                            <?php echo $__env->make('sell.partials.produto_os', ['ordem' => $ordem], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered table-striped">
                        <tr>
                            <td>
                                <div class="pull-right">
                                    <b><?php echo app('translator')->get('sale.item'); ?>:</b>
                                    <span class="total_quantity">0</span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <b><?php echo app('translator')->get('sale.total'); ?>: </b>
                                    <span class="price_total">0</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php echo $__env->renderComponent(); ?>


            <!-- aqui desconto -->

            <div class="box <?php if(!empty($class)): ?> <?php echo e($class, false); ?> <?php else: ?> box-primary <?php endif; ?>" id="accordion">
                <div class="box-header with-border" style="cursor: pointer;">
                    <h3 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseDesconto">
                            Desconto
                        </a>
                    </h3>
                </div>
                <div id="collapseDesconto" class="panel-collapse active collapse" aria-expanded="true">
                    <div class="box-body">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('discount_type', 'Tipo do desconto*' ); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    <?php echo Form::select('discount_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'percentage' , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'percentage']); ?>

                                </div>
                            </div>
                        </div>
                        <?php
                        $max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';

                        //if sale discount is more than user max discount change it to max discount
                        $sales_discount = $business_details->default_sales_discount;
                        if($max_discount != '' && $sales_discount > $max_discount) $sales_discount = $max_discount;

                        ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('discount_amount', __('sale.discount_amount') . ':*' ); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    <?php echo Form::text('discount_amount', number_format($sales_discount, 2, ',', '.'), ['class' => 'form-control input_number', 'data-default' => $sales_discount, 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? number_format($max_discount, 2, ',', '.') : '']) ]); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4"><br>
                            <b><?php echo app('translator')->get( 'sale.discount_amount' ); ?>:</b>(-)
                            <span class="display_currency" id="total_discount">0</span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12 well well-sm bg-light-gray <?php if(session('business.enable_rp') != 1): ?> hide <?php endif; ?>">
                            <input type="hidden" name="rp_redeemed" id="rp_redeemed" value="0">
                            <input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="0">
                            <div class="col-md-12">
                                <h4><?php echo e(session('business.rp_name'), false); ?></h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php echo Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ); ?>

                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-gift"></i>
                                        </span>
                                        <?php echo Form::number('rp_redeemed_modal', 0, ['class' => 'form-control direct_sell_rp_input', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'min' => 0, 'data-max_points' => 0, 'data-min_order_total' => session('business.min_order_total_for_redeem') ]); ?>

                                        <input type="hidden" id="rp_name" value="<?php echo e(session('business.rp_name'), false); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <p><strong><?php echo app('translator')->get('lang_v1.available'); ?>:</strong> <span id="available_rp">0</span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong><?php echo app('translator')->get('lang_v1.redeemed_amount'); ?>:</strong> (-)<span id="rp_redeemed_amount_text">0</span></p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('tax_rate_id', __('sale.order_tax') . ':*' ); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    <?php echo Form::select('tax_rate_id', $taxes['tax_rates'], $business_details->default_sales_tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $business_details->default_sales_tax], $taxes['attributes']); ?>


                                    <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount" value="<?php if(empty($edit)): ?> <?php echo e(number_format($business_details->tax_calculation_amount, 2, ',', '.'), false); ?> <?php else: ?> <?php echo e(number_format(optional($transaction->tax)->amount, 2, ',', '.'), false); ?> <?php endif; ?>" data-default="<?php echo e($business_details->tax_calculation_amount, false); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-md-offset-4">
                            <b><?php echo app('translator')->get( 'sale.order_tax' ); ?>:</b>(+)
                            <span class="display_currency" id="order_tax">0</span>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('shipping_details', 'Detalhes de envio'); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    <?php echo Form::textarea('shipping_details',null, ['class' => 'form-control','placeholder' => 'Detalhes de envio' ,'rows' => '1', 'cols'=>'30']); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('shipping_address', __('lang_v1.shipping_address')); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-map-marker"></i>
                                    </span>
                                    <?php echo Form::textarea('shipping_address',null, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '1', 'cols'=>'30']); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('shipping_charges', 'Custos de envio'); ?>

                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-info"></i>
                                    </span>
                                    <?php echo Form::text('shipping_charges',number_format(0.00, 2, ',', '.'),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('shipping_status', __('lang_v1.shipping_status')); ?>

                                <?php echo Form::select('shipping_status',$shipping_statuses, null, ['class' => 'form-control','placeholder' => __('messages.please_select')]); ?>

                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-4 col-md-offset-8">
                            <?php if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0): ?>
                            <small id="round_off"><br>(<?php echo app('translator')->get('lang_v1.round_off'); ?>: <span id="round_off_text">0</span>)</small>
                            <br />
                            <input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
                            <?php endif; ?>
                            <div><b><?php echo app('translator')->get('sale.total_payable'); ?>: </b>
                                <input type="hidden" name="final_total" id="final_total_input">
                                <span id="total_payable">0</span>
                            </div>
                        </div>


                        <input type="hidden" name="is_direct_sale" value="1">


                    </div>
                </div>
            </div>

            <!-- termina desconto -->


            <div class="box <?php if(!empty($class)): ?> <?php echo e($class, false); ?> <?php else: ?> box-primary <?php endif; ?>" id="accordion">
                <div class="box-header with-border" style="cursor: pointer;">
                    <h3 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
                            Transporte
                        </a>
                    </h3>
                </div>
                <div id="collapseFilter" class="panel-collapse active collapse" aria-expanded="true">
                    <div class="box-body">
                        <div class="col-md-2">
                            <div class="form-group">
                                <?php echo Form::label('placa', 'Placa:' ); ?>

                                <?php echo Form::text('placa', null, ['class' => 'form-control','placeholder' => 'placa',
                                'data-mask="AAA-AAAA"', 'data-mask-reverse="true"']); ?>

                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <?php echo Form::label('uf', 'UF:' ); ?>


                                <?php echo Form::select('uf', $ufs, 'uf' , ['class' => 'form-control select2','placeholder' => 'UF', 'data-default' => 'percentage']); ?>


                            </div>
                        </div>

                        <div class="col-md-2 col-sm-2">
                            <div class="form-group">
                                <?php echo Form::label('tipo', 'Tipo do frete:' ); ?>


                                <?php echo Form::select('tipo', $tiposFrete, 'tipo' , ['class' => 'form-control', 'data-default' => 'percentage']); ?>


                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?php echo Form::label('peso_liquido', 'Peso liquido:' ); ?>

                                <?php echo Form::text('peso_liquido', null, ['class' => 'form-control','placeholder' => 'Peso liquido', 'data-mask="00000000.000"', 'data-mask-reverse="true"']); ?>

                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?php echo Form::label('peso_bruto', 'Peso bruto:' ); ?>

                                <?php echo Form::text('peso_bruto', null, ['class' => 'form-control','placeholder' => 'Peso bruto', 'data-mask="00000000.000"', 'data-mask-reverse="true"']); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?php echo Form::label('especie', 'Espécie:' ); ?>

                                <?php echo Form::text('especie', null, ['class' => 'form-control','placeholder' => 'Espécie']); ?>

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?php echo Form::label('qtd_volumes', 'Quantidade de volumes:' ); ?>

                                <?php echo Form::text('qtd_volumes', null, ['class' => 'form-control','placeholder' => 'Quantidade de volumes']); ?>

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?php echo Form::label('numeracao_volumes', 'Numeração de volumes:' ); ?>

                                <?php echo Form::text('numeracao_volumes', null, ['class' => 'form-control','placeholder' => 'Numeração de volumes']); ?>

                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?php echo Form::label('valor_frete', 'Valor do frete:' ); ?>

                                <?php echo Form::text('valor_frete', 0.00, ['id' => 'valor_frete', 'class' => 'form-control','placeholder' => 'Valor do frete', 'data-mask="00000000,00"', 'data-mask-reverse="true"']); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('transportadora_id', 'Transportadora:' ); ?>


                                <?php echo Form::select('transportadora_id', $transportadoras, 'transportadora_id' , ['class' => 'form-control select2','placeholder' => 'Transportadora', 'data-default' => 'percentage', 'style' => 'width: 100%']); ?>


                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ); ?>

                                <?php echo Form::text('delivered_to', null, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo Form::label('additional_notes', 'Informação complementar'); ?>

                    <?php echo Form::textarea('additional_notes', $default_location ? $default_location->info_complementar : '', ['class' => 'form-control', 'rows' => 3, 'id' => 'info_complementar']); ?>

                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <?php echo Form::label('referencia_nfe', 'Referência NF-e' . ':' ); ?>

                    <?php echo Form::text('referencia_nfe', null, ['class' => 'form-control','placeholder' => 'Referência NF-e', 'data-mask="00000000000000000000000000000000000000000000"', 'data-mask-reverse="true"']); ?>

                </div>
            </div>

            <?php echo $__env->make('sale_pos.partials.payment_modal_pedido', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <input type="hidden" id="json_boleto" name="json_boleto">

            <?php echo Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); ?>

            <div class="col-sm-12 text-right">
                <!-- <button type="button" id="submit-sell" class="btn btn-primary btn-flat"><?php echo app('translator')->get('messages.save'); ?></button>
			<button type="button" id="save-and-print" class="btn btn-primary btn-flat"><?php echo app('translator')->get('lang_v1.save_and_print'); ?></button> -->
            <?php if(request()->query('status') === 'locacao'): ?>
                <button type="button" class="btn bg-navy btn-default" id="pedido-finalize" title="<?php echo app('translator')->get('lang_v1.tooltip_checkout_multi_pay'); ?>"><i class="fas fa-check" aria-hidden="true"></i> Finalizar</button>
            <?php else: ?>
                <button type="button" class="btn bg-navy btn-default" id="pedido-finalize" title="<?php echo app('translator')->get('lang_v1.tooltip_checkout_multi_pay'); ?>"><i class="fas fa-check" aria-hidden="true"></i> Finalizar Venda</button>
            <?php endif; ?>  
            </div>
            <?php echo $__env->renderComponent(); ?>

        </div>
    </div>





    <!-- TAG Pagamento -->


    <!-- <div class="row">
	
	<?php echo Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); ?>

	<div class="col-sm-12 text-right">
	<button type="button" id="submit-sell" class="btn btn-primary btn-flat"><?php echo app('translator')->get('messages.save'); ?></button>
		<button type="button" id="save-and-print" class="btn btn-primary btn-flat"><?php echo app('translator')->get('lang_v1.save_and_print'); ?></button>

		<button type="button" class="btn bg-navy btn-default" id="pedido-finalize" title="<?php echo app('translator')->get('lang_v1.tooltip_checkout_multi_pay'); ?>"><i class="fas fa-check" aria-hidden="true"></i> Finalizar</button>
	</div>

</div> -->
    <br>

    <?php if(empty($pos_settings['disable_recurring_invoice'])): ?>
    <?php echo $__env->make('sale_pos.partials.recurring_invoice_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>


    <?php echo Form::close(); ?>

</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <?php echo $__env->make('contact.create', ['quick_add' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<?php echo $__env->make('sale_pos.partials.configure_search_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('javascript'); ?>


<script src="<?php echo e(asset('js/pos.js?v=' . $asset_v), false); ?>"></script>
<script src="<?php echo e(asset('js/product.js?v=' . $asset_v), false); ?>"></script>
<script src="<?php echo e(asset('js/opening_stock.js?v=' . $asset_v), false); ?>"></script>

<!-- Call restaurant module if defined -->
<?php if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules)): ?>
<script src="<?php echo e(asset('js/restaurant.js?v=' . $asset_v), false); ?>"></script>


<?php endif; ?>

<script type="text/javascript">
    $('#select_location_id').change((target) => {
        let id = target.target.value
        if (id) {
            $.get('/business-location/' + id + '/settingsAjax')
                .done((res) => {
                    console.log(res)
                    $('#info_complementar').val(res.info_complementar)
                })
                .fail((err) => {
                    console.log(err)
                })
        }
    })

</script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/sell/create.blade.php ENDPATH**/ ?>