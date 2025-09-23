@extends('layouts.app')
@section('title', __( 'lang_v1.quotation'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.list_quotations')
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('created_by',  __('report.user') . ':') !!}
                {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>

        {{-- teste de fatura  --}}
        {{-- <div class="row">
            <div class="col-sm-12 col-xs-12">
              <h4>{{ __('sale.payment_info') }}:</h4>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="table-responsive">
                <table class="table bg-gray">
                  <tr class="bg-green">
                    <th>#</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>Vencimento</th>
                    <th>{{ __('sale.amount') }}</th>
                    <th>{{ __('sale.payment_mode') }}</th>
                    <th>{{ __('sale.payment_note') }}</th>
                  </tr>
                  @php
                    $total_paid = 0;
                  @endphp
                  @foreach($sell->payment_lines as $payment_line)
                    @php
                      if($payment_line->is_return == 1){
                        $total_paid -= $payment_line->amount;
                      } else {
                        $total_paid += $payment_line->amount;
                      }
                    @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ @format_date($payment_line->vencimento) }}</td>
                      <td>{{ $payment_line->payment_ref_no }}</td>
                      <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                      <td>
                        {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                        @if($payment_line->is_return == 1)
                          <br/>
                          ( {{ __('lang_v1.change_return') }} )
                        @endif
                      </td>
                      <td>@if($payment_line->note) 
                        {{ ucfirst($payment_line->note) }}
                        @else
                        --
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </table>
              </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="table-responsive">
                <table class="table bg-gray">
                  <tr>
                    <th>{{ __('sale.total') }}: </th>
                    <td></td>
                    <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->final_total }}</span></td>
                  </tr>
                  <tr>
                    <th>{{ __('sale.discount') }}:</th>
                    <td><b>(-)</b></td>
                    <td><div class="pull-right"><span class="display_currency" @if( $sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span> @if( $sell->discount_type == 'percentage') {{ '%'}} @endif</span></div></td>
                  </tr>
                  @if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge))
                    <tr>
                      <th>{{ __('lang_v1.packing_charge') }}:</th>
                      <td><b>(+)</b></td>
                      <td><div class="pull-right"><span class="display_currency" @if( $sell->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->packing_charge }}</span> @if( $sell->packing_charge_type == 'percent') {{ '%'}} @endif </div></td>
                    </tr>
                  @endif
                  @if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) )
                    <tr>
                      <th>{{session('business.rp_name')}}:</th>
                      <td><b>(-)</b></td>
                      <td> <span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->rp_redeemed_amount }}</span></td>
                    </tr>
                  @endif
                  <tr>
                    <th>{{ __('sale.order_tax') }}:</th>
                    <td><b>(+)</b></td>
                    <td class="text-right">
                      @if(!empty($order_taxes))
                        @foreach($order_taxes as $k => $v)
                          <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                        @endforeach
                      @else
                      0.00
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>{{ __('sale.shipping') }}: @if($sell->shipping_details)({{$sell->shipping_details}}) @endif</th>
                    <td><b>(+)</b></td>
                    <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->shipping_charges }}</span></td>
                  </tr>
                  <tr>
                    <th>{{ __('lang_v1.round_off') }}: </th>
                    <td></td>
                    <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->round_off_amount }}</span></td>
                  </tr>
                  <tr>
                    <th>{{ __('sale.total_payable') }}: </th>
                    <td></td>
                    <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->final_total }}</span></td>
                  </tr>
                  <tr>
                    <th>{{ __('sale.total_paid') }}:</th>
                    <td></td>
                    <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $total_paid }}</span></td>
                  </tr>
                  <tr>
                    <th>{{ __('sale.total_remaining') }}:</th>
                    <td></td>
                    <td>
                      <!-- Converting total paid to string for floating point substraction issue -->
                      @php
                        $total_paid = (string) $total_paid;
                      @endphp
                      <span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell->final_total - $total_paid }}</span></td>
                  </tr>
                </table>
              </div>
            </div>
          </div> --}}
          {{-- fim do teste de fatura  --}}
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action('SellPosController@create')}}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('purchase.ref_no')</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('sale.location')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": '/sells/draft-dt?is_quotation=1',
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();

                if($('#created_by').length) {
                    d.created_by = $('#created_by').val();
                }
            }
        },
        columnDefs: [ {
            "targets": 4,
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'name', name: 'contacts.name'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#purchase_table'));
        }
    });
    
    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by',  function() {
        sell_table.ajax.reload();
    });
});
</script>
	
@endsection