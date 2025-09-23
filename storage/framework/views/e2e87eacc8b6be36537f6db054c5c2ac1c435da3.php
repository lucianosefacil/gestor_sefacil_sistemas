<!-- business information here -->
<div class="row">
    <?php if(!empty($receipt_details->logo)): ?>
        <div class="col-xs-3">
            <img width="90" src="<?php echo e($receipt_details->logo, false); ?>" class="img img-responsive center-block">
        </div>
        <div class="col-xs-9">
            <h4 class="text-left">
                <!-- Shop & Location Name  -->
                <?php if(!empty($receipt_details->display_name)): ?>
                    <?php echo e($receipt_details->display_name, false); ?>

                <?php endif; ?>
            </h4>
            <p>
                <?php if(!empty($receipt_details->address)): ?>
                    <small class="text-center">
                        <?php echo $receipt_details->address; ?>

                    </small>
                <?php endif; ?>

                <?php if(!empty($receipt_details->contact)): ?>
                    <?php
                        $contact = preg_replace('/\D/', '', $receipt_details->contact);
                        if (strlen($contact) == 11) {
                            $formatted_contact = preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $contact);
                        } elseif (strlen($contact) == 10) {
                            $formatted_contact = preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $contact);
                        } else {
                            $formatted_contact = $contact;
                        }
                    ?>
                    <br /><?php echo e($formatted_contact, false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->contact) && !empty($receipt_details->website)): ?>
                    ,
                <?php endif; ?>
                <?php if(!empty($receipt_details->website)): ?>
                    <?php echo e($receipt_details->website, false); ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->location_custom_fields)): ?>
                    <br><?php echo e($receipt_details->location_custom_fields, false); ?>

                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="col-xs-12">
            <h2 class="text-center">
                <!-- Shop & Location Name  -->
                <?php if(!empty($receipt_details->display_name)): ?>
                    <?php echo e($receipt_details->display_name, false); ?>

                <?php endif; ?>
            </h2>
            <p class="text-center">
                <?php if(!empty($receipt_details->address)): ?>
                    <small class="text-center">
                        <?php echo $receipt_details->address; ?>

                    </small>
                <?php endif; ?>
                <?php if(!empty($receipt_details->contact)): ?>
                    <br /><?php echo e($receipt_details->contact, false); ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->contact) && !empty($receipt_details->website)): ?>
                    ,
                <?php endif; ?>
                <?php if(!empty($receipt_details->website)): ?>
                    <?php echo e($receipt_details->website, false); ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->location_custom_fields)): ?>
                    <br><?php echo e($receipt_details->location_custom_fields, false); ?>

                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Header text -->
    <?php if(!empty($receipt_details->header_text)): ?>
        <div class="col-xs-12">
            <?php echo $receipt_details->header_text; ?>

        </div>
    <?php endif; ?>

    <!-- business information here -->
    <div class="col-xs-12 text-center">
        <!-- Address -->
        <!-- <p>
        <?php if(!empty($receipt_details->address)): ?>
        <small class="text-center">
            <?php echo $receipt_details->address; ?>

        </small>
        <?php endif; ?>
        <?php if(!empty($receipt_details->contact)): ?>
        <br/><?php echo e($receipt_details->contact, false); ?>

        <?php endif; ?>
        <?php if(!empty($receipt_details->contact) && !empty($receipt_details->website)): ?>
        ,
        <?php endif; ?>
        <?php if(!empty($receipt_details->website)): ?>
        <?php echo e($receipt_details->website, false); ?>

        <?php endif; ?>
        <?php if(!empty($receipt_details->location_custom_fields)): ?>
        <br><?php echo e($receipt_details->location_custom_fields, false); ?>

        <?php endif; ?>
        </p> -->
        <p>
            <?php if(!empty($receipt_details->sub_heading_line1)): ?>
                <?php echo e($receipt_details->sub_heading_line1, false); ?>

            <?php endif; ?>
            <?php if(!empty($receipt_details->sub_heading_line2)): ?>
                <br><?php echo e($receipt_details->sub_heading_line2, false); ?>

            <?php endif; ?>
            <?php if(!empty($receipt_details->sub_heading_line3)): ?>
                <br><?php echo e($receipt_details->sub_heading_line3, false); ?>

            <?php endif; ?>
            <?php if(!empty($receipt_details->sub_heading_line4)): ?>
                <br><?php echo e($receipt_details->sub_heading_line4, false); ?>

            <?php endif; ?>
            <?php if(!empty($receipt_details->sub_heading_line5)): ?>
                <br><?php echo e($receipt_details->sub_heading_line5, false); ?>

            <?php endif; ?>
        </p>
        <p>
            <?php if(!empty($receipt_details->tax_info1)): ?>
                <b><?php echo e($receipt_details->tax_label1, false); ?></b> <?php echo e($receipt_details->tax_info1, false); ?>

            <?php endif; ?>

            <?php if(!empty($receipt_details->tax_info2)): ?>
                <b><?php echo e($receipt_details->tax_label2, false); ?></b> <?php echo e($receipt_details->tax_info2, false); ?>

            <?php endif; ?>
        </p>

        <!-- Title of receipt -->
        <?php if($receipt_details->is_locacao == 1): ?>
            <h4 class="text-center">
                Locação
            </h4>
        <?php else: ?>
            <?php if(!empty($receipt_details->invoice_heading)): ?>
                <h3 class="text-center">
                    <?php echo $receipt_details->invoice_heading; ?>

                </h3>
            <?php endif; ?>
        <?php endif; ?>


        <!-- Invoice  number, Date  -->
        <p style="width: 100% !important; font-size: 12px" class="word-wrap">
            <span class="pull-left text-left word-wrap">
                <?php if($receipt_details->is_locacao == 1): ?>
                    <span><strong>Locação N.</strong></span>
                <?php else: ?>
                    <?php if(!empty($receipt_details->invoice_no_prefix)): ?>
                        <b><?php echo $receipt_details->invoice_no_prefix; ?></b>
                    <?php endif; ?>
                <?php endif; ?>

                <?php echo e($receipt_details->invoice_no, false); ?>


                <?php if(!empty($receipt_details->types_of_service)): ?>
                    <br />
                    <span class="pull-left text-left">
                        <strong><?php echo $receipt_details->types_of_service_label; ?>:</strong>
                        <?php echo e($receipt_details->types_of_service, false); ?>

                        <!-- Waiter info -->
                        <?php if(!empty($receipt_details->types_of_service_custom_fields)): ?>
                            <?php $__currentLoopData = $receipt_details->types_of_service_custom_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <br><strong><?php echo e($key, false); ?>: </strong> <?php echo e($value, false); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>

                <!-- Table information-->
                <?php if(!empty($receipt_details->table_label) || !empty($receipt_details->table)): ?>
                    <br />
                    <span class="pull-left text-left">
                        <?php if(!empty($receipt_details->table_label)): ?>
                            <b><?php echo $receipt_details->table_label; ?></b>
                        <?php endif; ?>
                        <?php echo e($receipt_details->table, false); ?>


                        <!-- Waiter info -->
                    </span>
                <?php endif; ?>

                <!-- customer info -->
                <?php if(!empty($receipt_details->customer_name)): ?>
                    <br />
                    <b><?php echo e($receipt_details->customer_label, false); ?></b> <?php echo e($receipt_details->customer_name, false); ?> <br>

                    <?php if($receipt_details->is_locacao == 0): ?>
                        <?php if($cliente->rua): ?>
                            <span><?php echo e($cliente->rua, false); ?>, <?php echo e($cliente->numero, false); ?></span> <br>
                            <span><?php echo e($cliente->bairro, false); ?></span> <br>
                            <span><?php echo e($cliente->cep, false); ?></span> <br>
                        <?php endif; ?>
                    <?php else: ?>
                        <span><strong>Documento</strong></span> <?php echo e($receipt_details->cpf_cnpj, false); ?>

                    <?php endif; ?>
                <?php endif; ?>

                <?php if($receipt_details->is_locacao == 0): ?>
                <?php if(!empty($receipt_details->customer_info)): ?>
                    <?php echo $receipt_details->customer_info; ?>

                <?php endif; ?>
                <?php endif; ?>
                
                <?php if($receipt_details->is_locacao == 0): ?>
                    <?php if(!empty($receipt_details->client_id_label)): ?>
                        <br />
                        <b><?php echo e($receipt_details->client_id_label, false); ?></b> <?php echo e($receipt_details->client_id, false); ?>

                    <?php endif; ?>
                <?php endif; ?>

                <?php if($receipt_details->is_locacao == 0): ?>
                <?php if(!empty($receipt_details->customer_tax_label)): ?>
                    <br />
                    <b><?php echo e($receipt_details->customer_tax_label, false); ?></b> <?php echo e($receipt_details->customer_tax_number, false); ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->customer_custom_fields)): ?>
                    <br /><?php echo $receipt_details->customer_custom_fields; ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->sales_person_label)): ?>
                    <br />
                    <b><?php echo e($receipt_details->sales_person_label, false); ?></b> <?php echo e($receipt_details->sales_person, false); ?>

                <?php endif; ?>
                <?php if(!empty($receipt_details->customer_rp_label)): ?>
                    <br />
                    <strong><?php echo e($receipt_details->customer_rp_label, false); ?></strong>
                    <?php echo e($receipt_details->customer_total_rp, false); ?>

                <?php endif; ?>
                <?php endif; ?>

            </span>

            <span class="pull-right text-left">
                <?php if($receipt_details->is_locacao == 1): ?>
                    <span><strong>Data Locação</strong></span>
                    <?php echo e(\Carbon::createFromTimestamp(strtotime($data_criacao))->format(session('business.date_format')), false); ?>

                <?php else: ?>
                    <b><?php echo e($receipt_details->date_label, false); ?></b>
                    <?php echo e($receipt_details->invoice_date, false); ?>

                <?php endif; ?>

                <br>

                <?php if($receipt_details->is_locacao == 1): ?>
                    <span><strong>Data Devol.</strong></span>
                    <?php echo e(\Carbon::createFromTimestamp(strtotime($data_devolucao))->format(session('business.date_format')), false); ?>

                <?php endif; ?>

                <br>

                <?php if($receipt_details->is_locacao == 1): ?>
                    <span><strong>Código cliente</strong></span>
                    <?php echo e($receipt_details->client_id, false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->due_date_label)): ?>
                    <br><b><?php echo e($receipt_details->due_date_label, false); ?></b> <?php echo e($receipt_details->due_date ?? '', false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand)): ?>
                    <br>
                    <?php if(!empty($receipt_details->brand_label)): ?>
                        <b><?php echo $receipt_details->brand_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_brand, false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->device_label) || !empty($receipt_details->repair_device)): ?>
                    <br>
                    <?php if(!empty($receipt_details->device_label)): ?>
                        <b><?php echo $receipt_details->device_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_device, false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no)): ?>
                    <br>
                    <?php if(!empty($receipt_details->model_no_label)): ?>
                        <b><?php echo $receipt_details->model_no_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_model_no, false); ?>

                <?php endif; ?>

                <?php if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no)): ?>
                    <br>
                    <?php if(!empty($receipt_details->serial_no_label)): ?>
                        <b><?php echo $receipt_details->serial_no_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_serial_no, false); ?><br>
                <?php endif; ?>
                <?php if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status)): ?>
                    <?php if(!empty($receipt_details->repair_status_label)): ?>
                        <b><?php echo $receipt_details->repair_status_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_status, false); ?><br>
                <?php endif; ?>

                <?php if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty)): ?>
                    <?php if(!empty($receipt_details->repair_warranty_label)): ?>
                        <b><?php echo $receipt_details->repair_warranty_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->repair_warranty, false); ?>

                    <br>
                <?php endif; ?>

                <!-- Waiter info -->
                <?php if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff)): ?>
                    <br />
                    <?php if(!empty($receipt_details->service_staff_label)): ?>
                        <b><?php echo $receipt_details->service_staff_label; ?></b>
                    <?php endif; ?>
                    <?php echo e($receipt_details->service_staff, false); ?>

                <?php endif; ?>
            </span>
        </p>
    </div>
</div>

<?php if($receipt_details->is_locacao == 0): ?>
<div class="row">
    <?php if ($__env->exists('sale_pos.receipts.partial.common_repair_invoice')) echo $__env->make('sale_pos.receipts.partial.common_repair_invoice', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php endif; ?>

<div class="row" style="font-size: 12px">
    <div class="col-xs-12">
        <table class="table table-responsive" style="margin-bottom: -15px;">
            <thead>
                <tr>
                    <th>
                        <?php if($receipt_details->is_locacao == 1): ?>
                            Reboque
                        <?php else: ?>
                            <?php echo e($receipt_details->table_product_label, false); ?>

                        <?php endif; ?>
                    </th>
                    <th class="text-right">
                        <?php if($receipt_details->is_locacao == 1): ?>
                            Dias
                        <?php else: ?>
                            <?php echo e($receipt_details->table_qty_label, false); ?>

                        <?php endif; ?>
                    </th>
                    <th class="text-right">
                        <?php if($receipt_details->is_locacao == 1): ?>
                            Valor Diária
                        <?php else: ?>
                            <?php echo e($receipt_details->table_unit_price_label, false); ?>

                        <?php endif; ?>
                    </th>
                    <th class="text-right"><?php echo e($receipt_details->table_subtotal_label, false); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $receipt_details->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="word-break: break-all;">
                            <?php if(!empty($line['image'])): ?>
                                <img src="<?php echo e($line['image'], false); ?>" alt="Image" width="30"
                                    style="float: left; margin-right: 8px;">
                            <?php endif; ?>
                            <?php echo e($line['name'], false); ?> <?php echo e($line['product_variation'], false); ?> <?php echo e($line['variation'], false); ?>

                            <?php if(!empty($line['sub_sku'])): ?>
                                , <?php echo e($line['sub_sku'], false); ?>

                                <?php endif; ?> <?php if(!empty($line['brand'])): ?>
                                    , <?php echo e($line['brand'], false); ?>

                                    <?php endif; ?> <?php if(!empty($line['cat_code'])): ?>
                                        , <?php echo e($line['cat_code'], false); ?>

                                    <?php endif; ?>
                                    <?php if(!empty($line['product_custom_fields'])): ?>
                                        , <?php echo e($line['product_custom_fields'], false); ?>

                                    <?php endif; ?>
                                    <?php if(!empty($line['sell_line_note'])): ?>
                                        (<?php echo e($line['sell_line_note'], false); ?>)
                                    <?php endif; ?>
                                    <?php if(!empty($line['lot_number'])): ?>
                                        <br> <?php echo e($line['lot_number_label'], false); ?>: <?php echo e($line['lot_number'], false); ?>

                                    <?php endif; ?>
                                    <?php if(!empty($line['product_expiry'])): ?>
                                        , <?php echo e($line['product_expiry_label'], false); ?>: <?php echo e($line['product_expiry'], false); ?>

                                    <?php endif; ?>

                                    <?php if(!empty($line['warranty_name'])): ?>
                                        <br><small><?php echo e($line['warranty_name'], false); ?> </small>
                                        <?php endif; ?> <?php if(!empty($line['warranty_exp_date'])): ?>
                                            <small>- <?php echo e(\Carbon::createFromTimestamp(strtotime($line['warranty_exp_date']))->format(session('business.date_format')), false); ?> </small>
                                        <?php endif; ?>
                                        <?php if(!empty($line['warranty_description'])): ?>
                                            <small> <?php echo e($line['warranty_description'] ?? '', false); ?></small>
                                        <?php endif; ?>
                        </td>
                        <td class="text-right"><?php echo e($line['quantity'], false); ?> <?php echo e($line['units'], false); ?> </td>
                        <td class="text-right"><?php echo e($line['unit_price'], false); ?></td>
                        <td class="text-right"><?php echo e($line['line_total'], false); ?></td>
                    </tr>
                    <?php if(!empty($line['modifiers'])): ?>
                        <?php $__currentLoopData = $line['modifiers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modifier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php echo e($modifier['name'], false); ?> <?php echo e($modifier['variation'], false); ?>

                                    <?php if(!empty($modifier['sub_sku'])): ?>
                                        , <?php echo e($modifier['sub_sku'], false); ?>

                                        <?php endif; ?> <?php if(!empty($modifier['cat_code'])): ?>
                                            , <?php echo e($modifier['cat_code'], false); ?>

                                        <?php endif; ?>
                                        <?php if(!empty($modifier['sell_line_note'])): ?>
                                            (<?php echo e($modifier['sell_line_note'], false); ?>)
                                        <?php endif; ?>
                                </td>
                                <td class="text-right"><?php echo e($modifier['quantity'], false); ?> <?php echo e($modifier['units'], false); ?> </td>
                                <td class="text-right"><?php echo e($modifier['unit_price_inc_tax'], false); ?></td>
                                <td class="text-right"><?php echo e($modifier['line_total'], false); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="row" style="font-size: 12px; padding: 10px">
    <?php if($receipt_details->is_locacao == 0): ?>
    <div class="col-xs-6">
        <table class="table table-condensed">
            <?php if(!empty($receipt_details->payments)): ?>
                <?php $__currentLoopData = $receipt_details->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="font-weight: bold"><?php echo e($payment['method'], false); ?></td>
                        <td class="text-right"><?php echo e($payment['amount'], false); ?></td>
                        <td> - <?php echo e($payment['date'], false); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <!-- Total Paid-->
            <?php if(!empty($receipt_details->total_paid)): ?>
                <tr>
                    <th>
                        <?php echo $receipt_details->total_paid_label; ?>

                    </th>
                    <td class="text-right">
                        <?php echo e($receipt_details->total_paid, false); ?>

                    </td>
                </tr>
            <?php endif; ?>

            <!-- Total Due-->
            <?php if(!empty($receipt_details->total_due)): ?>
                <tr>
                    <th>
                        <?php echo $receipt_details->total_due_label; ?>

                    </th>
                    <td class="text-right">
                        <?php echo e($receipt_details->total_due, false); ?>

                    </td>
                </tr>
            <?php endif; ?>

            <?php if(!empty($receipt_details->all_due)): ?>
                <tr>
                    <th>
                        <?php echo $receipt_details->all_bal_label; ?>

                    </th>
                    <td class="text-right">
                        <?php echo e($receipt_details->all_due, false); ?>

                    </td>
                </tr>
            <?php endif; ?>
        </table>
        <?php echo e($receipt_details->additional_notes, false); ?>

    </div>
    <?php else: ?>

    <div class="col-xs-6">

    </div>
    <?php endif; ?>

    <div class="col-xs-6" style="padding: 10px">
        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr class="color-555">
                        <th style="width:70%; padding: 5px !important;">
                            <strong>Subtotal</strong>
                        </th>
                        <td class="text-right" style="padding: 5px !important;">
                            <?php echo e($receipt_details->total_paid, false); ?>

                        </td>
                    </tr>
                    
                    
                    <?php if(!empty($receipt_details->total_quantity_label)): ?>
                        <tr class="color-555">
                            <th style="width:70%">
                                <?php echo $receipt_details->total_quantity_label; ?>

                            </th>
                            <td class="text-right">
                                <?php echo e($receipt_details->total_quantity, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Shipping Charges -->
                    <?php if(!empty($receipt_details->shipping_charges)): ?>
                        <tr>
                            <th style="width:70%">
                                <?php echo $receipt_details->shipping_charges_label; ?>

                            </th>
                            <td class="text-right">
                                <?php echo e($receipt_details->shipping_charges, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Discount -->
                    <?php if(!empty($receipt_details->discount)): ?>
                        <tr>
                            <th style="width:70%; padding: 3px !important;">
                                <?php echo $receipt_details->discount_label; ?>

                            </th>
                            <td class="text-right" style="width:70%; padding: 3px !important;">
                                (-) <?php echo e($receipt_details->discount, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if(!empty($receipt_details->reward_point_label)): ?>
                        <tr>
                            <th>
                                <?php echo $receipt_details->reward_point_label; ?>

                            </th>

                            <td class="text-right">
                                (-) <?php echo e($receipt_details->reward_point_amount, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Tax -->
                    <?php if(!empty($receipt_details->tax)): ?>
                        <tr>
                            <th>
                                <?php echo $receipt_details->tax_label; ?>

                            </th>
                            <td class="text-right">
                                (+) <?php echo e($receipt_details->tax, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if(!empty($receipt_details->round_off_label)): ?>
                        <tr>
                            <th>
                                <?php echo $receipt_details->round_off_label; ?>

                            </th>
                            <td class="text-right">
                                <?php echo e($receipt_details->round_off, false); ?>

                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Total -->
                    <tr>
                        <th style="width:70%; padding: 3px !important;">
                            <?php echo $receipt_details->total_label; ?>

                        </th>
                        <td class="text-right" style="width:70%; padding: 3px !important;">
                            <?php echo e($receipt_details->total, false); ?>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($receipt_details->is_locacao == 1): ?>
<div class="" style="font-size: 12px">
    <p class="text-center">_________________________________________________ </p>
    <p class="text-center">Assinatura do Cliente</p>

    <div class="card" style="width: 100%; min-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;">
        <strong>Obs. de Pagamento:</strong>
        <p style="margin-top: 5px;">
            <?php echo e($observacao, false); ?>

        </p>
    </div>

    <p>
        <strong> • Obs. o LOCATÁRIO (A),</strong> responderá pelo pagamento de eventuais multa de trânsito (indepedentemente de sua culpabilidade), despesas e danos pessoais 
        e materiais porventura ocasionados a terceiros, durante o período de locação, a responsabilidade por todas as infrações cometidas na condução da carreta alugada, bem como 
        assume a pontuação decorrente.
        <br>
        <strong> • Obs. O LOCATÁRIO (A), </strong>  Carreta está sendo locada limpa, e a mesma deverá retornar limpa, caso isto não ocorra será cobrado o valor de R$ 20,00(Vinte Reais), para realizar a limpeza da mesma.
        
    </p>
</div>


<?php if($receipt_details->show_barcode): ?>
    <div class="row">
        <div class="col-xs-12">
            
            <img class="center-block"
                src="data:image/png;base64,<?php echo e(DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true), false); ?>">
        </div>
    </div>
<?php endif; ?>

<?php if(!empty($receipt_details->footer_text)): ?>
    <div class="row">
        <div class="col-xs-12">
            <?php echo $receipt_details->footer_text; ?>

        </div>
    </div>
<?php endif; ?>
<?php endif; ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/sale_pos/receipts/classic.blade.php ENDPATH**/ ?>