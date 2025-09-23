@foreach ($ordem->itens as $row_count => $product)

@php
$row_count += 1;
@endphp

<tr class="product_row" data-row_index="{{$row_count}}">
    <td>
        @php
        $product_name = $product->produto->name;
        @endphp

        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer">
                {!! $product_name !!}
                &nbsp;
            </span>
        </div>

        <input type="hidden" class="product_type" name="products[{{$row_count}}][product_type]" value="{{$product->produto->type}}">

        @php
        $hide_tax = 'hide';
        if(session()->get('business.enable_inline_tax') == 1){
        $hide_tax = '';
        }

        $unit_price_inc_tax = $product->valor_unitario;
        if($hide_tax == 'hide'){
        $tax_id = null;
        $unit_price_inc_tax = $product->valor_unitario;
        }
        @endphp

        <input type="hidden" class="" name="products[{{$row_count}}][item_tax]" value="0.00">
        <input type="hidden" class="" name="products[{{$row_count}}][tax_id]" value={{$tax_id}}>


        @if(!empty($product->lot_numbers))
        <select class="form-control lot_number input-sm" name="products[{{$row_count}}][lot_no_line_id]" @if(!empty($product->transaction_sell_lines_id)) disabled @endif>
            <option value="">@lang('lang_v1.lot_n_expiry')</option>
            @foreach($product->lot_numbers as $lot_number)
            @php
            $selected = "";
            if($lot_number->purchase_line_id == $lot_no_line_id){
            $selected = "selected";

            $max_qty_rule = $lot_number->qty_available;
            $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit ]);
            }

            $expiry_text = '';
            if($exp_enabled == 1 && !empty($lot_number->exp_date)){
            if( \Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)) ){
            $expiry_text = '(' . __('report.expired') . ')';
            }
            }

            //preselected lot number if product searched by lot number
            if(!empty($purchase_line_id) && $purchase_line_id == $lot_number->purchase_line_id) {
            $selected = "selected";

            $max_qty_rule = $lot_number->qty_available;
            $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit ]);
            }
            @endphp
            <option value="{{$lot_number->purchase_line_id}}" data-qty_available="{{$lot_number->qty_available}}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ])" {{$selected}}>@if(!empty($lot_number->lot_number) && $lot_enabled == 1){{$lot_number->lot_number}} @endif @if($lot_enabled == 1 && $exp_enabled == 1) - @endif @if($exp_enabled == 1 && !empty($lot_number->exp_date)) @lang('product.exp_date'): {{@format_date($lot_number->exp_date)}} @endif {{$expiry_text}}</option>
            @endforeach
        </select>
        @endif
    </td>

    <td>
        {{-- If edit then transaction sell lines will be present --}}
        @if(!empty($product->transaction_sell_lines_id))
        <input type="hidden" name="products[{{$row_count}}][transaction_sell_lines_id]" class="form-control" value="{{$product->transaction_sell_lines_id}}">
        @endif

        <input type="hidden" name="products[{{$row_count}}][product_id]" class="form-control product_id" value="{{$product->produto->id}}">

        <input type="hidden" value="{{$product->variation_id}}" name="products[{{$row_count}}][variation_id]" class="row_variation_id">

        <input type="hidden" value="{{$product->produto->enable_stock}}" name="products[{{$row_count}}][enable_stock]">

        @if(empty($product->quantity_ordered))
        @php
        $product->quantity_ordered = 1;
        @endphp
        @endif

        @php
        $multiplier = 1;
        $allow_decimal = true;
        if($product->unit_allow_decimal != 1) {
        $allow_decimal = false;
        }
        @endphp

        <div class="input-group input-number">
            <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap input_quantity" value="{{@format_quantity($product->quantidade)}}" name="products[{{$row_count}}][quantity]" data-allow-overselling="@if(empty($pos_settings['allow_overselling'])){{'false'}}@else{{'true'}}@endif" @if($allow_decimal) data-decimal=1 @else data-decimal=0 data-rule-abs_digit="true" data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')" @endif data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if($product->enable_stock && empty($pos_settings['allow_overselling']) )
            data-rule-max-value="{{$max_qty_rule}}" data-qty_available="{{$product->qty_available}}" data-msg-max-value="{{$max_qty_msg}}"
            data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit ])"
            @endif
            >
            <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
        </div>

        <input type="hidden" name="products[{{$row_count}}][product_unit_id]" value="{{$product->unit_id}}">

        <input type="hidden" class="base_unit_multiplier" name="products[{{$row_count}}][base_unit_multiplier]" value="{{$multiplier}}">

        <input type="hidden" class="hidden_base_unit_sell_price" value="{{$product->valor_unitario / $multiplier}}">

        {{-- Hidden fields for combo products --}}
        @if($product->product_type == 'combo')

        @foreach($product->combo_products as $k => $combo_product)

        @if(isset($action) && $action == 'edit')
        @php
        $combo_product['qty_required'] = $combo_product['quantity'] / $product->quantity_ordered;

        $qty_total = $combo_product['quantity'];
        @endphp
        @else
        @php
        $qty_total = $combo_product['qty_required'];
        @endphp
        @endif

        <input type="hidden" name="products[{{$row_count}}][combo][{{$k}}][product_id]" value="{{$combo_product['product_id']}}">

        <input type="hidden" name="products[{{$row_count}}][combo][{{$k}}][variation_id]" value="{{$combo_product['variation_id']}}">

        <input type="hidden" class="combo_product_qty" name="products[{{$row_count}}][combo][{{$k}}][quantity]" data-unit_quantity="{{$combo_product['qty_required']}}" value="{{$qty_total}}">

        @if(isset($action) && $action == 'edit')
        <input type="hidden" name="products[{{$row_count}}][combo][{{$k}}][transaction_sell_lines_id]" value="{{$combo_product['id']}}">
        @endif

        @endforeach
        @endif
    </td>
    <input type="hidden" name="products[{{$row_count}}][unit_price]" class="form-control" value="{{@num_format($product->valor_unitario)}}">
    @if(!empty($pos_settings['inline_service_staff']))
    <td>
        <div class="form-group">
            <div class="input-group">
                {!! Form::select("products[" . $row_count . "][res_service_staff_id]", $waiters, !empty($product->res_service_staff_id) ? $product->res_service_staff_id : null, ['class' => 'form-control select2 order_line_service_staff', 'placeholder' => __('restaurant.select_service_staff'), 'required' => (!empty($pos_settings['is_service_staff_required']) && $pos_settings['is_service_staff_required'] == 1) ? true : false ]); !!}
            </div>
        </div>
    </td>
    @endif
    <td class="{{$hide_tax}}">
        <input type="text" name="products[{{$row_count}}][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{@num_format($unit_price_inc_tax)}}" @if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$unit_price_inc_tax}}" data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($unit_price_inc_tax)])}}" @endif>
    </td>
    <td class="text-center v-center">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp

        <input type="{{$subtotal_type}}" class="form-control pos_line_total @if(!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{@num_format($product->quantidade*$unit_price_inc_tax )}}">
        <span class="display_currency pos_line_total_text @if(!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{($product->quantidade * $unit_price_inc_tax)}}</span>
    </td>
    <td class="text-center">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>
@endforeach



{{-- servicos --}}

@foreach ($ordem->servicos as $row_count_servico => $servico)
@php
$row_count_servico += 1;
@endphp

<tr class="servico_row" data-row_index="{{$row_count_servico}}">
    <td>
        @php
        $servico_name = $servico->servico->nome;
        @endphp

        <div title="@lang('lang_v1.pos_edit_product_price_help')">
            <span class="text-link text-info cursor-pointer">
                {!! $servico_name !!}
                &nbsp;
            </span>
        </div>
        <input type="hidden" class="" name="services[{{$row_count_servico}}][servico_id]" value="{{$servico->servico_id}}">
    </td>
    <input type="hidden" class="" name="services[{{$row_count_servico}}][valor_unitario]" value="{{@num_format($servico->valor_unitario)}}">

    <td>
        <div class="input-group">
            <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text" data-min="1" class="form-control pos_quantity mousetrap" disabled value="{{@format_quantity($servico->quantidade)}}" 
            name="services[{{$row_count_servico}}]"
            data-qty_available="{{$servico->quantidade}}"
            data-msg_max_default="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $servico->quantidade, 'unit' => $servico->unit ])">
            <span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
        </div>
        <input type="hidden" name="services[{{$row_count_servico}}][quantidade_servico]" value="{{@format_quantity($servico->quantidade)}}">
    </td>

    <td class="text-center v-center">
        @php
        $subtotal_type = !empty($pos_settings['is_pos_subtotal_editable']) ? 'text' : 'hidden';

        @endphp
        <input type="{{$subtotal_type}}" class="form-control pos_line_total @if(!empty($pos_settings['is_pos_subtotal_editable'])) input_number @endif" value="{{@num_format($servico->quantidade*$servico->valor_unitario )}}">
        <span class="display_currency pos_line_total_text @if(!empty($pos_settings['is_pos_subtotal_editable'])) hide @endif" data-currency_symbol="true">{{($servico->quantidade * $servico->valor_unitario)}}</span>
    </td>
    <input type="hidden" name="services[{{$row_count_servico}}][sub_total]" class="form-control" value="{{@num_format($servico->quantidade*$servico->valor_unitario )}}">
    <td class="text-center">
        <i class="fa fa-times text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>
    </td>
</tr>
@endforeach

<script type="text/javascript">
    $(document).ready(function() {
        $('input.expiry_datepicker').datepicker({
            autoclose: true
            , format: datepicker_date_format
        });
    });

</script>
