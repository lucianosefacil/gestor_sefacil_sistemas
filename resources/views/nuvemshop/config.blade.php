@extends('layouts.app')
@section('title', 'Config Nuvem Shop')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Configuração Nuvem Shop</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Configuração Nuvem Shop'])
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
    <div class="row">
        @if($config != null)
        {!! Form::open(['url' => action('NuvemShopController@save', [$config->id]), 'method' => 'post' ]) !!}
        @else
        {!! Form::open(['url' => action('NuvemShopController@save'), 'method' => 'post', 'id' => 'mdfe_add_form' ]) !!}
        @endif
        <input type="hidden" name="id" value="{{{ isset($config->id) ? $config->id : 0 }}}">
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::label('client_id', 'Cliente ID' . ':*') !!}
                {!! Form::text('client_id', $config != null ? $config->client_id : '', ['class' => 'form-control', 'id' => '', 'required', 'placeholder' => 'Exemplo: 5001']); !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('client_secret', 'Client Secret' . ':*') !!}
                {!! Form::text('client_secret', $config !=null ? $config->client_secret : '', ['class' => 'form-control', 'id' => '', 'required', 'placeholder' => 'Exemplo: iL8mfw69yXb02s6WP8iyX5VLTzO3Pvqt1s26KNwrDoDQtPyF']); !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('email', 'Email' . ':*') !!}
                {!! Form::text('email', $config !=null ? $config->email : '', ['class' => 'form-control', 'id' => '', 'required', 'placeholder' => '']); !!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button id="finalizar" type="submit" class="btn btn-primary pull-right" id="submit_user_button">@if($config != null) Atualizar @else Salvar @endif Config</button>
            </div>
        </div>
        <br><br>
        {!! Form::close() !!}
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
