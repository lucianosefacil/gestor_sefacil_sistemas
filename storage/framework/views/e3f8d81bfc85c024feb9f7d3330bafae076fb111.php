<?php $__env->startSection('title', __('purchase.add_purchase')); ?>


<?php $__env->startSection('css'); ?>
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
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <!-- Content Header (Page header) -->


    <!-- Main content -->
    <section class="content">

        <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">

        <?php echo Form::open([
            'url' => '/purchase-xml/save',
            'method' => 'post',
            'id' => 'add_purchase_form',
            'files' => true,
        ]); ?>

        <?php $__env->startComponent('components.widget', ['class' => 'box-primary']); ?>

            <?php if(count($business_locations) == 1): ?>
                <?php
                    $default_location = current(array_keys($business_locations->toArray()));
                    $search_disable = false;
                ?>
            <?php else: ?>
                <?php
                    $default_location = null;
                    $search_disable = true;
                ?>
            <?php endif; ?>
            <div class="col-sm-3">
                <div class="form-group">
                    <?php echo Form::label('location_id', __('purchase.business_location') . ':*'); ?>

                    <?php
            if(session('business.enable_tooltip')){
                echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                data-container="body" data-toggle="popover" data-placement="auto bottom" 
                data-content="' . __('tooltip.purchase_location') . '" data-html="true" data-trigger="hover"></i>';
            }
            ?>
                    <?php echo Form::select('location_id', $business_locations, $default_location, [
                        'class' => 'form-control select2',
                        'placeholder' => __('messages.please_select'),
                        'required',
                    ]); ?>

                </div>
            </div>

            <input type="hidden" value="<?php echo e(json_encode($contact), false); ?>" name="contact">
            <input type="hidden" value="<?php echo e(json_encode($itens), false); ?>" name="itens">
            <input type="hidden" value="<?php echo e(json_encode($fatura), false); ?>" name="fatura">
            <input type="hidden" value="<?php echo e(json_encode($dadosNf), false); ?>" name="dadosNf">

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Fornecedor</h3>
                        <?php if($dadosNf['novoFornecedor']): ?>
                            <p class="text-danger">*Este é um novo fornecedor, será cadastrado se finalizar a compra!</p>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-sm-6">

                                <span>Nome: <strong><?php echo e($contact['name'], false); ?></strong></span><br>
                                <span>CNPJ/CPF: <strong><?php echo e($contact['cpf_cnpj'], false); ?></strong></span><br>
                                <span>IE/RG: <strong><?php echo e($contact['ie_rg'], false); ?></strong></span>
                            </div>

                            <div class="col-sm-6">

                                <span>Rua: <strong><?php echo e($contact['rua'], false); ?>, <?php echo e($contact['numero'], false); ?></strong></span><br>
                                <span>Bairro: <strong><?php echo e($contact['bairro'], false); ?></strong></span><br>
                                <span>Cidade: <strong><?php echo e($cidade->nome, false); ?> (<?php echo e($cidade->uf, false); ?>)</strong></span>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <h3 class="box-title">Dados do Documento</h3>

                        <div class="row">
                            <div class="col-sm-12">

                                <span>Chave: <strong><?php echo e($dadosNf['chave'], false); ?></strong></span><br>
                                <span>Valor Integral: <strong><?php echo e($dadosNf['vProd'], false); ?></strong></span><br>
                                <span>Número: <strong><?php echo e($dadosNf['nNf'], false); ?></strong></span><br>
                                <span>Valor do frete: <strong><?php echo e($dadosNf['vFrete'], false); ?></strong></span><br>
                                <span>Valor de desconto: <strong><?php echo e($dadosNf['vDesc'], false); ?></strong></span><br>
                                <span>Valor Final: <strong><?php echo e($dadosNf['vFinal'], false); ?></strong></span><br><br>

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
                                                    <table class="table table-bordered table-striped ajax_view hide-footer dataTable no-footer tabela_produto"
                                                        id="product_table_p" role="grid" aria-describedby="product_table_info"
                                                        style="width: 1300px;">
                                                        <thead>
                                                            <tr role="row">
                                                                

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
                                                            <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr id="tr_codigo_<?php echo e($i['codigo'], false); ?>">
                                                                    <td style="width: 450px;">
                                                                        <input style="width: 450px"
                                                                            class="<?php echo e($i['produtoNovo'] ? 'text-danger' : '', false); ?> form-control"
                                                                            value="<?php echo e($i['xProd'], false); ?>" type=""
                                                                            name="produto[]">
                                                                    </td>
                                                                    <td style="width: 80px;">
                                                                        <input style="width: 130px;" class="form-control"
                                                                            id="" value="<?php echo e($i['codigo'], false); ?>"
                                                                            type="" name="codigo[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 100px;" class="form-control"
                                                                            id="" value="<?php echo e($i['NCM'], false); ?>"
                                                                            type="" name="ncm[]">
                                                                    </td>
                                                                    <td style="width: 40px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="<?php echo e($i['cfop_interno'], false); ?>"
                                                                            type="" name="cfop_interno[]"
                                                                            data-mask="AAAA">
                                                                    </td>
                                                                    <td style="width: 40px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="<?php echo e($i['cfop_externo'], false); ?>"
                                                                            type="" name="cfop_externo[]"
                                                                            data-mask="AAAA">
                                                                    </td>
                                                                    <td>
                                                                        <select style="width: 550px" name="cst_csosn[]"
                                                                            class="form-control">
                                                                            <?php $__currentLoopData = App\Models\Product::listaCSTCSOSN(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option
                                                                                    <?php if($i['cst_csosn'] == $key): ?> selected <?php endif; ?>
                                                                                    value="<?php echo e($key, false); ?>">
                                                                                    <?php echo e($item, false); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                    </td>
                                                                    

                                                                    <td style="width: 90px;">
                                                                        <input style="width: 90px;" class="form-control"
                                                                            id="" value="<?php echo e($i['qCom'], false); ?>"
                                                                            type="" name="qCom[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 100px;"
                                                                            class="form-control valor_unitario" id=""
                                                                            value="<?php echo e($i['vUnCom'], false); ?>" type=""
                                                                            name="vUnCom[]">
                                                                    </td>
                                                                    <td style="width: 150px;">
                                                                        <input style="width: 150px;"
                                                                            class="form-control codBarras" id="codBarras" value="<?php echo e($i['codBarras'], false); ?>" type=""name="codBarras[]">
                                                                    </td>
                                                                    <td style="width: 100px;">
                                                                        <input style="width: 80px;" class="form-control"
                                                                            id="" value="<?php echo e($i['uCom'], false); ?>"
                                                                            type="" name="uCom[]">
                                                                    </td>

                                                                    <td style="width: 100px;">
                                                                        <select style="width: 120px" name="unid_venda[]" class="form-control">
                                                                        <?php $__currentLoopData = $tipo_unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option <?php if($i['uCom'] == $key): ?> selected <?php endif; ?>
                                                                                    value="<?php echo e($item->id, false); ?>"> 
                                                                                    <?php echo e($item->actual_name, false); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    </select>
                                                                    </td>

                                                                    <td style="width: 80px;">
                                                                        <input class="form-control cn"
                                                                            id="cn_<?php echo e($key, false); ?>" value="1"
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
                                                                            value="<?php echo e($lucro, false); ?>"
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
                                                                            onclick="atribuirProd('<?php echo e($i['codigo'], false); ?>')"><i
                                                                                class="fa fa-plus"></i></a>
                                                                    </td>



                                                                    
                                                                    <td style="width: 100px">
                                                                        <input style="width: 180px" type="text"
                                                                            class="form-control" id=""
                                                                            value="<?php echo e($i['produtoReferenciado'], false); ?>"
                                                                            name="product_atribuido[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="hidden"
                                                                            class="form-control" id=""
                                                                            value="<?php echo e($i['productId'], false); ?>"
                                                                            name="product_id[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="hidden"
                                                                            class="form-control" id=""
                                                                            value="<?php echo e($i['produtoVariationAtribuido'], false); ?>"
                                                                            name="variation_id[]">
                                                                    </td>
                                                                    <td style="width: 100px">
                                                                        <input style="width: 100px" type="hidden"
                                                                            class="form-control" id=""
                                                                            value="<?php echo e($i['idProdutoSku'], false); ?>"
                                                                            name="id_produto_sku[]">
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn-modal btn-warning"
                                                                            data-bs-toggle="modal" title="Desassociar Produto"
                                                                            data-bs-target="#atribuirProduto"
                                                                            href="javascript:;"
                                                                            onclick="desassociarProd('<?php echo e($i['idProdutoSku'], false); ?>')"><i
                                                                                class="fa fa-minus"></i></a>
                                                                    </td>
                                                                    




                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                    <?php
                                                    $conversao = '';
                                                    ?>
                                                    <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                        $conversao .= '1';
                                                        ?>
                                                        <?php if($key < sizeof($itens) - 1): ?>
                                                            <?php
                                                            $conversao .= ',';
                                                            ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <input type="hidden" name="conversao"
                                                        value="<?php echo e($conversao, false); ?>"id="conversao">
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="form-group">
                                                                <?php echo Form::label('perc_venda', '% de acrescimo para valor de venda, sobre o valor de compra' . ':'); ?>

                                                                <?php echo Form::text('perc_venda', $lucro, ['id' => 'perc_venda', 'class' => 'form-control']); ?>

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
                            <?php if(sizeof($fatura) > 0): ?>
                                <p class="text-danger">Está fatura será incluida em despesas!</p>
                            <?php endif; ?>
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

                                                            <?php if(sizeof($fatura) > 0): ?>
                                                                <?php $__currentLoopData = $fatura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <tr>
                                                                        <td style="width: 200px;"><?php echo e($f['numero'], false); ?></td>
                                                                        <td style="width: 80px;"><?php echo e($f['vencimento'], false); ?></td>
                                                                        <td style="width: 80px;"><?php echo e($f['valor_parcela'], false); ?>

                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td colspan="3">Nenhuma fatura neste XML</td>
                                                                </tr>
                                                            <?php endif; ?>

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
        <?php echo $__env->renderComponent(); ?>
        <?php echo Form::close(); ?>


        <?php echo $__env->make('purchase.partials.atribuir_produto', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


    </section>

<?php $__env->startSection('javascript'); ?>
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
    </script>
<?php $__env->stopSection(); ?>


<!-- /.content -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/purchase/view_xml.blade.php ENDPATH**/ ?>