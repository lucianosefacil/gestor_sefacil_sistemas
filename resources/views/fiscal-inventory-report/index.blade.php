@extends('layouts.app')
@section('title', 'Relatorio de Inventario Fiscal')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{{ __('report.stock_report') }}</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    {!! Form::open([
                        'url' => action('ReportController@getStockReport'),
                        'method' => 'get',
                        'id' => 'stock_report_filter_form',
                    ]) !!}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                            ]) !!}
                        </div>
                    </div>



                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('sk_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'sk_date_range',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="stock_report_table">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>@lang('business.product')</th>
                                    {{-- <th>Estoque Inicial</th> --}}
                                    <th>Quantidade Comprada</th>
                                    <th>Quantidade Vendida</th>
                                    <th>Valor de Compra</th>
                                    <th>Valor de Venda</th>
                                    <th>@lang('report.current_stock')</th>
                                </tr>
                            </thead>
                            {{-- <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <th colspan="3"><strong>@lang('sale.total'):</strong></th>
                            <th id="footer_total_stock">

                                </td>
                            <th><span id="footer_total_stock_price" class="display_currency"
                                    data-currency_symbol="true"></span>
                            </th>
                            <th><span id="footer_stock_value_by_sale_price" class="display_currency"
                                    data-currency_symbol="true"></span>
                            </th>
                            <th><span id="footer_stock_value_by_sale_price" class="display_currency"
                                    data-currency_symbol="true"></span>
                            </th>
                            <th><span id="footer_stock_value_by_sale_price" class="display_currency"
                                    data-currency_symbol="true"></span></th>
                        </tr>
                    </tfoot> --}}
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('#sk_date_range').length == 1) {
                const start = moment().subtract(1, 'month');
                const end = moment();

                $('#sk_date_range').daterangepicker({
                        ...dateRangeSettings,
                        startDate: start,
                        endDate: end
                    },
                    function(start, end) {
                        $('#sk_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                            moment_date_format));
                        stock_report_table.ajax.reload();
                    });

                $('#sk_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));

                $('#sk_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    stock_report_table.ajax.reload();
                });

            }

            const columns = [{
                    data: 'sku',
                    name: 'sku'
                },
                {
                    data: 'product',
                    name: 'product'
                },
                {
                    data: 'qtd_purchase',
                    name: 'qtd_purchase',
                    searchable: false
                },
                {
                    data: 'qtd_sell',
                    name: 'qtd_sell',
                    searchable: false
                },
                // { data: 'initial_stock', name: 'initial_stock',  searchable: false},
                {
                    data: 'amount_purchase',
                    name: 'amount_purchase',
                    searchable: false
                },
                {
                    data: 'amount_sell',
                    name: 'amount_sell',
                    searchable: false
                },
                {
                    data: 'currente_stock',
                    name: 'currente_stock',
                    searchable: false
                },
            ];

            stock_report_table = $('#stock_report_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/reports/fiscal-report-submit',
                    data: function(d) {
                        d.location_id = $('#location_id').val();
                        if ($('#sk_date_range').val()) {
                            var date_range = $('#sk_date_range').val().split(' ~ ');
                            d.start_date = date_range[0];
                            d.end_date = date_range[1];
                            d.location_id = $('#location_id').val();

                        } else {
                            d.start_date = '';
                            d.end_date = '';
                            d.location_id = $('#location_id').val();

                        }

                    },
                },
                columns: columns,
                fnDrawCallback: function(oSettings) {},
            });

            if ($('#tax_report_date_filter').length == 1) {
                $('#tax_report_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                    $('#tax_report_date_filter span').html(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                    updateTaxReport();
                });
                $('#tax_report_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $('#tax_report_date_filter').html(
                        '<i class="fa fa-calendar"></i> ' + LANG.filter_by_date
                    );
                });
                updateTaxReport();
            }


            $('#stock_report_filter_form #location_id, #stock_report_filter_form #category_id, #stock_report_filter_form #sub_category_id, #stock_report_filter_form #brand, #stock_report_filter_form #unit,#stock_report_filter_form #view_stock_filter')
                .change(function() {
                    stock_report_table.ajax.reload();
                    // stock_expiry_report_table.ajax.reload();
                    // get_stock_value();
                });

            $('#only_mfg_products').on('ifChanged', function(event) {
                stock_report_table.ajax.reload();
                // lot_report.ajax.reload();
                // stock_expiry_report_table.ajax.reload();
                // items_report_table.ajax.reload();
            });

            $('#purchase_sell_location_filter').change(function() {
                updatePurchaseSell();
            });
            $('#tax_report_location_filter').change(function() {
                updateTaxReport();
            });


        })
    </script>
@endsection
