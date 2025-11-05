@extends('layouts.app')
@section('title', __('purchase.add_purchase'))


@section('css')
    <style>
        .ui-autocomplete {
            z-index: 1055;
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            max-height: 200px;
            /* Limita a altura do dropdown */
            overflow-y: auto;
            /* Adiciona rolagem se necessário */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .ui-menu-item {
            padding: 5px 10px;
            cursor: pointer;
        }

        .ui-menu-item:hover {
            background-color: #f5f5f5;
        }
    </style>
@endsection


@section('content')
    <!-- Content Header (Page header) -->


    <!-- Main content -->
    <section class="content">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        {!! Form::open([
            'url' => '/purchase-xml/save',
            'method' => 'post',
            'id' => 'add_purchase_form',
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])

            @if (count($business_locations) == 1)
                @php
                    $default_location = current(array_keys($business_locations->toArray()));
                    $search_disable = false;
                @endphp
            @else
                @php
                    $default_location = null;
                    $search_disable = true;
                @endphp
            @endif
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                    @show_tooltip(__('tooltip.purchase_location'))
                    {!! Form::select('location_id', $business_locations, $default_location, [
                        'class' => 'form-control select2',
                        'placeholder' => __('messages.please_select'),
                        'required',
                    ]) !!}
                </div>
            </div>

            <input type="hidden" value="{{ json_encode($contact) }}" name="contact">
            {{-- <input type="hidden" value="{{ json_encode($itens) }}" name="itens"> --}}
            {{-- <input type="hidden" value="{{ json_encode($fatura) }}" name="fatura"> --}}
            <input type="hidden" value="{{ json_encode($dadosNf) }}" name="dadosNf">

            <input type="hidden" name="itens_json" id="itens_json">
            <input type="hidden" name="fatura_json" id="fatura_json">

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Fornecedor</h3>
                        @if ($dadosNf['novoFornecedor'])
                            <p class="text-danger">*Este é um novo fornecedor, será cadastrado se finalizar a compra!</p>
                        @endif
                        <div class="row">
                            <div class="col-sm-6">

                                <span>Nome: <strong>{{ $contact['name'] }}</strong></span><br>
                                <span>CNPJ/CPF: <strong>{{ $contact['cpf_cnpj'] }}</strong></span><br>
                                <span>IE/RG: <strong>{{ $contact['ie_rg'] }}</strong></span>
                            </div>

                            <div class="col-sm-6">

                                <span>Rua: <strong>{{ $contact['rua'] }}, {{ $contact['numero'] }}</strong></span><br>
                                <span>Bairro: <strong>{{ $contact['bairro'] }}</strong></span><br>
                                <span>Cidade: <strong>{{ $cidade->nome }} ({{ $cidade->uf }})</strong></span>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Dados do Documento</h3>

                        <div class="row">
                            <div class="col-sm-12">

                                <span>Chave: <strong>{{ $dadosNf['chave'] }}</strong></span><br>
                                <span>Valor Integral: <strong>{{ $dadosNf['vProd'] }}</strong></span><br>
                                <span>Número: <strong>{{ $dadosNf['nNf'] }}</strong></span><br>
                                <span>Valor do frete: <strong>{{ $dadosNf['vFrete'] }}</strong></span><br>
                                <span>Valor de desconto: <strong>{{ $dadosNf['vDesc'] }}</strong></span><br>
                                <span>Valor Final: <strong>{{ $dadosNf['vFinal'] }}</strong></span><br><br>

                                <h5 style="color: red">* Produtos em vermelho não estão cadastrados no sistema!</h5>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Produtos</h3>

                        <div class="">

                            <!-- Inicio tabela -->
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="product_list_tab">
                                        <br><br>
                                        <div class="table-responsive">
                                            <div id="product_table_wrapper"
                                                class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                                                <div class="row margin-bottom-20 text-center">
                                                    <table 
                                                    class="table table-bordered table-striped ajax_view hide-footer dataTable no-footer tabela_produto"
                                                        id="product_table_p" role="grid" 
                                                        aria-describedby="product_table_info"
                                                        style="width: 1300px;">
                                                        <thead>
                                                            <tr role="row">
                                                                {{-- <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 80px;" aria-label="Produto">#</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" style="width: 80px;" aria-label="Produto">ProdutoId</th> --}}

                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    aria-label="Produto">Produto</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Código</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">NCM</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">CFOP</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">CFOP Interestadual
                                                                </th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">CST/CSOSN</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Quantidade</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Valor Unit.</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 100px;" aria-label="Produto">Cod. Barras</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Unidade Compra</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Unidade Venda</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 80px;" aria-label="Produto">Conversão
                                                                    Unitária</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 90px;" aria-label="Produto">Valor Custo</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 60px;" aria-label="Produto">% Lucro</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 90px;" aria-label="Produto">Valor Venda</th>

                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 90px;" aria-label="Produto">Atribuir Produto
                                                                </th>

                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 400px;" aria-label="Produto">Produto
                                                                    Atribuido</th>

                                                            </tr>
                                                        </thead>

                                                        <tbody id="itens">
                                                            @foreach ($itens as $key => $i)
                                                                <tr id="tr_codigo_{{ $i['codigo'] }}">
                                                                    <td style="width: 450px;">
                                                                        <input style="width: 450px"
                                                                            class="{{ $i['produtoNovo'] ? 'text-danger' : '' }} form-control"
                                                                            value="{{ $i['xProd'] }}" type=""
                                                                            name="produto[]">
                                                                    </td>
                                                                    <td style="width: 80px;">
                                                                        <input style="width: 130px;" class="form-control"
                                                                            id="" value="{{ $i['codigo'] }}"
                                                                            type="" name="codigo[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 100px;" class="form-control"
                                                                            id="" value="{{ $i['NCM'] }}"
                                                                            type="" name="ncm[]">
                                                                    </td>
                                                                    <td style="width: 40px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="{{ $i['cfop_interno'] }}"
                                                                            type="" name="cfop_interno[]"
                                                                            data-mask="AAAA">
                                                                    </td>
                                                                    <td style="width: 40px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="{{ $i['cfop_externo'] }}"
                                                                            type="" name="cfop_externo[]"
                                                                            data-mask="AAAA">
                                                                    </td>
                                                                    <td>
                                                                        <select style="width: 550px" name="cst_csosn[]"
                                                                            class="form-control">
                                                                            @foreach (App\Models\Product::listaCSTCSOSN() as $key => $item)
                                                                                <option
                                                                                    @if ($i['cst_csosn'] == $key) selected @endif
                                                                                    value="{{ $key }}">
                                                                                    {{ $item }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    {{-- <input style="width: 80px;" class="form-control" id="" value="{{$i['cst']}}" type="" name="cst[]"> --}}

                                                                    <td style="width: 90px;">
                                                                        <input style="width: 90px;" class="form-control"
                                                                            id="" value="{{ $i['qCom'] }}"
                                                                            type="" name="qCom[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 100px;"
                                                                            class="form-control valor_unitario" id=""
                                                                            value="{{ $i['vUnCom'] }}" type=""
                                                                            name="vUnCom[]">
                                                                    </td>
                                                                    <td style="width: 150px;">
                                                                        <input style="width: 150px;"
                                                                            class="form-control codBarras" id="codBarras" value="{{ $i['codBarras'] }}" type="" name="codBarras[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="{{ $i['uCom'] }}"
                                                                            type="" name="uCom[]">
                                                                    </td>

                                                                    <td style="width: 100px;">
                                                                        <select style="width: 120px" name="unid_venda[]" class="form-control">
                                                                        @foreach ($tipo_unidades as $item)
                                                                                <option @if ($i['uCom'] == $key) selected @endif
                                                                                    value="{{ $item->id }}"> 
                                                                                    {{ $item->actual_name }}
                                                                                </option>
                                                                            @endforeach
                                                                    </select>
                                                                    </td>

                                                                    <td style="width: 80px;">
                                                                        <input class="form-control cn"
                                                                            id="cn_{{ $key }}" value="1"
                                                                            type="" name="">
                                                                        <label>Somente números</label>
                                                                    </td>


                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control valor_custo" id="valor_custo"
                                                                            value="" name="valor_custo[]">
                                                                    </td>

                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control margem_lucro" id=""
                                                                            value="{{ $lucro }}"
                                                                            name="margem_lucro[]">
                                                                    </td>

                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control valor_venda moeda"
                                                                            id="" value=""
                                                                            name="valor_venda[]">
                                                                    </td>

                                                                    <td>
                                                                        <a class="btn-modal" data-bs-toggle="modal"
                                                                            title="Associar Produto"
                                                                            data-bs-target="#atribuirProduto"
                                                                            href="javascript:;"
                                                                            onclick="atribuirProd('{{ $i['codigo'] }}')"><i
                                                                                class="fa fa-plus"></i></a>
                                                                    </td>



                                                                    {{-- teste de atribuicao --}}
                                                                    <td style="width: 100px">
                                                                        <input style="width: 180px" type="text"
                                                                            class="form-control" id=""
                                                                            value="{{ $i['produtoReferenciado'] }}"
                                                                            name="product_atribuido[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control" id=""
                                                                            value="{{ $i['productId'] }}"
                                                                            name="product_id[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control" id=""
                                                                            value="{{ $i['produtoVariationAtribuido'] }}"
                                                                            name="variation_id[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="text"
                                                                            class="form-control" id=""
                                                                            value="{{ $i['idProdutoSku'] }}"
                                                                            name="id_produto_sku[]">
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn-modal btn-warning"
                                                                            data-bs-toggle="modal" title="Desassociar Produto"
                                                                            data-bs-target="#atribuirProduto"
                                                                            href="javascript:;"
                                                                            onclick="desassociarProd('{{ $i['idProdutoSku'] }}')"><i
                                                                                class="fa fa-minus"></i></a>
                                                                    </td>
                                                                    {{-- fim --}}




                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    <?php
                                                    $conversao = '';
                                                    ?>
                                                    @foreach ($itens as $key => $i)
                                                        <?php
                                                        $conversao .= '1';
                                                        ?>
                                                        @if ($key < sizeof($itens) - 1)
                                                            <?php
                                                            $conversao .= ',';
                                                            ?>
                                                        @endif
                                                    @endforeach
                                                    <input type="hidden" name="conversao"
                                                        value="{{ $conversao }}"id="conversao">
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="form-group">
                                                                {!! Form::label('perc_venda', '% de acrescimo para valor de venda, sobre o valor de compra' . ':') !!}
                                                                {!! Form::text('perc_venda', $lucro, ['id' => 'perc_venda', 'class' => 'form-control']) !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- fim tabela -->
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Fatura</h3>
                        <div class="">
                            @if (sizeof($fatura) > 0)
                                <p class="text-danger">Está fatura será incluida em despesas!</p>
                            @endif
                            <div class="nav-tabs-custom">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="product_list_tab">
                                        <br><br>
                                        <div class="table-responsive">
                                            <div id="product_table_wrapper"
                                                class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                                                <div class="row margin-bottom-20 text-center">
                                                    <table
                                                        class="table table-bordered table-striped ajax_view hide-footer dataTable no-footer"
                                                        id="product_table" role="grid"
                                                        aria-describedby="product_table_info" style="width: 700px;">
                                                        <thead>
                                                            <tr role="row">
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 100px;" aria-label="Produto">Número</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 100px;" aria-label="Produto">Vencimento</th>
                                                                <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                    style="width: 100px;" aria-label="Produto">Valor</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>

                                                            @if (sizeof($fatura) > 0)
                                                                @foreach ($fatura as $f)
                                                                    <tr>
                                                                        <td style="width: 200px;">{{ $f['numero'] }}</td>
                                                                        <td style="width: 80px;">{{ $f['vencimento'] }}</td>
                                                                        <td style="width: 80px;">{{ $f['valor_parcela'] }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="3">Nenhuma fatura neste XML</td>
                                                                </tr>
                                                            @endif

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-primary pull-right btn-flat btnSalvarCompra" id="btnSalvarCompra">
                            Salvar Compra
                        </button>
                    </div>
                </div>
            </div>
        @endcomponent
        {!! Form::close() !!}

        @include('purchase.partials.atribuir_produto')


    </section>

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script type="text/javascript">

        window.onload = function() {
            const productTable = document.getElementById('product_table_p');
            productTable.addEventListener('keydown', function(event) {
                const target = event.target.closest('.codBarras');
                if (target && event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        };

        // Bloquear Enter de submeter o formulário inteiro
        $(document).on('keydown', '#add_purchase_form', function (e) {
        // permite Enter em <textarea> ou em campos com a classe .allow-enter (se você quiser liberar em algum campo específico)
        if (e.key === 'Enter' && !$(e.target).is('textarea') && !$(e.target).hasClass('allow-enter')) {
            e.preventDefault();
        }
        });

        // Submeter somente no clique do botão
        $(document).on('click', '.btnSalvarCompra', function (e) {
            e.preventDefault();
            $('#add_purchase_form').trigger('submit'); // dispara seu handler de submit (o que envia em blocos)
        });


        $('#perc_venda').mask('000.00')

        $(document).ready(function() {
            $('#itens tr').each(function() {
                let valor_unit = parseFloat(($(this).find('.valor_unitario').val()).replace(",", "."))
                let margem = ($(this).find('.margem_lucro').val())
                $(this).find('.valor_custo').val(valor_unit);

                let preco_venda = valor_unit + (valor_unit * (margem / 100))

                preco_venda = preco_venda.toFixed(2)

                $(this).find('.valor_venda').val((preco_venda).replace(".", ","))
            });
        })

        $('.cn').keyup(() => {
            percorreTabela()
        })

        function percorreTabela() {
            let valores = '';
            let valido = true;
            $('#itens tr').each(function() {
                if (!$(this).find('.cn').val()) valido = false;
                valores += ($(this).find('.cn').val()) + ',';
            });

            if (valido) {
                valores = valores.substring(0, valores.length - 1);
                $('#conversao').val(valores)
            } else {

            }
        }

        $(document).ready(function() {
            $('#itens').on('keyup', '.cn', function() {
                let row = $(this).closest('tr');
                let valorConversao = parseFloat($(this).val()) || 0;
                let valorUnitario = parseFloat(row.find('.valor_unitario').val().replace(',', '.')) || 0;
                let margem = (row.find('.margem_lucro').val())
                console.log(margem)
                if (valorConversao !== 1) {
                    let resultado = valorUnitario / valorConversao;
                    row.find('.valor_custo').val(resultado.toFixed(2).replace('.', ','));
                    let valor_venda = resultado + (resultado * (margem / 100))
                    row.find('.valor_venda').val((valor_venda).toFixed(2).replace('.', ','))
                } else {
                    row.find('.valor_custo').val(valorUnitario.toFixed(2).replace('.', ','));
                }
            });
        });


        $(document).ready(function() {
            $('#itens').on('keyup', '.margem_lucro', function() {
                let row = $(this).closest('tr');
                let margem = parseFloat(row.find('.margem_lucro').val().replace(',', '.'))
                let custo = parseFloat(row.find('.valor_custo').val().replace(',', '.'));
                let valor_venda = custo + (custo * (margem / 100))
                row.find('.valor_venda').val((valor_venda).toFixed(2).replace(".", ","))
            });
        });

        $(document).ready(function() {
            $('#itens').on('keyup', '.valor_venda', function() {
                let row = $(this).closest('tr');
                let valor_venda = parseFloat(row.find('.valor_venda').val().replace(',', '.'));
                let custo = parseFloat(row.find('.valor_custo').val().replace(',', '.'));

                if (custo > 0 && valor_venda > 0) {
                    let dif = ((valor_venda - custo) / custo) * 100
                    row.find('.margem_lucro').val((dif).toFixed(4))
                }

            });
        });


        var casas_decimais = typeof casas_decimais !== "undefined" ? casas_decimais : 2;
        var mask = "00";
        $(function() {
            if (casas_decimais >= 2 && casas_decimais <= 7) {
                mask = "0".repeat(casas_decimais);
            } else {
                mask = "00";
            }
            $(document).on("focus", ".moeda", function() {
                $(this).mask("00000000," + mask, {
                    reverse: true
                });
            });
        });





        function atribuirProd(codigo) {
            $('#search_product').data('codigo-produto', codigo);
            $('#atribuir_produto').modal('show');
        }


        // $(document).ready(function() {
        //     // Verificar todos os inputs inicialmente
        //     $('input[name="product_atribuido[]"]').each(function() {
        //         if ($(this).val() === '') {
        //             $(this).prop('readonly', true); // Torna o input somente leitura se estiver vazio
        //         }
        //     });

        //     // Monitorar mudanças nos inputs
        //     $('input[name="product_atribuido[]"]').on('input', function() {
        //         if ($(this).val() === '') {
        //             $(this).prop('readonly', true); // Torna o input somente leitura se estiver vazio
        //         } else {
        //             $(this).prop('readonly', false); // Caso tenha conteúdo, o input será editável
        //         }
        //     });
        // });


        if ($('#search_product').length > 0) {
            $('#search_product')
                .autocomplete({
                    source: function(request, response) {
                        $.getJSON(
                            '/purchases/get_products', {
                                location_id: $('#location_id').val(),
                                term: request.term
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        } else if (ui.content.length == 0) {
                            var term = $(this).data('ui-autocomplete').term;
                            swal({
                                title: LANG.no_products_found,
                                text: __translate('add_name_as_new_product', {
                                    term: term
                                }),
                                buttons: [LANG.cancel, LANG.ok],
                            }).then(value => {
                                if (value) {
                                    var container = $('.quick_add_product_modal');
                                    $.ajax({
                                        url: '/products/quick_add?product_name=' + term,
                                        dataType: 'html',
                                        success: function(result) {
                                            $(container)
                                                .html(result)
                                                .modal('show');
                                        },
                                    });
                                }
                            });
                        }
                    },
                    select: function(event, ui) {
                        $(this).val(ui.item.text);
                        $(this).data('selectedProduct', ui.item);


                        $('#default_purchase_price').val(formatCurrency(ui.item.default_purchase_price));
                        $('#profit_percent').val(ui.item.profit_percent);
                        $('#default_price').val(formatCurrency(ui.item.default_price));

                        var codigoProduto = $('#search_product').data('codigo-produto');
                        var linhaProduto = $('#tr_codigo_' + codigoProduto);

                        if (linhaProduto.length > 0) {
                            // Atualiza os campos existentes na linha
                            linhaProduto.find('input[name="product_atribuido[]"]').val(ui.item.text);
                            linhaProduto.find('input[name="product_id[]"]').val(ui.item.product_id);
                            linhaProduto.find('input[name="variation_id[]"]').val(ui.item.variation_id);

                            // $('#atribuir_produto').modal('hide');

                        } else {
                            alert('Linha correspondente não encontrada.');
                        }

                        event.preventDefault();
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                    return $('<li>')
                        .append('<div>' + item.text + '</div>')
                        .appendTo(ul);
                };
        }

        $('#btn-ok').on('click', function() {
            $('#atribuir_produto').modal('hide');

            let valor_custo = $('#default_purchase_price').val()
            let margem_lucro = $('#profit_percent').val()
            let valor_venda = $('#default_price').val()

            var codigoProduto = $('#search_product').data('codigo-produto');
            var linhaProduto = $('#tr_codigo_' + codigoProduto);

            linhaProduto.find('.valor_custo').val(valor_custo)
            linhaProduto.find('.margem_lucro').val(margem_lucro)
            linhaProduto.find('.valor_venda').val(valor_venda)
        });

        function formatCurrency(value) {
            value = parseFloat(value);
            if (isNaN(value)) {
                return '0,00'; // Retorna '0,00' se o valor não for um número válido
            }
            return value.toFixed(2).replace('.', ',');
        }

        function desassociarProd(id) {
            swal({
                title: 'Deseja Desassociar Produto?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(sim => {
                if (sim) {
                    $.ajax({
                        url: '/purchase-xml/desassociarProd', // URL da rota
                        type: 'POST', // Método HTTP
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Token CSRF
                        },
                        data: {
                            id: id // Enviando o ID
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                // console.log('ID enviado:', response.id);
                                location.reload();
                            } else {
                                // alert('Erro: ' + response.message);
                            }
                        },
                        error: function(xhr) {
                            // alert('Erro ao enviar o ID.');
                            // console.log(xhr.responseText);
                        }
                    });
                }
            });
        }

        $('#add_purchase_form').on('submit', function(e) {
        e.preventDefault();

        const btnSalvar = $('button[type="submit"], .btnSalvarCompra');
        btnSalvar.prop('disabled', true);
        btnSalvar.html('<i class="fa fa-spinner fa-spin"></i> Salvando...');

        let itensArray = [];
        $('#itens tr').each(function() {
            let item = {
                produto: $(this).find('input[name="produto[]"]').val(),
                codigo: $(this).find('input[name="codigo[]"]').val(),
                ncm: $(this).find('input[name="ncm[]"]').val(),
                cfop_interno: $(this).find('input[name="cfop_interno[]"]').val(),
                cfop_externo: $(this).find('input[name="cfop_externo[]"]').val(),
                cst_csosn: $(this).find('select[name="cst_csosn[]"]').val(),
                qCom: $(this).find('input[name="qCom[]"]').val(),
                vUnCom: $(this).find('input[name="vUnCom[]"]').val(),
                codBarras: $(this).find('input[name="codBarras[]"]').val(),
                uCom: $(this).find('input[name="uCom[]"]').val(),
                unid_venda: $(this).find('select[name="unid_venda[]"]').val(),
                valor_custo: $(this).find('input[name="valor_custo[]"]').val(),
                margem_lucro: $(this).find('input[name="margem_lucro[]"]').val(),
                valor_venda: $(this).find('input[name="valor_venda[]"]').val(),
                product_atribuido: $(this).find('input[name="product_atribuido[]"]').val(),
                product_id: $(this).find('input[name="product_id[]"]').val(),
                variation_id: $(this).find('input[name="variation_id[]"]').val(),
                id_produto_sku: $(this).find('input[name="id_produto_sku[]"]').val(),
            };
            itensArray.push(item);
        });

    let faturaArray = [];
    $('#product_table tbody tr').each(function() {
        let numero = $(this).find('td').eq(0).text();
        let vencimento = $(this).find('td').eq(1).text();
        let valor = $(this).find('td').eq(2).text();
        if (numero != 'Nenhuma fatura neste XML') {
            faturaArray.push({
                numero: numero,
                vencimento: vencimento,
                valor_parcela: valor
            });
        }
    });

    let contact = $('input[name="contact"]').val();
    let dadosNf = $('input[name="dadosNf"]').val();
    let perc_venda = $('#perc_venda').val();
    let conversao = $('#conversao').val();
    let location_id = $('#location_id').val();

    const chunkSize = 50;
    let totalChunks = Math.ceil(itensArray.length / chunkSize);

    function enviarChunk(index) {
        if (index >= totalChunks) {
            swal('Sucesso', 'Todos os produtos foram salvos!', 'success').then(() => {
                window.location.href = "/purchases";
            });
            return;
        }

        let itens_chunk = itensArray.slice(index * chunkSize, (index + 1) * chunkSize);

        $.ajax({
            url: '/purchase-xml/save',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                contact: contact,
                itens_json: JSON.stringify(itens_chunk),
                fatura_json: JSON.stringify(faturaArray),
                dadosNf: dadosNf,
                perc_venda: perc_venda,
                conversao: conversao,
                location_id: location_id,
                append: index > 0, // << ADICIONAR
                finalize: index == totalChunks - 1 // << ADICIONAR
            },
            success: function(response) {
                console.log('Bloco ' + (index + 1) + ' enviado com sucesso!');
                enviarChunk(index + 1);
            },
            error: function(xhr) {
                console.error('Erro ao enviar bloco ' + (index + 1), xhr.responseText);
                swal('Erro', 'Erro ao enviar bloco ' + (index + 1), 'error');

                btnSalvar.prop('disabled', false);
                btnSalvar.html('Salvar Compra');
            }
        });
    }
    enviarChunk(0);
});
    </script>
@endsection


<!-- /.content -->

@endsection
