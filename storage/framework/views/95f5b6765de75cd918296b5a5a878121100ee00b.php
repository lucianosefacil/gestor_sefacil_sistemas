<table class="table bg-gray">
        <tr class="bg-green">
        <th>#</th>
        <th><?php echo e(__('sale.product'), false); ?></th>
        <?php if( session()->get('business.enable_lot_number') == 1): ?>
            <th><?php echo e(__('lang_v1.lot_n_expiry'), false); ?></th>
        <?php endif; ?>
        <th><?php echo e(__('sale.qty'), false); ?></th>
        <?php if(!empty($pos_settings['inline_service_staff'])): ?>
            <th>
                <?php echo app('translator')->get('restaurant.service_staff'); ?>
            </th>
        <?php endif; ?>
        <th><?php echo e(__('sale.unit_price'), false); ?></th>
        <th><?php echo e(__('sale.discount'), false); ?></th>
        <th><?php echo e(__('sale.tax'), false); ?></th>
        <th><?php echo e(__('sale.price_inc_tax'), false); ?></th>
        <th><?php echo e(__('sale.subtotal'), false); ?></th>
    </tr>
    <?php $__currentLoopData = $sell->sell_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sell_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($loop->iteration, false); ?></td>
            <td>
                <?php echo e($sell_line->product->name, false); ?>

                <?php if( $sell_line->product->type == 'variable'): ?>
                - <?php echo e($sell_line->variations->product_variation->name ?? '', false); ?>

                - <?php echo e($sell_line->variations->name ?? '', false); ?>,
                <?php endif; ?>
                <?php echo e($sell_line->variations->sub_sku ?? '', false); ?>

                <?php
                $brand = $sell_line->product->brand;
                ?>
                <?php if(!empty($brand->name)): ?>
                , <?php echo e($brand->name, false); ?>

                <?php endif; ?>

                <?php if(!empty($sell_line->sell_line_note)): ?>
                <br> <?php echo e($sell_line->sell_line_note, false); ?>

                <?php endif; ?>
                <?php if($is_warranty_enabled && !empty($sell_line->warranties->first()) ): ?>
                    <br><small><?php echo e($sell_line->warranties->first()->display_name ?? '', false); ?> - <?php echo e(\Carbon::createFromTimestamp(strtotime($sell_line->warranties->first()->getEndDate($sell->transaction_date)))->format(session('business.date_format')), false); ?></small>
                    <?php if(!empty($sell_line->warranties->first()->description)): ?>
                    <br><small><?php echo e($sell_line->warranties->first()->description ?? '', false); ?></small>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <?php if( session()->get('business.enable_lot_number') == 1): ?>
                <td><?php echo e($sell_line->lot_details->lot_number ?? '--', false); ?>

                    <?php if( session()->get('business.enable_product_expiry') == 1 && !empty($sell_line->lot_details->exp_date)): ?>
                    (<?php echo e(\Carbon::createFromTimestamp(strtotime($sell_line->lot_details->exp_date))->format(session('business.date_format')), false); ?>)
                    <?php endif; ?>
                </td>
            <?php endif; ?>
            <td>
                <span class="display_currency" data-currency_symbol="false" data-is_quantity="true"><?php echo e($sell_line->quantity, false); ?></span> <?php if(!empty($sell_line->sub_unit)): ?> <?php echo e($sell_line->sub_unit->short_name, false); ?> <?php else: ?> <?php echo e($sell_line->product->unit->short_name, false); ?> <?php endif; ?>
            </td>
            <?php if(!empty($pos_settings['inline_service_staff'])): ?>
                <td>
                <?php echo e($sell_line->service_staff->user_full_name ?? '', false); ?>

                </td>
            <?php endif; ?>
            <td>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($sell_line->unit_price_before_discount, false); ?></span>
            </td>
            <td>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($sell_line->get_discount_amount(), false); ?></span> <?php if($sell_line->line_discount_type == 'percentage'): ?> (<?php echo e($sell_line->line_discount_amount, false); ?>%) <?php endif; ?>
            </td>
            <td>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($sell_line->item_tax, false); ?></span> 
                <?php if(!empty($taxes[$sell_line->tax_id])): ?>
                ( <?php echo e($taxes[$sell_line->tax_id], false); ?> )
                <?php endif; ?>
            </td>
            <td>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($sell_line->unit_price_inc_tax, false); ?></span>
            </td>
            <td>
                <span class="display_currency" data-currency_symbol="true"><?php echo e($sell_line->quantity * $sell_line->unit_price_inc_tax, false); ?></span>
            </td>
        </tr>
        <?php if(!empty($sell_line->modifiers)): ?>
        <?php $__currentLoopData = $sell_line->modifiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modifier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <?php echo e($modifier->product->name, false); ?> - <?php echo e($modifier->variations->name ?? '', false); ?>,
                    <?php echo e($modifier->variations->sub_sku ?? '', false); ?>

                </td>
                <?php if( session()->get('business.enable_lot_number') == 1): ?>
                    <td>&nbsp;</td>
                <?php endif; ?>
                <td><?php echo e($modifier->quantity, false); ?></td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($modifier->unit_price, false); ?></span>
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($modifier->item_tax, false); ?></span> 
                    <?php if(!empty($taxes[$modifier->tax_id])): ?>
                    ( <?php echo e($taxes[$modifier->tax_id], false); ?> )
                    <?php endif; ?>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($modifier->unit_price_inc_tax, false); ?></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($modifier->quantity * $modifier->unit_price_inc_tax, false); ?></span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $servicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($servico->id, false); ?></td>
                <td><?php echo e($servico->servico->nome, false); ?></td>
                <td>
                    <span class="display_currency" data-currency_symbol="false" data-is_quantity="true"><?php echo e($servico->quantity, false); ?></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($servico->valor_unitario, false); ?></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($servico->valor_unitario, false); ?></span>
                </td>
                <td>
                    <span class="display_currency" data-currency_symbol="true"><?php echo e($servico->quantity * $servico->valor_unitario, false); ?></span>
                </td>
            </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/sale_pos/partials/sale_line_details.blade.php ENDPATH**/ ?>