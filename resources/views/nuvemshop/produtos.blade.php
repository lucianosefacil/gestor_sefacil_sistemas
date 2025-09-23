@extends('layouts.app')
@section('title', 'Produtos')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Produtos Nuvem Shop</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Produtos Nuvem Shop'])
    @can('user.create')
    @slot('tool')

    @endslot
    @endcan
    @can('user.view')
    <div class="card card-custom gutter-b">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-lg-4 col-md-6 col-xl-4">
                    <a href="/nuvemshop/produto_new" class="btn btn-lg btn-success">
                        <i class="fa fa-plus"></i> Novo Produto
                    </a>
                </div>
            </div>
            <br>
            <div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
                <div class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
                    <form method="get" action="/nuvemshop/produtos">
                        <div class="row align-items-center">
                            <div class="col-lg-4 col-xl-4">
                                <div class="row align-items-center">
                                    <div class="col-md-12 my-2 my-md-0">
                                        <label>Descrição do produto</label>
                                        <div class="input-icon">
                                            <input type="text" name="search" class="form-control" value="{{{isset($search) ? $search : ''}}}" id="kt_datatable_search_query">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xl-2">
                                <button style="margin-top: 25px;" type="submit" class="btn btn-light-primary font-weight-bold">Pesquisa</button>
                            </div>
                        </div>
                    </form>
                </div>
                <br>
                <div class="text-center" style="margin-top: 35px;">
                    <h4>Lista de Produtos Nuvem Shop</h4>
                </div>
                <label class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">Registros: <strong class="text-success">{{sizeof($produtos)}}</strong></label>
                <div class="row">
                    @foreach($produtos as $p)
                    <!-- inicio grid -->
                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-6">
                        <!--begin::Card-->
                        <div class="box box-primary" style="margin-top: 15px">
                            <!--begin::Body-->
                            <div class="navbar-custom-menu" style="margin: 3px">
                                <!--begin::Toolbar-->
                                <div class="btn-group pull-right">
                                    <a class="btn btn-success dropdown-toggle btn-sm" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <span class="font-size-lg">Ações:</span>
                                        </li>
                                        <li>
                                            <a href="/nuvemshop/produto_edit/{{$p->id}}" class="navi-link">
                                                <span class="navi-text">
                                                    <span class="box-title">Editar</span>
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/nuvemshop/produto_delete/{{$p->id}}" }else{return false} })' href="#!" class="navi-link">
                                                <span class="navi-text">
                                                    <span class="box-title">Remover</span>
                                                </span>
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a href="/nuvemshop/produto_galeria/{{$p->id}}" class="navi-link">
                                        <span class="navi-text">
                                            <span class="box-title">Galeria</span>
                                        </span>
                                        </a>
                                        </li> --}}
                                    </ul>
                                </div>
                                <div class="d-flex align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="symbol symbol-circle symbol-lg-75">
                                                @if(sizeof($p->images) > 0)
                                                <img src="{{$p->images[0]->src}}" class="" alt="image" height="100px">
                                                @else
                                                <img src="/img/default.png" alt="image" class="" height="100px">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted font-weight-bold mt-5">
                                    <a class="text-dark font-weight-bold text-hover-primary">{{$p->name->pt}}</a>
                                </p>
                                <p class="text-muted font-weight-bold">Preço:
                                    <strong class="text-danger">R$ {{ number_format($p->variants[0]->price, 2, ',', '.') }}</strong>
                                </p>
                                <p class="text-muted font-weight-bold">Preço promocional:
                                    <strong class="text-danger">R$ {{ number_format($p->variants[0]->promotional_price, 2, ',', '.') }}</strong>
                                </p>
                                <p class="text-muted font-weight-bold">Estoque:
                                    @if($p->variants[0]->stock == 0)
                                    <strong class="text-info">ilimitado</strong>
                                    @else
                                    <strong class="text-danger"> {{ number_format($p->variants[0]->stock, 2, '.', '') }}</strong>
                                    @endif
                                </p>
                                <p class="text-muted font-weight-bold">Código de barras:
                                    <strong class="text-danger"> {{ $p->variants[0]->barcode }}</strong>
                                </p>
                                <p class="text-muted font-weight-bold">Categoria(s):
                                    <strong class="text-info">
                                        @foreach($p->categories as $key => $c)
                                        {{$c->name->pt}}

                                        @if($key < sizeof($p->categories)-1) | @endif
                                            @endforeach
                                    </strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @if(isset($produtos))
    <div class="row">
        <div class="col-sm-1">
            @if($page > 1)
            <a class="btn btn-light-primary" href="/nuvemshop/produtos?page={{$page-1}}" class="float-left">
                <i class="fa fa-angle-left"></i>
            </a>
            @endif
        </div>
        <div class="col-sm-10"></div>
        <div class="col-sm-1">
            <a class="btn btn-light-primary" href="/nuvemshop/produtos?page={{$page+1}}" class="float-right">
                <i class="fa fa-angle-right"></i>
            </a>
        </div>
    </div>
    @endif
    @endcan
    @endcomponent

    {{-- <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div> --}}

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">



</script>
@endsection
