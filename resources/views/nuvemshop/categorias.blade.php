@extends('layouts.app')
@section('title', 'Categorias de Produtos')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Categorias de Produtos Nuvem Shop</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Categorias Nuvem Shop'])
    @can('user.create')
    @slot('tool')

    {{-- <div class="box-tools">
        <a style="margin-left: 4px;" class="btn btn-block btn-primary" 
        href="{{ route('mdfe.create')}}" >
    <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
    </div>
    <div class="box-tools">
        <a class="btn btn-block btn-danger" href="{{ route('mdfe.nao-encerrados')}}">
            <i class="fa fa-list"></i> Documentos não encerrados</a>
    </div> --}}

    @endslot
    @endcan
    @can('user.view')

    <div class="card card-custom gutter-b">
        <div class="card-body">
            <div class="col-sm-12 col-lg-12 col-md-6 col-xl-4">
                <a href="/nuvemshop/categoria_new" class="btn btn-lg btn-success">
                    <i class="fa fa-plus"></i> Nova Categoria
                </a>
            </div>
            <br>
            <div class="row">
                <div class="text-center" style="margin-top: 55px;">
                    <h4>Lista de Categorias Nuvem Shop</h4>
                </div>
                @php
                if(sizeof($categorias) > 0)
                $categoria = $categorias[0]->name->pt;
                @endphp

                @foreach($categorias as $c)

                <div class="col-xl-4 col-lg-3 col-md-6 col-sm-6">
                    <div class="box box-primary" style="margin-top: 15px">
                        <div class="navbar-custom-menu" style="margin: 3px">
                            <div class="btn-group pull-right">
                                <a class="btn btn-success dropdown-toggle btn-sm" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <span aria-hidden="true">Ações:</span>
                                    </li>
                                    <li>
                                        <a href="/nuvemshop/categoria_edit/{{$c->id}}" class="navi-link">
                                            <span class="navi-text">
                                                <span class="box-title">Editar</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="navi-item">
                                        <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/nuvemshop/categoria_delete/{{$c->id}}" }else{return false} })' href="#!" class="navi-link">
                                            <span class="navi-text">
                                                <span class="box-title">Remover</span>
                                            </span>
                                        </a>
                                    </li>

                                </ul>
                            </div>
                            <br>
                            <br>
                            <div class="d-flex">
                                <h4 class="text-center">{{$c->name->pt}}</h4>
                            </div>
                        </div>
                        @if($c->parent > 0)
                        <p class="text-muted font-weight-bold">Sub-categoria de :
                            <strong class="text-danger">{{ $categoria }}</strong>
                        </p>
                        @endif
                        @php
                        if(!$c->parent)
                        $categoria = $c->name->pt;
                        @endphp
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

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
