@extends('layouts.app')
@section('title', __('Locações'))

@section('content')

    <section class="content-header no-print">
        <h1>@lang('Locações')</h1>
    </section>

    <section class="content no-print">

        <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">

        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <!-- Filtro por Local -->
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_location_id', __('purchase.business_location') . ':') !!}
                        {!! Form::select('sell_list_filter_location_id', $business_locations, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>

                <!-- Filtro por Cliente -->
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_customer_id', __('contact.customer') . ':') !!}
                        {!! Form::select('sell_list_filter_customer_id', $customers, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>

                <!-- Filtro por Data -->
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('sell_list_filter_date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'readonly',
                        ]) !!}
                    </div>
                </div>

                <!-- Filtro por Usuário -->
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('created_by', __('report.user') . ':') !!}
                        {!! Form::select('created_by', $sales_representative, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                        ]) !!}
                    </div>
                </div>

                <!-- Filtro por Status -->
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_status', __('Status') . ':') !!}
                        {!! Form::select(
                            'sell_list_filter_status',
                            ['' => __('lang_v1.all'), 'aberta' => __('Aberta'), 'fechada' => __('Fechada')],
                            null,
                            [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_status_pagamento', __('Status de Pagamento') . ':') !!}
                        {!! Form::select(
                            'sell_list_filter_status_pagamento',
                            ['' => __('lang_v1.all'), 'pendente' => __('Pendente'), 'pago' => __('Pago')],
                            null,
                            [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                            ],
                        ) !!}
                    </div>
                </div>

            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
        <div class="box-tools">
            <a class="btn btn-block btn-primary" href="{{action('SellController@create', ['status' => 'locacao']) }}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="locacoes_table">
                    <thead>
                        <tr>
                            {{-- <th>@lang('messages.date')</th> --}}
                            <th>@lang('purchase.ref_no')</th>
                            <th>Status Pagamento</th>
                            <th>@lang('sale.customer_name')</th>
                            <th>Data Abertura</th>
                            <th>Valor</th>
                            <th>Dias</th>
                            <th>Excedentes</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
    </section>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Filtro por intervalo de datas
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    locacoes_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                locacoes_table.ajax.reload();
            });

            // Inicializando o DataTable
            locacoes_table = $('#locacoes_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('locacoes.index') }}",
                    data: function(d) {
                        // Coletando os filtros

                        if ($('#sell_list_filter_status').val()) {
                            d.status = $('#sell_list_filter_status').val();
                        }

                        if ($('#sell_list_filter_status_pagamento').val()) {
                            d.status_pagamento = $('#sell_list_filter_status_pagamento').val();
                        }

                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }

                        if ($('#sell_list_filter_location_id').val()) {
                            d.location_id = $('#sell_list_filter_location_id').val();
                        }

                        if ($('#sell_list_filter_customer_id').val()) {
                            d.customer_id = $('#sell_list_filter_customer_id').val();
                        }

                        if ($('#created_by').val()) {
                            d.created_by = $('#created_by').val();
                        }
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'status_pagamento',
                        name: 'status_pagamento'
                    },
                    
                    {
                        data: 'name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'data_abertura',
                        name: 'data_abertura'
                    },
                    {
                        data: 'valor',
                        name: 'valor'
                    },
                    {
                        data: 'dias_em_locacao',
                        name: 'dias_em_locacao'
                    },
                    {
                        data: 'excedentes',
                        name: 'excedentes'
                    },
                    {
                        data: 'valor_total',
                        name: 'valor_total'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Atualizar DataTable ao mudar os filtros
            $(document).on('change', '#sell_list_filter_status, #sell_list_filter_status_pagamento,  #sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by',
                function() {
                    locacoes_table.ajax.reload();
                });


            $(document).on('click', '.delete-locacao', function(e) {
                e.preventDefault();
                var href = $(this).attr('href'); // Captura o atributo href

                swal({
                    title: 'Deseja realmente excluir?',
                    icon: 'error',
                    buttons: ["Não", "Sim"],
                }).then((sim) => {
                    if (sim) {
                        $.ajax({
                            url: href,
                            type: 'DELETE', // Método DELETE
                            data: {
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content') // Token CSRF
                            },
                            success: function(result) {
                                if (result.success) {
                                    swal(
                                        'Excluído!', result.msg, 'success'
                                    ).then(() => {
                                        $('#locacoes_table').DataTable().ajax
                                            .reload(null, false);
                                    });
                                } else {
                                    swal(
                                        'Erro!', result.msg, 'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                swal(
                                    'Erro!',
                                    'Ocorreu um erro ao tentar excluir a locação.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.reabrir-locacao', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                swal({
                    title: 'Deseja realmente reabrir?',
                    icon: 'warning',
                    buttons: ["Não", "Sim"],
                }).then((sim) => {
                    if (sim) {
                        $.ajax({
                            url: href,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(result) {
                                console.log(result)
                                if (result.success) {
                                    swal('Reaberta!', result.msg, 'success');
                                    $('#locacoes_table').DataTable().ajax
                                        .reload(null, false);
                                } else {
                                    swal('Erro!', result.msg, 'error');
                                }
                            },
                            error: function(xhr) {
                                swal('Erro!',
                                    'Não foi possível reabrir a locação.', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.btn-finalizar', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                swal({
                    title: 'Atencão?',
                    text: 'Uma vez finalizada não poderá mais reabrir, e não aparecera mais na lista de Locações.',
                    icon: 'warning',
                    buttons: ["Não", "Sim"],
                }).then((sim) => {
                    if (sim) {
                        // Redireciona para a URL ao confirmar
                        window.location.href = href;
                    }
                });
            });



        });
    </script>
@endsection
