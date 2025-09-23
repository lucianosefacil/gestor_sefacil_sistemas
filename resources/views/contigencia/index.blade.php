@extends('layouts.app')
@section('title', 'Contigência')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Contigência
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Contigência'])
    @can('user.create')
    @slot('tool')
    
    @endslot
    @endcan
    <div class="row">
        <div class="col-md-12">
            <a class="btn pull-right btn-primary" href="{{action('ContigenciaController@create')}}">
                <i class="fa fa-plus"></i> Ativar
            </a>
        </div>

        <div class="col-md-12">
            <br>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="users_table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Motivo</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->motivo }}</td>
                            <td>{{ $item->tipo }}</td>
                            <td>{{ $item->documento }}</td>
                            <td>
                                @if($item->status)
                                <i class="fa fa-check text-success"></i>
                                @else
                                <i class="fa fa-times text-danger"></i>
                                @endif
                            </td>
                            <td>
                                @if($item->status)
                                <a href="{{ route('contigencia.desactive', [$item->id]) }}" class="btn btn-danger btn-sm">Desativar</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>



</section>

<div class="modal fade" id="modal-msg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Mensagem</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="msg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table

    
</script>
@endsection
