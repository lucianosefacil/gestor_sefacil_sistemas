<!-- business information here -->
<div class="row">
    @if (!empty($receipt_details->logo))
        <div class="col-xs-3">
            <img width="90" src="{{ $receipt_details->logo }}" class="img img-responsive center-block">
        </div>
        <div class="col-xs-9">
            <h4 class="text-left">
                <!-- Shop & Location Name  -->
                @if (!empty($receipt_details->display_name))
                    {{ $receipt_details->display_name }}
                @endif
            </h4>
            <p>
                @if (!empty($receipt_details->address))
                    <small class="text-center">
                        {!! $receipt_details->address !!}
                    </small>
                @endif

                @if (!empty($receipt_details->contact))
                    @php
                        $contact = preg_replace('/\D/', '', $receipt_details->contact);
                        if (strlen($contact) == 11) {
                            $formatted_contact = preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $contact);
                        } elseif (strlen($contact) == 10) {
                            $formatted_contact = preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $contact);
                        } else {
                            $formatted_contact = $contact;
                        }
                    @endphp
                    <br />{{ $formatted_contact }}
                @endif

                @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                    ,
                @endif
                @if (!empty($receipt_details->website))
                    {{ $receipt_details->website }}
                @endif
                @if (!empty($receipt_details->location_custom_fields))
                    <br>{{ $receipt_details->location_custom_fields }}
                @endif
            </p>
        </div>
    @else
        <div class="col-xs-12">
            <h2 class="text-center">
                <!-- Shop & Location Name  -->
                @if (!empty($receipt_details->display_name))
                    {{ $receipt_details->display_name }}
                @endif
            </h2>
            <p class="text-center">
                @if (!empty($receipt_details->address))
                    <small class="text-center">
                        {!! $receipt_details->address !!}
                    </small>
                @endif
                @if (!empty($receipt_details->contact))
                    <br />{{ $receipt_details->contact }}
                @endif
                @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
                    ,
                @endif
                @if (!empty($receipt_details->website))
                    {{ $receipt_details->website }}
                @endif
                @if (!empty($receipt_details->location_custom_fields))
                    <br>{{ $receipt_details->location_custom_fields }}
                @endif
            </p>
        </div>
    @endif

    <!-- Header text -->
    @if (!empty($receipt_details->header_text))
        <div class="col-xs-12">
            {!! $receipt_details->header_text !!}
        </div>
    @endif

    <!-- business information here -->
    <div class="col-xs-12 text-center">
        <!-- Address -->
        <!-- <p>
        @if (!empty($receipt_details->address))
        <small class="text-center">
            {!! $receipt_details->address !!}
        </small>
        @endif
        @if (!empty($receipt_details->contact))
        <br/>{{ $receipt_details->contact }}
        @endif
        @if (!empty($receipt_details->contact) && !empty($receipt_details->website))
        ,
        @endif
        @if (!empty($receipt_details->website))
        {{ $receipt_details->website }}
        @endif
        @if (!empty($receipt_details->location_custom_fields))
        <br>{{ $receipt_details->location_custom_fields }}
        @endif
        </p> -->
        <p>
            @if (!empty($receipt_details->sub_heading_line1))
                {{ $receipt_details->sub_heading_line1 }}
            @endif
            @if (!empty($receipt_details->sub_heading_line2))
                <br>{{ $receipt_details->sub_heading_line2 }}
            @endif
            @if (!empty($receipt_details->sub_heading_line3))
                <br>{{ $receipt_details->sub_heading_line3 }}
            @endif
            @if (!empty($receipt_details->sub_heading_line4))
                <br>{{ $receipt_details->sub_heading_line4 }}
            @endif
            @if (!empty($receipt_details->sub_heading_line5))
                <br>{{ $receipt_details->sub_heading_line5 }}
            @endif
        </p>
        <p>
            @if (!empty($receipt_details->tax_info1))
                <b>{{ $receipt_details->tax_label1 }}</b> {{ $receipt_details->tax_info1 }}
            @endif

            @if (!empty($receipt_details->tax_info2))
                <b>{{ $receipt_details->tax_label2 }}</b> {{ $receipt_details->tax_info2 }}
            @endif
        </p>

        <!-- Title of receipt -->
        @if ($receipt_details->is_locacao == 1)
            <h4 class="text-center">
                Locação
            </h4>
        @else
            @if (!empty($receipt_details->invoice_heading))
                <h3 class="text-center">
                    {!! $receipt_details->invoice_heading !!}
                </h3>
            @endif
        @endif


        <!-- Invoice  number, Date  -->
        <p style="width: 100% !important; font-size: 12px" class="word-wrap">
            <span class="pull-left text-left word-wrap">
                @if ($receipt_details->is_locacao == 1)
                    <span><strong>Locação N.</strong></span>
                @else
                    @if (!empty($receipt_details->invoice_no_prefix))
                        <b>{!! $receipt_details->invoice_no_prefix !!}</b>
                    @endif
                @endif

                {{ $receipt_details->invoice_no }}

                @if (!empty($receipt_details->types_of_service))
                    <br />
                    <span class="pull-left text-left">
                        <strong>{!! $receipt_details->types_of_service_label !!}:</strong>
                        {{ $receipt_details->types_of_service }}
                        <!-- Waiter info -->
                        @if (!empty($receipt_details->types_of_service_custom_fields))
                            @foreach ($receipt_details->types_of_service_custom_fields as $key => $value)
                                <br><strong>{{ $key }}: </strong> {{ $value }}
                            @endforeach
                        @endif
                    </span>
                @endif

                <!-- Table information-->
                @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                    <br />
                    <span class="pull-left text-left">
                        @if (!empty($receipt_details->table_label))
                            <b>{!! $receipt_details->table_label !!}</b>
                        @endif
                        {{ $receipt_details->table }}

                        <!-- Waiter info -->
                    </span>
                @endif

                <!-- customer info -->
                @if (!empty($receipt_details->customer_name))
                    <br />
                    <b>{{ $receipt_details->customer_label }}</b> {{ $receipt_details->customer_name }} <br>

                    @if ($receipt_details->is_locacao == 0)
                        @if ($cliente->rua)
                            <span>{{ $cliente->rua }}, {{ $cliente->numero }}</span> <br>
                            <span>{{ $cliente->bairro }}</span> <br>
                            <span>{{ $cliente->cep }}</span> <br>
                        @endif
                    @else
                        <span><strong>Documento</strong></span> {{ $receipt_details->cpf_cnpj }}
                    @endif
                @endif

                @if ($receipt_details->is_locacao == 0)
                @if (!empty($receipt_details->customer_info))
                    {!! $receipt_details->customer_info !!}
                @endif
                @endif
                
                @if ($receipt_details->is_locacao == 0)
                    @if (!empty($receipt_details->client_id_label))
                        <br />
                        <b>{{ $receipt_details->client_id_label }}</b> {{ $receipt_details->client_id }}
                    @endif
                @endif

                @if ($receipt_details->is_locacao == 0)
                @if (!empty($receipt_details->customer_tax_label))
                    <br />
                    <b>{{ $receipt_details->customer_tax_label }}</b> {{ $receipt_details->customer_tax_number }}
                @endif
                @if (!empty($receipt_details->customer_custom_fields))
                    <br />{!! $receipt_details->customer_custom_fields !!}
                @endif
                @if (!empty($receipt_details->sales_person_label))
                    <br />
                    <b>{{ $receipt_details->sales_person_label }}</b> {{ $receipt_details->sales_person }}
                @endif
                @if (!empty($receipt_details->customer_rp_label))
                    <br />
                    <strong>{{ $receipt_details->customer_rp_label }}</strong>
                    {{ $receipt_details->customer_total_rp }}
                @endif
                @endif

            </span>

            <span class="pull-right text-left">
                @if ($receipt_details->is_locacao == 1)
                    <span><strong>Data Locação</strong></span>
                    {{ @format_date($data_criacao) }}
                @else
                    <b>{{ $receipt_details->date_label }}</b>
                    {{ $receipt_details->invoice_date }}
                @endif

                <br>

                @if ($receipt_details->is_locacao == 1)
                    <span><strong>Data Devol.</strong></span>
                    {{ @format_date($data_devolucao) }}
                @endif

                <br>

                @if ($receipt_details->is_locacao == 1)
                    <span><strong>Código cliente</strong></span>
                    {{ $receipt_details->client_id }}
                @endif

                @if (!empty($receipt_details->due_date_label))
                    <br><b>{{ $receipt_details->due_date_label }}</b> {{ $receipt_details->due_date ?? '' }}
                @endif

                @if (!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                    <br>
                    @if (!empty($receipt_details->brand_label))
                        <b>{!! $receipt_details->brand_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_brand }}
                @endif

                @if (!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                    <br>
                    @if (!empty($receipt_details->device_label))
                        <b>{!! $receipt_details->device_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_device }}
                @endif

                @if (!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                    <br>
                    @if (!empty($receipt_details->model_no_label))
                        <b>{!! $receipt_details->model_no_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_model_no }}
                @endif

                @if (!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                    <br>
                    @if (!empty($receipt_details->serial_no_label))
                        <b>{!! $receipt_details->serial_no_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_serial_no }}<br>
                @endif
                @if (!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                    @if (!empty($receipt_details->repair_status_label))
                        <b>{!! $receipt_details->repair_status_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_status }}<br>
                @endif

                @if (!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                    @if (!empty($receipt_details->repair_warranty_label))
                        <b>{!! $receipt_details->repair_warranty_label !!}</b>
                    @endif
                    {{ $receipt_details->repair_warranty }}
                    <br>
                @endif

                <!-- Waiter info -->
                @if (!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                    <br />
                    @if (!empty($receipt_details->service_staff_label))
                        <b>{!! $receipt_details->service_staff_label !!}</b>
                    @endif
                    {{ $receipt_details->service_staff }}
                @endif
            </span>
        </p>
    </div>
</div>

@if($receipt_details->is_locacao == 0)
<div class="row">
    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
</div>
@endif

<div class="row" style="font-size: 12px">
    <div class="col-xs-12">
        <table class="table table-responsive" style="margin-bottom: -15px;">
            <thead>
                <tr>
                    <th>
                        @if ($receipt_details->is_locacao == 1)
                            Reboque
                        @else
                            {{ $receipt_details->table_product_label }}
                        @endif
                    </th>
                    <th class="text-right">
                        @if ($receipt_details->is_locacao == 1)
                            Dias
                        @else
                            {{ $receipt_details->table_qty_label }}
                        @endif
                    </th>
                    <th class="text-right">
                        @if ($receipt_details->is_locacao == 1)
                            Valor Diária
                        @else
                            {{ $receipt_details->table_unit_price_label }}
                        @endif
                    </th>
                    <th class="text-right">{{ $receipt_details->table_subtotal_label }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipt_details->lines as $line)
                    <tr>
                        <td style="word-break: break-all;">
                            @if (!empty($line['image']))
                                <img src="{{ $line['image'] }}" alt="Image" width="30"
                                    style="float: left; margin-right: 8px;">
                            @endif
                            {{ $line['name'] }} {{ $line['product_variation'] }} {{ $line['variation'] }}
                            @if (!empty($line['sub_sku']))
                                , {{ $line['sub_sku'] }}
                                @endif @if (!empty($line['brand']))
                                    , {{ $line['brand'] }}
                                    @endif @if (!empty($line['cat_code']))
                                        , {{ $line['cat_code'] }}
                                    @endif
                                    @if (!empty($line['product_custom_fields']))
                                        , {{ $line['product_custom_fields'] }}
                                    @endif
                                    @if (!empty($line['sell_line_note']))
                                        ({{ $line['sell_line_note'] }})
                                    @endif
                                    @if (!empty($line['lot_number']))
                                        <br> {{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                    @endif
                                    @if (!empty($line['product_expiry']))
                                        , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                    @endif

                                    @if (!empty($line['warranty_name']))
                                        <br><small>{{ $line['warranty_name'] }} </small>
                                        @endif @if (!empty($line['warranty_exp_date']))
                                            <small>- {{ @format_date($line['warranty_exp_date']) }} </small>
                                        @endif
                                        @if (!empty($line['warranty_description']))
                                            <small> {{ $line['warranty_description'] ?? '' }}</small>
                                        @endif
                        </td>
                        <td class="text-right">{{ $line['quantity'] }} {{ $line['units'] }} </td>
                        <td class="text-right">{{ $line['unit_price'] }}</td>
                        <td class="text-right">{{ $line['line_total'] }}</td>
                    </tr>
                    @if (!empty($line['modifiers']))
                        @foreach ($line['modifiers'] as $modifier)
                            <tr>
                                <td>
                                    {{ $modifier['name'] }} {{ $modifier['variation'] }}
                                    @if (!empty($modifier['sub_sku']))
                                        , {{ $modifier['sub_sku'] }}
                                        @endif @if (!empty($modifier['cat_code']))
                                            , {{ $modifier['cat_code'] }}
                                        @endif
                                        @if (!empty($modifier['sell_line_note']))
                                            ({{ $modifier['sell_line_note'] }})
                                        @endif
                                </td>
                                <td class="text-right">{{ $modifier['quantity'] }} {{ $modifier['units'] }} </td>
                                <td class="text-right">{{ $modifier['unit_price_inc_tax'] }}</td>
                                <td class="text-right">{{ $modifier['line_total'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<div class="row" style="font-size: 12px; padding: 10px">
    @if($receipt_details->is_locacao == 0)
    <div class="col-xs-6">
        <table class="table table-condensed">
            @if (!empty($receipt_details->payments))
                @foreach ($receipt_details->payments as $payment)
                    <tr>
                        <td style="font-weight: bold">{{ $payment['method'] }}</td>
                        <td class="text-right">{{ $payment['amount'] }}</td>
                        <td> - {{ $payment['date'] }}</td>
                    </tr>
                @endforeach
            @endif

            <!-- Total Paid-->
            @if (!empty($receipt_details->total_paid))
                <tr>
                    <th>
                        {!! $receipt_details->total_paid_label !!}
                    </th>
                    <td class="text-right">
                        {{ $receipt_details->total_paid }}
                    </td>
                </tr>
            @endif

            <!-- Total Due-->
            @if (!empty($receipt_details->total_due))
                <tr>
                    <th>
                        {!! $receipt_details->total_due_label !!}
                    </th>
                    <td class="text-right">
                        {{ $receipt_details->total_due }}
                    </td>
                </tr>
            @endif

            @if (!empty($receipt_details->all_due))
                <tr>
                    <th>
                        {!! $receipt_details->all_bal_label !!}
                    </th>
                    <td class="text-right">
                        {{ $receipt_details->all_due }}
                    </td>
                </tr>
            @endif
        </table>
        {{ $receipt_details->additional_notes }}
    </div>
    @else

    <div class="col-xs-6">

    </div>
    @endif

    <div class="col-xs-6" style="padding: 10px">
        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr class="color-555">
                        <th style="width:70%; padding: 5px !important;">
                            <strong>Subtotal</strong>
                        </th>
                        <td class="text-right" style="padding: 5px !important;">
                            {{ $receipt_details->total_paid }}
                        </td>
                    </tr>
                    
                    
                    @if (!empty($receipt_details->total_quantity_label))
                        <tr class="color-555">
                            <th style="width:70%">
                                {!! $receipt_details->total_quantity_label !!}
                            </th>
                            <td class="text-right">
                                {{ $receipt_details->total_quantity }}
                            </td>
                        </tr>
                    @endif

                    <!-- Shipping Charges -->
                    @if (!empty($receipt_details->shipping_charges))
                        <tr>
                            <th style="width:70%">
                                {!! $receipt_details->shipping_charges_label !!}
                            </th>
                            <td class="text-right">
                                {{ $receipt_details->shipping_charges }}
                            </td>
                        </tr>
                    @endif

                    <!-- Discount -->
                    @if (!empty($receipt_details->discount))
                        <tr>
                            <th style="width:70%; padding: 3px !important;">
                                {!! $receipt_details->discount_label !!}
                            </th>
                            <td class="text-right" style="width:70%; padding: 3px !important;">
                                (-) {{ $receipt_details->discount }}
                            </td>
                        </tr>
                    @endif

                    @if (!empty($receipt_details->reward_point_label))
                        <tr>
                            <th>
                                {!! $receipt_details->reward_point_label !!}
                            </th>

                            <td class="text-right">
                                (-) {{ $receipt_details->reward_point_amount }}
                            </td>
                        </tr>
                    @endif

                    <!-- Tax -->
                    @if (!empty($receipt_details->tax))
                        <tr>
                            <th>
                                {!! $receipt_details->tax_label !!}
                            </th>
                            <td class="text-right">
                                (+) {{ $receipt_details->tax }}
                            </td>
                        </tr>
                    @endif

                    @if (!empty($receipt_details->round_off_label))
                        <tr>
                            <th>
                                {!! $receipt_details->round_off_label !!}
                            </th>
                            <td class="text-right">
                                {{ $receipt_details->round_off }}
                            </td>
                        </tr>
                    @endif

                    <!-- Total -->
                    <tr>
                        <th style="width:70%; padding: 3px !important;">
                            {!! $receipt_details->total_label !!}
                        </th>
                        <td class="text-right" style="width:70%; padding: 3px !important;">
                            {{ $receipt_details->total }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($receipt_details->is_locacao == 1)
<div class="" style="font-size: 12px">
    <p class="text-center">_________________________________________________ </p>
    <p class="text-center">Assinatura do Cliente</p>

    <div class="card" style="width: 100%; min-height: 100px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin: 10px 0;">
        <strong>Obs. de Pagamento:</strong>
        <p style="margin-top: 5px;">
            {{ $observacao }}
        </p>
    </div>

    <p>
        <strong> • Obs. o LOCATÁRIO (A),</strong> responderá pelo pagamento de eventuais multa de trânsito (indepedentemente de sua culpabilidade), despesas e danos pessoais 
        e materiais porventura ocasionados a terceiros, durante o período de locação, a responsabilidade por todas as infrações cometidas na condução da carreta alugada, bem como 
        assume a pontuação decorrente.
        <br>
        <strong> • Obs. O LOCATÁRIO (A), </strong>  Carreta está sendo locada limpa, e a mesma deverá retornar limpa, caso isto não ocorra será cobrado o valor de R$ 20,00(Vinte Reais), para realizar a limpeza da mesma.
        {{-- <strong>Carreta esta sendo locada limpa, e a mesma deverá retornar limpa, caso isto não ocorra será cobrado o valor de R$ 20,00(Vinte Reais), para realizar a limpeza da mesma.</strong>  --}}
    </p>
</div>


@if ($receipt_details->show_barcode)
    <div class="row">
        <div class="col-xs-12">
            {{-- Barcode --}}
            <img class="center-block"
                src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
        </div>
    </div>
@endif

@if (!empty($receipt_details->footer_text))
    <div class="row">
        <div class="col-xs-12">
            {!! $receipt_details->footer_text !!}
        </div>
    </div>
@endif
@endif