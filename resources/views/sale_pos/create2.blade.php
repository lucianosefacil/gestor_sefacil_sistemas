@extends('layouts.app')

@section('title', 'PDV')

@section('content')
<section class="content no-print">
    <input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">

    {{-- <input type="text" id="tipo_venda" value="{{$tipo_venda}}"> --}}

    @if(!empty($pos_settings['allow_overselling']))
    <input type="hidden" id="is_overselling_allowed">
    @endif
    @if(session('business.enable_rp') == 1)
    <input type="hidden" id="reward_point_enabled">
    @endif
    @php
    $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
    $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
    @endphp
    {!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_pos_sell_form' ]) !!}
    <div class="row mb-12">
        <div class="col-md-12">
            <div class="row">
                <div class="@if(empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-12 col-md-offset-1 @endif no-padding pr-12">
                    <div class="box box-solid mb-12">
                        <div class="box-body pb-0">
                            {!! Form::hidden('location_id', $default_location->id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_accounts' => $default_location->default_payment_accounts]); !!}
                            <!-- sub_type -->
                            {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                            <input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
                            @include('sale_pos.partials.pos_form')

                            @include('sale_pos.partials.pos_form_totals')

                            @include('sale_pos.partials.payment_modal')

                            @if(empty($pos_settings['disable_suspend']))
                            @include('sale_pos.partials.suspend_note_modal')
                            @endif

                            @if(empty($pos_settings['disable_recurring_invoice']))
                            @include('sale_pos.partials.recurring_invoice_modal')
                            @endif
                        </div>
                    </div>
                </div>

                <input type="hidden" id="cpf" value="" name="cpf">
                <input type="hidden" id="valor_recebido" value="0" name="valor_recebido">

                <input type="hidden" value="{{ isset($pos_settings['disabled_payment_partial']) ? $pos_settings['disabled_payment_partial'] : '0' }}" id="disabled_payment_partial">

                <input type="hidden" id="token" value="{{csrf_token()}}">

                <div class="col-md-3">
                    {{-- <div class="col-md-12" style="height: 150px;">
                        <h3>Código de Barras</h3>
                        <input type="text" class="codigo_barras box box-solid text-center" style="border-radius: 4px; height: 65%; font-size:25px;">
                    </div> --}}
                    <div class="col-md-12" style="height: 150px;">
                        <h4>Quantidade</h4>
                        <input type="text" class="quantidade box box-solid text-center" style="border-radius: 4px; height: 65%; font-size:25px;">
                    </div>
                    <div class="col-md-12" style="height: 150px;">
                        <h4>Valor Unitário</h4>
                        <input type="text" class="valor_unitario box box-solid text-center" style="border-radius: 4px; height: 65%; font-size:25px;">
                    </div>
                    <div class="col-md-12" style="height: 150px;">
                        <h4>SubTotal</h4>
                        <input type="text" class="sub_total box box-solid text-center" style="border-radius: 4px; height: 65%; font-size:25px;">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="col-md-12 box box-solid" style="margin-top:220px">
                        @if($business_details->logo != '')
                        <img class="" src="/uploads/business_logos/{{$business_details->logo}}">
                        @else
                        <img class="" src="/imgs/logo.png" style="height: 120px">
                        @endif
                    </div>
                    {{-- <div class="col-md-12 box box-solid" style="height: 150px">
                        @if($business_details->logo != '')
                        <img class="logo box box-solid" src="/uploads/business_logos/{{$business_details->logo}}" style="height: 170px; width:190px; margin-top:220px; margin-left:25px">
                    @else
                    <img class="logo box box-solid" src="/imgs/logo.png" style="height: 120px">
                    @endif
                </div> --}}
            </div>
        </div>
    </div>
    </div>
    @include('sale_pos.partials.pos_form_actions')
    {!! Form::close() !!}
</section>
@include('sale_pos.partials.recent_transactions_modal')
<div class="modal fade sangria_suprimento_modal" tabindex="-1" role="dialog" 	
	aria-labelledby="gridSystemModalLabel">
</div> 

<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
</div>
<!-- This will be printed -->
{{-- <section class="invoice print_section" id="receipt_section">
</section> --}}

{{-- @if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif --}}
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>

{{-- @include('sale_pos.partials.configure_search_modal') --}}

{{-- @include('sale_pos.partials.weighing_scale_modal') --}}
{{-- <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div> --}}
@stop

@section('javascript')
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
@include('sale_pos.partials.keyboard_shortcuts')

<!-- Call restaurant module if defined -->
@if(in_array('tables', $enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
@endif
<!-- include module js -->
@if(!empty($pos_module_data))
@foreach($pos_module_data as $key => $value)
@if(!empty($value['module_js_path']))
@includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
@endif
@endforeach
@endif
@endsection
