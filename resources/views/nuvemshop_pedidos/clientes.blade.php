@extends('layouts.app')
@section('title', 'Clientes Nuvem Shop')

@section('content')

<section class="content-header">
    <h1>
        <small>Clientes Nuvem Shop</small>
    </h1>

</section>

<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Clientes Nuvem Shop'])
    @can('user.create')
    @slot('tool')


    @endslot
    @endcan
    @can('user.view')
    <div class="row">
        <div class="card card-custom gutter-b">
            <div class="card-body">
                <div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
                    <input type="hidden" id="_token" value="{{ csrf_token() }}">
                    <form class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInLeft" method="get" action="/clienteEcommerce/filtro">
                        <div class="row align-items-center">
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
                            <div class="col-lg-2 col-xl-2">
                                <button style="margin-top: 25px;" type="submit" class="btn btn-light-primary font-weight-bold">Pesquisa</button>
                            </div>
                        </div>
                    </form>
                    <br>
                    <h4 class="text-center">Lista de Clientes de Nuvem Shop</h4>
                    <label class="@if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">Registros: <strong class="text-success">{{sizeof($clientes)}}</strong></label>
                    <div class="row @if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">
                        <div class="form-group col-lg-3 col-md-4 col-sm-6">
                            <button type="button" class="btn btn-primary btn-modal" data-href="{{action('ContactController@create', ['type' => 'customer'])}}" data-container=".contact_modal">
                                <i class="fa fa-plus"></i> Adicionar Cliente</button>
                        </div>
                    </div>
                </div>
                <div class="row @if(getenv('ANIMACAO')) animate__animated @endif animate__backInRight">
                    <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                        <div class="wizard wizard-3" id="kt_wizard_v3" data-wizard-state="between" data-wizard-clickable="true">
                            <!--begin: Wizard Nav-->
                            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                                <!--begin: Wizard Form-->
                                <form class="form fv-plugins-bootstrap fv-plugins-framework" id="kt_form">
                                    <!--begin: Wizard Step 1-->
                                    <div class="pb-5" data-wizard-type="step-content">
                                        <!-- Inicio da tabela -->
                                        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead class="datatable-head">
                                                                <tr class="datatable-row" style="left: 0px;">
                                                                    <th>Nome</th>

                                                                    <th>Documento</th>

                                                                    <th>Email</th>

                                                                    <th>Telefone</th>

                                                                    {{-- <th>Ações</th> --}}
                                                                </tr>
                                                            </thead>

                                                            <tbody id="body" class="datatable-body">
                                                                <?php $total = 0; ?>
                                                                @foreach($clientes as $c)
                                                                <tr class="datatable-row">

                                                                    <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{$c->name}}</span>
                                                                    </td>

                                                                    <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{$c->cpf_cnpj}}</span>
                                                                    </td>

                                                                    <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{$c->email}}</span>
                                                                    </td>

                                                                    <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{$c->mobile}}</span>
                                                                    </td>

                                                                    {{-- <td>
                                                                        <a class="btn btn-warning" onclick='swal("Atenção!", "Deseja editar este registro?", "warning").then((sim) => {if(sim){ location.href="/contacts/edit/{{ $c->id }}" }else{return false} })' href="#!">
                                                                    <i class="glyphicon glyphicon-edit"></i>
                                                                    </a>
                                                                    </td> --}}
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="d-flex flex-wrap py-2 mr-3">
                                            @if(isset($links))
                                            {{$clientes->links()}}
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
    @endcomponent
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).on('shown.bs.modal', '.contact_modal', function(e) {
        initAutocomplete();
    });

</script>
@endsection
