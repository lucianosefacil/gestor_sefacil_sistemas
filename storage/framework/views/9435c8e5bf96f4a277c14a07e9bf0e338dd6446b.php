<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> 
        <?php if($sell->status == 'pending'): ?>
          <?php if($sell->is_locacao == 1): ?>
            <?php echo app('translator')->get('Detalhes da'); ?>
              <?php echo e(__('Locação'), false); ?> 
              (<b><?php echo app('translator')->get('Numero'); ?>: <?php echo e($sell->invoice_no, false); ?>)</b> 
              <br>   
          <?php else: ?>
            <?php echo app('translator')->get('Detalhes do'); ?>
            <?php echo e(__('Orçamento'), false); ?> 
            (<b><?php echo app('translator')->get('Numero'); ?>: <?php echo e($sell->invoice_no, false); ?>)</b> 
            <br>
          <?php endif; ?>
        <?php else: ?>
          <?php if($sell->is_locacao == 1): ?>
            <?php echo app('translator')->get('Detalhes da'); ?>
            <?php echo e(__('Locação'), false); ?> 
            (<b><?php echo app('translator')->get('Numero'); ?>: <?php echo e($sell->invoice_no, false); ?>)</b> 
            <br>   
          <?php else: ?>
            <?php echo app('translator')->get('Detalhes da'); ?>
            <?php echo e(__('Venda'), false); ?>  
              (<b><?php echo app('translator')->get('Numero'); ?>: <?php echo e($sell->invoice_no, false); ?>)</b> 
          <?php endif; ?>
           
        <?php endif; ?>
    </h4>

</div>

<div class="modal-body">
    <div class="row">
      <div class="col-xs-12">
          <p class="pull-right"><b><?php echo app('translator')->get('messages.date'); ?>:</b> <?php echo e(\Carbon::createFromTimestamp(strtotime($sell->transaction_date))->format(session('business.date_format')), false); ?></p>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-4">
          <?php if($sell->status == 'pending'): ?>
              <?php if($sell->is_locacao == 1): ?>
              <b><?php $__env->startTranslation(); ?><?php echo e('Status', false); ?>: <?php echo e('Locação', false); ?></b> 
              <br>
              <?php else: ?>
              <b><?php $__env->startTranslation(); ?><?php echo e('Status', false); ?>: <?php echo e('Orçamento', false); ?></b> 
              <br>
              <?php endif; ?>
              <b><?php $__env->startTranslation(); ?><?php echo e('Numero', false); ?>: <?php echo e($sell->invoice_no, false); ?></b>
              <br>
            <b><?php if(empty($sell->payment_status)): ?><?php echo e(('Pagamento: '. 'Em Aberto'), false); ?></b> 

          <?php endif; ?>
          
          <?php else: ?>
            <?php if($sell->is_locacao == 1): ?>
              <b><?php $__env->startTranslation(); ?> <?php echo e('Status:', false); ?> <?php echo e('Locação', false); ?></b> 
              <br>   
            <?php else: ?>
              <b><?php $__env->startTranslation(); ?> <?php echo e('Status:', false); ?> <?php echo e('Venda', false); ?></b> 
              <br>   
            <?php endif; ?>    
              <b><?php $__env->startTranslation(); ?> <?php echo e('Numero da Venda:', false); ?> <?php echo e($sell->invoice_no, false); ?></b> 
          <?php endif; ?>
              <br>     
              
            <?php if(!empty($sell->payment_status)): ?>
              <b><?php echo e(__('sale.payment_status'), false); ?>:</b>
              <b><?php echo e(__('lang_v1.' . $sell->payment_status), false); ?></b>
              <br>
            <?php endif; ?>

      </div>

      <div class="col-sm-4">
        <b><?php echo e(__('sale.customer_name'), false); ?>:</b> <?php echo e($sell->contact->name, false); ?><br>
        <b><?php echo e(__('business.address'), false); ?>:</b><br>
        <?php if(!empty($sell->billing_address())): ?>
          <?php echo e($sell->billing_address(), false); ?>

        <?php else: ?>
          <?php if($sell->contact->landmark): ?>
              <?php echo e($sell->contact->landmark, false); ?>,
          <?php endif; ?>

          <?php echo e($sell->contact->city, false); ?>


          <?php if($sell->contact->state): ?>
              <?php echo e(', ' . $sell->contact->state, false); ?>

          <?php endif; ?>
          <br>
          <?php if($sell->contact->country): ?>
              <?php echo e($sell->contact->country, false); ?>

          <?php endif; ?>
          <?php if($sell->contact->mobile): ?>
          <br>
              <?php echo e(__('contact.mobile'), false); ?>: <?php echo e($sell->contact->mobile, false); ?>

          <?php endif; ?>
          <?php if($sell->contact->alternate_number): ?>
          <br>
              <?php echo e(__('contact.alternate_contact_number'), false); ?>: <?php echo e($sell->contact->alternate_number, false); ?>

          <?php endif; ?>
          <?php if($sell->contact->landline): ?>
            <br>
              <?php echo e(__('contact.landline'), false); ?>: <?php echo e($sell->contact->landline, false); ?>

          <?php endif; ?>
        <?php endif; ?>
        
      </div>
      <div class="col-sm-4">
          <?php if(in_array('tables' ,$enabled_modules)): ?>
            <strong><?php echo app('translator')->get('restaurant.table'); ?>:</strong>
              <?php echo e($sell->table->name ?? '', false); ?><br>
          <?php endif; ?>
          <?php if(in_array('service_staff' ,$enabled_modules)): ?>
              <strong><?php echo app('translator')->get('restaurant.service_staff'); ?>:</strong>
              <?php echo e($sell->service_staff->user_full_name ?? '', false); ?><br>
          <?php endif; ?>

          <strong>Entrega:</strong>
          <span class="label <?php if(!empty($shipping_status_colors[$sell->shipping_status])): ?> <?php echo e($shipping_status_colors[$sell->shipping_status], false); ?> <?php else: ?> <?php echo e('bg-gray', false); ?> <?php endif; ?>"><?php echo e($shipping_statuses[$sell->shipping_status] ?? '', false); ?></span><br>
          <?php if(!empty($sell->shipping_address())): ?>
            <?php echo e($sell->shipping_address(), false); ?>

          <?php else: ?>
            <?php echo e($sell->shipping_address ?? '--', false); ?>

          <?php endif; ?>
          <?php if(!empty($sell->delivered_to)): ?>
            <br><strong><?php echo app('translator')->get('lang_v1.delivered_to'); ?>: </strong> <?php echo e($sell->delivered_to, false); ?>

          <?php endif; ?>

          <?php if(in_array('types_of_service' ,$enabled_modules)): ?>
          <?php
            $custom_labels = json_decode(session('business.custom_labels'), true);
          ?>
            <?php if(!empty($sell->types_of_service)): ?>
              <strong><?php echo app('translator')->get('lang_v1.types_of_service'); ?>:</strong>
              <?php echo e($sell->types_of_service->name, false); ?><br>
            <?php endif; ?>
            <?php if(!empty($sell->types_of_service->enable_custom_fields)): ?>
              <strong><?php echo e($custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' ), false); ?>:</strong>
              <?php echo e($sell->service_custom_field_1, false); ?><br>
              <strong><?php echo e($custom_labels['types_of_service']['custom_field_2'] ?? __('lang_v1.service_custom_field_2' ), false); ?>:</strong>
              <?php echo e($sell->service_custom_field_2, false); ?><br>
              <strong><?php echo e($custom_labels['types_of_service']['custom_field_3'] ?? __('lang_v1.service_custom_field_3' ), false); ?>:</strong>
              <?php echo e($sell->service_custom_field_3, false); ?><br>
              <strong><?php echo e($custom_labels['types_of_service']['custom_field_4'] ?? __('lang_v1.service_custom_field_4' ), false); ?>:</strong>
              <?php echo e($sell->service_custom_field_4, false); ?>

            <?php endif; ?>
          <?php endif; ?>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4><?php echo e(__('sale.products'), false); ?>:</h4>
      </div>

      <div class="col-sm-12 col-xs-12">
        <div class="table-responsive">
          <?php echo $__env->make('sale_pos.partials.sale_line_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4><?php echo e(__('sale.payment_info'), false); ?>:</h4>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr class="bg-green">
              <th>#</th>
              <th><?php echo e(__('messages.date'), false); ?></th>
              <th>Vencimento</th>
              <th><?php echo e(__('sale.amount'), false); ?></th>
              <th><?php echo e(__('sale.payment_mode'), false); ?></th>
              <th><?php echo e(__('sale.payment_note'), false); ?></th>
            </tr>
            <?php
              $total_paid = 0;
            ?>
            <?php $__currentLoopData = $sell->payment_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                if($payment_line->is_return == 1){
                  $total_paid -= $payment_line->amount;
                } else {
                  if($payment_line->method == 'aguardando_pagamento'){
                    $total_paid += 0;
                  }else{
                    $total_paid += $payment_line->amount;
                  }
                }
              ?>
              <tr>
                <td><?php echo e($loop->iteration, false); ?></td>
                <td><?php echo e(\Carbon::createFromTimestamp(strtotime($payment_line->vencimento))->format(session('business.date_format')), false); ?></td>
                <td><?php echo e($payment_line->payment_ref_no, false); ?></td>
                <td><span class="display_currency" data-currency_symbol="true"><?php echo e($payment_line->amount, false); ?></span></td>
                <td>
                  <?php echo e($payment_types[$payment_line->method] ?? $payment_line->method, false); ?>

                  <?php if($payment_line->is_return == 1): ?>
                    <br/>
                    ( <?php echo e(__('lang_v1.change_return'), false); ?> )
                  <?php endif; ?>
                </td>
                <td><?php if($payment_line->note): ?> 
                  <?php echo e(ucfirst($payment_line->note), false); ?>

                  <?php else: ?>
                  --
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </table>
        </div>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr>
              <th><?php echo e(__('sale.total'), false); ?>: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($sell->final_total, false); ?></span></td>
            </tr>
            <tr>
              <th><?php echo e(__('sale.discount'), false); ?>:</th>
              <td><b>(-)</b></td>
              <td><div class="pull-right"><span class="display_currency" <?php if( $sell->discount_type == 'fixed'): ?> data-currency_symbol="true" <?php endif; ?>><?php echo e($sell->discount_amount, false); ?></span> <?php if( $sell->discount_type == 'percentage'): ?> <?php echo e('%', false); ?> <?php endif; ?></span></div></td>
            </tr>
            <?php if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge)): ?>
              <tr>
                <th><?php echo e(__('lang_v1.packing_charge'), false); ?>:</th>
                <td><b>(+)</b></td>
                <td><div class="pull-right"><span class="display_currency" <?php if( $sell->packing_charge_type == 'fixed'): ?> data-currency_symbol="true" <?php endif; ?>><?php echo e($sell->packing_charge, false); ?></span> <?php if( $sell->packing_charge_type == 'percent'): ?> <?php echo e('%', false); ?> <?php endif; ?> </div></td>
              </tr>
            <?php endif; ?>
            <?php if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) ): ?>
              <tr>
                <th><?php echo e(session('business.rp_name'), false); ?>:</th>
                <td><b>(-)</b></td>
                <td> <span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($sell->rp_redeemed_amount, false); ?></span></td>
              </tr>
            <?php endif; ?>
            <tr>
              <th><?php echo e(__('sale.order_tax'), false); ?>:</th>
              <td><b>(+)</b></td>
              <td class="text-right">
                <?php if(!empty($order_taxes)): ?>
                  <?php $__currentLoopData = $order_taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <strong><small><?php echo e($k, false); ?></small></strong> - <span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($v, false); ?></span><br>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                0.00
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th><?php echo e(__('sale.shipping'), false); ?>: <?php if($sell->shipping_details): ?>(<?php echo e($sell->shipping_details, false); ?>) <?php endif; ?></th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($sell->shipping_charges, false); ?></span></td>
            </tr>
            <tr>
              <th><?php echo e(__('lang_v1.round_off'), false); ?>: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($sell->round_off_amount, false); ?></span></td>
            </tr>
            <tr>
              <th><?php echo e(__('sale.total_payable'), false); ?>: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true"><?php echo e($sell->final_total, false); ?></span></td>
            </tr>
            <tr>
              <th><?php echo e(__('sale.total_paid'), false); ?>:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true" ><?php echo e($total_paid, false); ?></span></td>
            </tr>
            <tr>
              <th><?php echo e(__('sale.total_remaining'), false); ?>:</th>
              <td></td>
              <td>
                <!-- Converting total paid to string for floating point substraction issue -->
                <?php
                  $total_paid = (string) $total_paid;
                ?>
                <span class="display_currency pull-right" data-currency_symbol="true" ><?php echo e($sell->final_total - $total_paid, false); ?></span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <strong><?php echo e(__( 'sale.sell_note'), false); ?>:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          <?php if($sell->additional_notes): ?>
            <?php echo e($sell->additional_notes, false); ?>

          <?php else: ?>
            --
          <?php endif; ?>
        </p>
      </div>
      <div class="col-sm-6">
        <strong><?php echo e(__( 'sale.staff_note'), false); ?>:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          <?php if($sell->staff_note): ?>
            <?php echo e($sell->staff_note, false); ?>

          <?php else: ?>
            --
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
  <div class="modal-footer">
  
    <a onclick="alert(window.open('/nfce/imprimirNaoFiscal/<?php echo e($sell->id, false); ?>'))" class="print-invoice btn btn-warning"><i class="fa fa-print" aria-hidden="true"></i> Imprimir não fiscal</a>

    <a href="#" class="print-invoice btn btn-primary" data-href="<?php echo e(route('sell.printInvoice', [$sell->id]), false); ?>"><i class="fa fa-print" aria-hidden="true"></i> Impressão</a>

    

    <button type="button" class="btn btn-default no-print" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>

     
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');
    __currency_convert_recursively(element);
  });
</script>
<?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/sale_pos/show.blade.php ENDPATH**/ ?>