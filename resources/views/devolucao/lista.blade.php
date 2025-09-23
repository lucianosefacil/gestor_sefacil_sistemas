@extends('layouts.app')
@section('title', 'Lista de Devoluções')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Devoluções
        <small>Lista</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">


    @component('components.widget', ['class' => 'box-primary', 'title' => 'NFe Lista Devolução'])

    <div class="box-header">


        <div class="box-tools">
            <a class="btn btn-block btn-primary" href="{{ route('devolucao.index') }}">
                <i class="fa fa-plus"></i> Nova Devolução</a>
            </div>
        </div>


        <form action="{{ route('devolucao.filtro') }}" method="get">
            <div class="row">
                <div class="col-sm-2 col-lg-3">
                    <div class="form-group">
                        <label for="product_custom_field2">Data inicial:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input class="form-control start-date-picker" required placeholder="Data inicial" value="{{{ isset($data_inicio) ? $data_inicio : ''}}}" data-mask="00/00/0000" name="data_inicio" type="text" id="">
                        </div>

                    </div>
                </div>
                <div class="col-sm-2 col-lg-3">
                    <div class="form-group">
                        <label for="product_custom_field2">Data final:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input class="form-control start-date-picker" required placeholder="Data final" data-mask="00/00/0000" name="data_final" type="text" value="{{{ isset($data_final) ? $data_final : ''}}}">
                        </div>

                    </div>
                </div>

                @if(is_null($default_location))

                <div class="col-sm-2 col-lg-3">
                    <br>
                    <div class="form-group" style="margin-top: 8px;">

                        {!! Form::select('select_location_id', $business_locations, $select_location_id, ['class' => 'form-control input-sm', 'placeholder' => 'Todas','id' => 'select_location_id', '', 'autofocus'], $bl_attributes); !!}

                    </div>

                </div>
                @endif

                <div class="col-sm-2 col-lg-3">
                    <div class="form-group"><br>
                        <button style="margin-top: 5px;" class="btn btn-block btn-primary">Filtrar</button>
                    </div>
                </div>

            </div>
        </form>
        @can('user.view')


        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="users_table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Fornecedor</th>
                        <th>Valor Integral</th>
                        <th>Valor Devolvido</th>
                        <th>Estado</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devolucoes as $d)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i:s')}}</td>

                        <td>{{ $d->contact ? $d->contact->name : '--'}}</td>
                        <td>{{number_format($d->valor_integral, 2, ',', '.')}}</td>
                        <td>{{number_format($d->valor_devolvido, 2, ',', '.')}}</td>
                        <td>{{$d->estado()}}</td>
                        <td>
                            @if($d->estado == 3)
                            <a class="btn" title="Ver" href="{{ route('devolucao.ver', [$d->id]) }}">
                                <i class="fas fa-arrow-right text-success"></i>
                            </a>

                            <a class="btn" title="Imprimir" target="_blank" href="{{ route('devolucao.imprimir-cancelamento', [$d->id]) }}">
                                <i class="fa fa-print text-danger" aria-hidden="true"></i>
                            </a>
                            @endif


                            @if($d->estado == 0 || $d->estado == 2)
                            <form id="devolucao{{$d->id}}" method="POST" action="{{route('devolucao.destroy', $d->id)}}">
                                @method('delete')
                                @csrf
                                <button class="btn btn-link btn-delete" type="button" title="Remover" >
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </button>

                                <a class="btn btn-clear" title="Ver" href="{{ route('devolucao.edit', [$d->id]) }}">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>

                                <a class="btn btn-clear" title="Ver" href="{{ route('devolucao.ver', [$d->id]) }}">
                                    <i class="fas fa-arrow-right text-success"></i>
                                </a>
                            </form>
                            @endif

                            @if($d->estado == 1)

                            <a class="btn" title="Ver" target="_blank" href="{{ route('devolucao.ver', [$d->id]) }}">
                                <i class="fas fa-arrow-right text-success"></i>
                            </a>

                            <a class="btn" title="Baixar XML Aprovado" target="_blank" href="{{ route('devolucao.baixar-xml', [$d->id]) }}">
                                <i class="fa fas fa-arrow-circle-down text-success"></i>
                            </a>

                            <a class="btn" title="Imprimir" target="_blank" href="{{ route('devolucao.imprimir', [$d->id]) }}">
                                <i class="fa fa-print" aria-hidden="true"></i>
                            </a>
                            @endif

                            <a class="btn" title="Alterar estado fiscal" href="{{ route('devolucao.edit-fiscal', [$d->id]) }}">
                                <i class="fas fa-file text-info"></i>
                            </a>


                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>


        </div>


        @endcan
        @endcomponent

        <div class="modal fade user_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">


</script>
@endsection
