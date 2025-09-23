@extends('layouts.app')
@section('title', 'Importação de XML')

@section('content')

<style type="text/css">
    .fa-arrow-down:hover{
        cursor: pointer;
    }
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Importação de XML</h1>
</section>

<!-- Main content -->
<section class="content">
    @if (session('notification') || !empty($notification))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                @if(!empty($notification['msg']))
                {{$notification['msg']}}
                @elseif(session('notification.msg'))
                {{ session('notification.msg') }}
                @endif
            </div>
        </div>  
    </div>     
    @endif
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
            {!! Form::open(['url' => action('ImportXmlController@store'), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
            <div class="row">
                <input type="hidden" value="{{ $type }}" name="type">
                <input type="hidden" value="{{ $location_id }}" name="location_id">
                <input type="hidden" value="{{ json_encode($data) }}" name="data">

                <div class="col-lg-12">
                    @foreach($data as $key => $d)
                    <div class="col-lg-12" style="border-bottom: 1px solid #70717E; margin-top: 10px; padding: 10;">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title collapsed" data-toggle="collapse" data-target="#collapseOne{{$key}}">
                                    <label class="checkbox checkbox-info check-sub">
                                        <input checked type="checkbox" name="chave_{{$d['chave']}}">
                                        <span></span>
                                    </label>
                                    <strong style="margin-left: 5px;" class="text-info">{{$d['chave']}}</strong> 
                                    <strong style="margin-left: 5px;" class="text-danger">{{ \Carbon\Carbon::parse($d['data'])->format('d/m/Y H:i:s')}}</strong>
                                    <i style="margin-left: 5px;" class="fa fa-arrow-down"></i>
                                </div>
                            </div>
                            <div id="collapseOne{{$key}}" class="collapse" data-parent="#accordionExample1">
                                <div class="card-body">
                                    @if($d['cliente'])
                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">
                                            <h3 class="card-title">Cliente</h3>

                                            <h5>Razão social: <strong>{{$d['cliente']['name']}}</strong></h5>
                                            
                                            <h5>CNPJ/CPF: <strong>{{$d['cliente']['cpf_cnpj']}}</strong></h5>
                                            <h5>IE/RG: <strong>{{$d['cliente']['ie_rg']}}</strong></h5>
                                            <h5>Endereço: <strong>{{$d['cliente']['rua']}}, {{$d['cliente']['numero']}} - {{$d['cliente']['bairro']}}</strong></h5>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">
                                            <h3 class="card-title">Produtos</h3>

                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Código</th>
                                                        <th>Nome</th>
                                                        <th>CFOP</th>
                                                        <th>Unidade</th>
                                                        <th>Valor unitário</th>
                                                        <th>Quantidade</th>
                                                        <th>NCM</th>
                                                        <th>Código de barras</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($d['produtos'] as $p)
                                                    <tr>
                                                        <td>{{$p['codigo']}}</td>
                                                        <td>{{$p['xProd']}}</td>
                                                        <td>{{$p['CFOP']}}</td>
                                                        <td>{{$p['uCom']}}</td>
                                                        <td>{{number_format((float)$p['vUnCom'], 2, ',', '.')}}</td>
                                                        <td>{{$p['qCom']}}</td>
                                                        <td>{{$p['NCM']}}</td>
                                                        <td>{{$p['codBarras']}}</td>

                                                    </tr>
                                                    @endforeach
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">
                                            <h3 class="card-title">Fatura</h3>

                                            @foreach($d['fatura'] as $f)
                                            <h5>Vencimento: <strong>{{ \Carbon\Carbon::parse($f['vencimento'])->format('d/m/Y')}}</strong></h5>
                                            <h5>Valor: <strong>{{number_format((float)$f['valor_parcela'], 2, ',', '.')}}</strong></h5>
                                            <hr>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">

                                           
                                            <h5>Natureza de Operação: <strong>{{ $d['natureza'] }}</strong></h5>
                                            <hr>
                                           
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <br>
                    </div>

                    @endforeach

                </div>

                <div class="col-sm-12">
                    <br>
                    <button type="submit" class="btn btn-primary float-right">Salvar</button>
                </div>
            </div>

            {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).on('click', 'a.revert_import', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                window.location = $(this).attr('href');
            } else {
                return false;
            }
        });
    });
</script>
@endsection