@extends('layouts.app')
@section('title', 'Pedidos Nuvem Shop')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Pedidos Nuvem Shop</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Pedidos Nuvem Shop'])
    @can('user.create')
    @slot('tool')


    @endslot
    @endcan
    @can('user.view')
    <div class="card card-custom gutter-b">
        <div class="card=body">
            <input type="hidden" id="_token" value="{{ csrf_token() }}">
            <form class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInLeft" method="get" action="/nuvemshop/pedidos">
                <div class="row align-items-center">
                    <div>
                        <div class="col-lg-4 col-xl-4">
                            <div class="row align-items-center">
                                <div class="col-md-12 my-2 my-md-0">
                                    <label>Cliente</label>
                                    <div class="input-icon">
                                        <input type="text" name="cliente" class="form-control" value="{{{isset($cliente) ? $cliente : ''}}}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="col-form-label">Data Inicial</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" name="data_inicial" id="start_date" value="{{@format_date('now')}}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <label class="col-form-label">Data Final</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" name="data_final" id="end_date" value="{{@format_date('now')}}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xl-2">
                            <button style="margin-top: 25px;" type="submit" class="btn btn-light-primary font-weight-bold">Pesquisa</button>
                            <a href="/nuvemshop/pedidos" style="margin-top: 25px;" class="btn btn-light-primary">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <br>
        <h4 class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">Lista de Pedidos Nuvem Shop</h4>
        <label class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">Registros: <strong class="text-success">{{sizeof($pedidos)}}</strong></label>
    </div>
    <div class="row @if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">
        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users_table">
                    <thead class="datatable-head">
                        <tr class="datatable-row" style="left: 0px;">

                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>NFe</th>
                            <th>Valor Total</th>
                            <th>Desconto</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="body" class="datatable-body">
                        <?php $total = 0; ?>
                        @foreach($pedidos as $p)
                        <tr class="datatable-row">
                            <td class="datatable-cell"><span class="codigo" style="width: 120px;" id="id">{{$p->id}}</span>
                            </td>
                            <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{ $p->contact_name }}</span>
                            </td>
                            <td class="datatable-cell">
                                <span class="codigo" style="width: 120px;" id="id">
                                    {{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="datatable-cell"><span class="codigo" style="width: 100px;" id="id">{{isset($p->transaction) ? $p->transaction->id : '--'}}</span>
                            </td>
                            <td class="datatable-cell">
                                <span class="codigo" style="width: 100px;" id="id">
                                    {{number_format($p->total, 2, ',', '.')}}
                                </span>
                            </td>
                            <td class="datatable-cell">
                                <span class="codigo" style="width: 100px;" id="id">
                                    {{number_format($p->discount, 2, ',', '.')}}
                                </span>
                            </td>
                            <td>
                                <span>
                                    <a class="btn btn-info" href="/nuvemshop/detalhar/{{ $p->id }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/nuvemshop/removerPedido/{{$p->id}}" }else{return false} })' href="#!" class="navi-link">
                                    <span class="navi-text">
                                        <span class="box-title">Remover</span>
                                    </span>
                                    </a> --}}
                                    {{-- <a class="btn btn-danger" href="/nuvemshop/remover/{{ $p->id }}">
                                    <i class="glyphicon glyphicon-trash"></i>
                                    </a> --}}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(!isset($cliente))
        <div class="row">
            <div class="col-sm-1">
                @if($page > 1)
                <a class="btn btn-light-primary" href="/nuvemshop/pedidos?page={{$page-1}}" class="float-left">
                    <i class="fa fa-angle-left"></i>
                </a>
                @endif
            </div>
            <div class="col-sm-10"></div>
            <div class="col-sm-1">
                <a class="btn btn-light-primary" href="/nuvemshop/pedidos?page={{$page+1}}" class="float-right">
                    <i class="fa fa-angle-right"></i>
                </a>
            </div>
        </div>
        @endif
    </div>

    @endcan
    @endcomponent

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        //Date picker
        $('#start_date').datepicker({
            autoclose: true
            , format: datepicker_date_format
        });

        $('#end_date').datepicker({
            autoclose: true
            , format: datepicker_date_format
        });
        update_balance_sheet();

        $('#end_date').change(function() {
            update_balance_sheet();
            $('#hidden_date').text($(this).val());
        });
    });

</script>
@endsection
