@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Relatório')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Assinaturas
            <small>Relatório</small>
        </h1>
        <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <br>
            <div class="col-md-12 no-print">
                @component('components.filters', ['title' => __('report.filters')])
                    {!! Form::open([
                        'url' => action('\Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController@relatorioAssinaturas'),
                        'method' => 'get',
                    ]) !!}
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('data_inicial', 'Data inícial:') !!}
                            {!! Form::date('data_inicial', $data_inicial ? $data_inicial : '', ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('data_final', 'Data final:') !!}
                            {!! Form::date('data_final', $data_final ? $data_final : '', ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('tipo_plano', 'Tipo de Plano:') !!}
    						{!! Form::select('interval', ['' => 'Todos'] + $intervals, null, ['class' => 'form-control select2']) !!}
                        </div>
                    </div>
                  
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('status', 'Status:') !!}
                            <br>
                            {!! Form::select('status', ['' => 'Todos'] + $statuses, null, ['class' => 'form-control select2']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <br>
                        <button class="btn btn-primary">
                            <i class="fa fa-search"></i>
                            Filtrar
                        </button>
                    </div>

                    {!! Form::close() !!}

                    <div class="col-md-2">
                        <br>
                        <form action="/superadmin/business_relatorio_assinatura" method="get">
                            <button type="submit" class="btn btn-success" name="limpar">Limpar Pesquisa</button>
                        </form>
                    </div>
                @endcomponent
            </div>

            <div class="box-body">
                @can('superadmin')
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>CNPJ</th>
                                <th>Fone</th>
                                {{-- <th>Email</th> --}}
                                <th>Vencimento do Plano</th>
                                <th>Plano</th>
                                <th>Valor do Plano</th>
                                <th>Intervalo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{ $item['nome'] }}</td>
                                    <td>{{ $item['cnpj'] }}</td>
                                    <td>{{ $item['fone'] }}</td>
                                    {{-- <td>{{ $item['email'] }}</td> --}}
                                    <td>{{ @format_date($item['data_vencimento']) }}</td>
                                    {{-- <td>{{ @format_date($item['data_vencimento_teste']) }}</td> --}}
                                    <td>{{ $item['plano'] }}</td>
                                    <td>{{ $item['valor_plano'] }}</td>
                                    <td>{{ $item['interval'] }}</td>
                                    <td>{{ $item['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <caption>Total de resultados: <strong>{{ sizeof($data) }}</strong></caption>
                    </table>
                @endcan
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <br>
                <a href="{{ route('exportar.excel', ['data_inicial' => $data_inicial, 'data_final' => $data_final, 'status' => $status, 'interval' => $interval]) }}"
                    class="btn btn-success">
                    Exportar para Excel
                </a>
            </div>
        </div>

        <div class="row no-print">
            <div class="col-sm-12">
                <button type="button" class="btn btn-primary pull-right" aria-label="Print" onclick="window.print();"><i
                        class="fa fa-print"></i> @lang('messages.print')</button>
            </div>
        </div>



    </section>
    <!-- /.content -->

@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%' // Certifique-se de que o Select2 está usando a largura total
        });
    });
</script>
@endsection