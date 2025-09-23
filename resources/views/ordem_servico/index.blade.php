@extends('layouts.app')
@section('title', 'Lista de Ordem de Servico')

@section('content')
@include('ordem_servico.nav.index')

<!-- Content Header (Page header) -->
<section class="content-header">

</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters'), 'closed' => true])
            <div class="col-md-3" id="location_filter">
                <div class="form-group">
                    {!! Form::label('location_id', __('Localização') . ':') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'ordem_location_id', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('cliente_id', __('contact.customer') . ':') !!}
                    {!! Form::select('cliente_id', $clientes, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ordem_cliente_id' ,'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status_id', 'Status' . ':') !!}
                    {!! Form::select('status_id', $status, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ordem_status_id', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ordem_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('ordem_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'),'id' => 'ordem_date_range', 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('funcionario_id', 'Profissional' . ':') !!}
                    {!! Form::select('funcionario_id', $funcionario, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ordem_funcionario_id', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>

            {{-- --include module filter --}}
            @if(!empty($pos_module_data))
            @foreach($pos_module_data as $key => $value)
            @if(!empty($value['view_path']))
            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
            @endif
            @endforeach
            @endif

            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Todas as Ordem de Serviço'])
    @can('user.create')
    @slot('tool')
    <div class="box-tools">
        <a class="btn btn-block btn-primary" href="{{ route('ordem-servico.create') }}">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
    </div>
    @endslot
    @endcan
    @can('user.view')
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>@lang('lang_v1.created_at')</th>
                            <th>Venda</th>
                            <th>@lang('Status')</th>
                            <th>
                                @lang('Cliente')
                            </th>
                            <th>@lang('business.location')</th>
                            <th>@lang('Valor')</th>
                            <th>@lang('N Nfe')</th>
                            <th>
                                @lang('Data entrega prevista')
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @endcan
    @endcomponent
    <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('#ordem_date_range').daterangepicker(
            dateRangeSettings
            , function(start, end) {
                $('#ordem_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                users_table.ajax.reload();
            }
        );
        $('#ordem_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#ordem_date_range').val('');
            users_table.ajax.reload();
        });
        users_table = $('#users_table').DataTable({
            processing: true
            , serverSide: true
            , "ajax": {
                "url": "/ordem-servico"
                , "data": function(d) {
                    if ($('#ordem_date_range').val()) {
                        var start = $('#ordem_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#ordem_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                    d.cliente_id = $('#ordem_cliente_id').val();
                    d.business_locations = $('#ordem_location_id').val();
                    d.status_id = $('#ordem_status_id').val();
                    d.funcionario_id = $('#ordem_funcionario_id').val();

                    d = __datatable_ajax_callback(d);
                }
            }

            , columnDefs: [{
                "targets": [4]
                , "orderable": false
                , "searchable": false
            }]
            , "columns": [
                    {"data": "action"},
                    {"data": "created_at"},
                    {"data": "venda_id"},  
                    {"data": "status_id"}, 
                    {"data": "cliente_id"}, 
                    {"data": "location_id", visible: false}, 
                    {"data": "valor"},
                    {"data": "nfe_id"}, 
                    {"data": "data_entrega"}, 
                ]
        });
    });

    $('#users_table').on('click', 'a.delete-os', function(e) {
        e.preventDefault();
        swal({
            title: LANG.sure
            , icon: "warning"
            , buttons: true
            , dangerMode: true
        , }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: "DELETE"
                    , url: href
                    , dataType: "json"
                    , success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            // users_table.ajax.reload();
                            window.location.reload(true);
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    $(document).on('change', '#ordem_cliente_id, #ordem_status_id, #ordem_location_id, #ordem_funcionario_id', function() {
        users_table.ajax.reload();
    });

</script>
@endsection
