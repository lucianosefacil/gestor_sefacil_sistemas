@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Relatório')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Certificados
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
        

        <div class="col-md-12 no-print">
            @component('components.filters', ['title' => __('report.filters')])

            {!! Form::open(['url' => action('\Modules\Superadmin\Http\Controllers\BusinessController@certificados'), 'method' => 'get']) !!}

            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('data_inicial', 'Data inícial:') !!}
                    {!! Form::date('data_inicial', $data_inicial ? $data_inicial : '', ['class' => 'form-control']); !!}
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('data_final', 'Data final:') !!}
                    {!! Form::date('data_final', isset($data_final) ? $data_final : '', ['class' => 'form-control']); !!}
                </div>
            </div>

            <div class="col-md-4">
                <br>
                <button class="btn btn-primary">
                    <i class="fa fa-search"></i>
                    Filtrar
                </button>

            </div>

            {!! Form::close() !!}


            @endcomponent
        </div>


        <div class="box-body">
            @can('superadmin')

            <table class="table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>Data de expiração</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                    <tr>
                        <td>{{ $item['nome'] }}</td>
                        <td>{{ $item['cnpj'] }}</td>
                        <td>{{ $item['fone'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['data'])->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <caption>Total de resultados: <strong>{{ sizeof($data) }}</strong></caption>
            </table>
            
            @endcan
        </div>

    </div>
    <div class="row no-print">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
        </div>
    </div>



</section>
<!-- /.content -->

@endsection
