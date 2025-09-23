@extends('layouts.app')
@section('title', 'Lista de Ordem de Servico')

@section('content')
@include('ordem_servico.nav.index')

<!-- Content Header (Page header) -->
<section class="content-header">

</section>

<!-- Main content -->
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <h5>Ordem de Serviço: {{$ordem_servico->id}}</h5>
                    <h5>Cliente: {{$ordem_servico->cliente->name}}</h5>
                    <h5>Previsão de Entrega: {{$ordem_servico->data_entrega}}</h5>
                </div>
            </div>

        </div>
    </div>
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                {!! Form::open(['action' => 'OrdemServicoController@storeServico', 'id' => 'job_sheet_form', 'method' => 'post', 'files' => true]) !!}
                <input type="hidden" value="{{$ordem_servico->id}}" name="ordem_servico_id">
                <div class="col-md-7">
                    <div class="form-group">
                        {!! Form::label('servico', __('Serviço') . ':') !!}
                        <select class="form-control" name="servico_id" id="servico_id">
                            <option value="">Selecione um serviço</option>
                            @foreach ($servicos as $s)
                            <option value="{{$s->id}}">{{$s->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    {!! Form::label('quantidade', __('Quantidade') . ':') !!}
                    {!! Form::tel('quantidade', '', ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-2">
                    <br>
                    @if(!isset($not_submit))
                    <button type="submit" class="btn btn-info btn-add-servico"><i class=" ri-add-line"></i>Adicionar</button>
                    @endif
                </div>
                <div class="col-md-1">
                    {!! Form::hidden('nome', 'Nome', ['class' => 'nome']) !!}
                </div>
                <div class="col-md-1">
                    {!! Form::hidden('valor', 'valor', ['class' => 'valor']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <p class="">Registro(s): {{ sizeof($ordem_servico->servicos) }}</p>
                    <table class="table mb-0 table-striped table-servico">
                        <thead class="table-dark">
                            <tr>
                                <th>Serviço</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>SubTotal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($ordem_servico)
                            @foreach ($ordem_servico->servicos as $item)
                            <tr>
                                <td>
                                    <input readonly type="text" name="servico[]" class="form-control" value="{{ $item->servico->nome }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="servico_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="valor[]" class="form-control qtd-item" value="{{ number_format($item->servico->valor, 2, ',', '') }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="subtotal[]" class="form-control qtd-item sub_total_servico" value="{{ number_format($item->sub_total, 2, ",", " ") }}">
                                </td>
                                <td>
                                    <a href="{{ route('ordemServico.deletarServico', $item->id) }}" style="width: 100%" class="btn btn-sm btn-danger"><i class="la la-close"></i>Excluir</a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="clearfix"></div>
            <hr>
            <div class="clearfix"></div>

            {{-- Produtos --}}
            <div class="row">
                {!! Form::open(['action' => 'OrdemServicoController@storeProduto', 'id' => 'job_sheet_form', 'method' => 'post', 'files' => true]) !!}
                <input type="hidden" value="{{$ordem_servico->id}}" name="ordem_servico_id">

                @if($check_qty == true)
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('produto', __('Produto') . ':') !!}
                        <select class="form-control select2 produto_id" name="produto_id" id="produto_id">
                            <option value="">Selecione um produto</option>
                            @foreach ($produtos as $p)
                            <option value="{{$p->id}}">{{$p->name}} - {{ $p->default_sell_price }} - Estoque: {{ $p->qty_available }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('produto', __('Produto') . ':') !!}
                        <select class="form-control select2 produto_id" name="produto_id" id="produto_id">
                            <option value="">Selecione um produto</option>
                            @foreach ($produtos as $p)
                            <option value="{{$p->id}}">{{$p->name}} - {{ $p->variations->last()->default_sell_price }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-md-2">
                    {!! Form::label('quantidade_produto', __('Quantidade') . ':') !!}
                    {!! Form::tel('quantidade_produto', '', ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-2">
                    {!! Form::label('valor_produto', __('Valor') . ':') !!}
                    {!! Form::tel('valor_produto', '', ['class' => 'form-control valor_produto']) !!}
                </div>
                <input type="hidden" name="variation_id" class="variation_id" value="">
                <div class="col-md-2">
                    <br>
                    @if(!isset($not_submit))
                    <button type="submit" class="btn btn-info btn-add-produto"><i class="ri-add-line"></i>Adicionar</button>
                    @endif
                </div>
                {!! Form::close() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <p class="">Registro(s): {{ sizeof($ordem_servico->itens) }}</p>
                    <table class="table mb-0 table-striped table-produto">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>SubTotal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($ordem_servico)
                            @foreach ($ordem_servico->itens as $item)
                            <tr>
                                <td>
                                    <input readonly type="text" name="produto[]" class="form-control" value="{{ $item->produto->name }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="produto_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                </td>

                                <td>
                                    <input readonly type="tel" name="total[]" class="form-control qtd-item" value="{{ number_format($item->valor_unitario, 2, ",", " ") }}">
                                </td>
                                <td>
                                    <input readonly type="tel" name="subtotal[]" class="form-control qtd-item sub_total_produto" value="{{ number_format($item->sub_total, 2, ",", " ") }}">
                                </td>
                                <td>
                                    <a href="{{ route('ordemServico.deletarProduto', $item->id) }}" style="width: 100%" class="btn btn-sm btn-danger">Excluir</a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="clearfix"></div>
            <hr>
            <div class="clearfix"></div>

            <div class="col-md-12">
                <div class="form-group">
                    <h3>Total de Serviços: <strong class="total_servico"></strong></h3>
                    <h3>Total de Produtos: <strong class="total_prod"></strong></h3>
                    <h3>Total Geral: <strong class="valor_total"></strong></h3>
                </div>
            </div>

            <div class="clearfix"></div>
            <hr>
            <div class="clearfix"></div>

            {{-- colocado essa parte para testar o js, porem essa parte nao usa na abertura da OS  --}}
            <div class="input-group">
                {{-- <div class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fa fa-barcode"></i></button>
                </div> --}}
                {!! Form::hidden('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
                'disabled' => is_null($default_location)? true : false,
                'autofocus' => is_null($default_location)? false : true,
                ]); !!}
                {{-- <span class="input-group-btn">
                    <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                </span> --}}
            </div>
            {{-- fim --}}


            <div class="col-sm-12 text-right">
                {!! Form::open(['url' => action('OrdemServicoController@updateValorOs', [$ordem_servico->id]), 'method' => 'put', 'id' => '']) !!}
                <input type="hidden" name="total_os" id="" class="total_os">
                <button type="submit" class="btn btn-success submit_button" value="save_and_add_parts" id="save_and_add_parts">
                    @lang('Atualizar Valor da Os')
                </button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!-- /form close -->
</section>
@stop
@section('javascript')
    
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">

    $(document).on('click','.select2', function () {
        $('span').removeClass('select2-search--hide');
    });


    var path = window.location.protocol + '//' + window.location.host
    $(function() {
        setTimeout(() => {
            $("#servico_id").change(() => {
                let servico_id = $("#servico_id").val()
                if (servico_id) {
                    $.get(path + "/servicos/findId/" + servico_id)
                        .done((e) => {
                            $('#quantidade').val('1,00')
                            $('.nome').val(e.nome)
                            $('.valor').val(e.valor)
                            console.log(e)
                        })
                        .fail((e) => {
                            console.log(e)
                        })
                }
            })
        }, 100)
    })

    $('.btn-add-servico').click(() => {
        let qtd = $("#quantidade").val();
        let valor = $(".valor").val();
        let servico_id = $("#servico_id").val()
        let nome = $(".nome").val()
        if (qtd && valor && servico_id && nome) {
            let dataRequest = {
                qtd: qtd
                , valor: valor
                , servico_id: servico_id
                , nome: nome
            }
            $.get(path + "/servicos/linhaServico", dataRequest)
                .done((e) => {
                    $('.table-servico tbody').append(e)
                })
                .fail((e) => {
                    console.log(e)
                })
        } else {
            swal("Atenção", "Informe corretamente os campos para continuar!", "warning")
        }
    })

    $(function() {
        setTimeout(() => {
            $("#produto_id").change(() => {
                let produto_id = $("#produto_id").val()
                if (produto_id) {
                    $.get(path + "/servicos/findProduto/" + produto_id)
                        .done((e) => {
                            console.log(e.variations[0].id)
                            $('#quantidade_produto').val('1,00')
                            $('#nome_produto').val(e.name)
                            $('.variation_id').val(e.variations[0].id)
                            $('#valor_produto').val(convertFloatToMoeda(e.variations[0].default_sell_price))
                        })
                        .fail((e) => {
                            console.log(e)
                        })
                }
            })
        }, 100)
    })

    function convertFloatToMoeda(value) {
        value = parseFloat(value)
        return value.toLocaleString("pt-BR", {
            minimumFractionDigits: 2
            , maximumFractionDigits: 2
        });
    }


    function convertMoedaToFloat(value) {
        if (!value) {
            return 0;
        }

        var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
        return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
    }

    $(function() {
        calcTotalProd()
        calcTotalServico()
        // calTotal()
        $('body').on('blur', '.produto_id', function() {
            calcTotalProd()
        })
    })

    // $(function() {
    //     setTimeout(() => {
    //         calTotal()
    //     })
    // }, 500)

    // CÁLCULO TOTAL DE PRODUTOS
    var total_produto = 0

    function calcTotalProd() {
        var total = 0
        $(".sub_total_produto").each(function() {
            total += convertMoedaToFloat($(this).val())
        })
        setTimeout(() => {
            total_produto = total
            $('.total_prod').html("R$ " + convertFloatToMoeda(total))
            $('.total_prod').val(total)
            // calTotal()
        }, 100)
    }

    var total_servico = 0

    function calcTotalServico() {
        var total = 0
        $(".sub_total_servico").each(function() {
            total += convertMoedaToFloat($(this).val())
        })
        setTimeout(() => {
            total_servico = total
            $('.total_servico').html("R$ " + convertFloatToMoeda(total))
            $('.total_servico').val(total)
            calTotal()
        }, 100)
    }

    var total_geral = 0

    function calTotal() {
        let total_prod = parseFloat($('.total_prod').val())
        let total_serv = parseFloat($('.total_servico').val())
        console.log(total_serv)
        console.log(total_prod)

        setTimeout(() => {
            total_geral = total_prod + total_serv
            $('.valor_total').html("R$ " + convertFloatToMoeda(total_geral))
            $('.total_os').val(convertFloatToMoeda(total_geral))
            console.log(total_geral)
        }, 500)
    }


</script>
@endsection
