@extends('layouts.app')
@section('title', 'Adicionar devolução')

@section('css')
<style type="text/css">
	.table-responsive{
		height: 400px !important;
	}

	.sticky-col {
		position: -webkit-sticky;
		position: sticky;
	}

	.first-col {
		width: 400px;
		min-width: 400px;
		max-width: 400px;
		left: 0px;
	}

	.second-col {
		width: 150px;
		min-width: 150px;
		max-width: 150px;
		left: 100px;
	}
</style>
@endsection

@section('content')

<section class="content">

	{!! Form::open(['url' => route('devolucao.store'), 'method' => 'post', 'id' => 'add_purchase_form', 'files' => true ]) !!}
	@component('components.widget', ['class' => 'box-primary'])

	<input type="hidden" value="{{json_encode($contact)}}" name="contact">
	<input type="hidden" value="{{json_encode($dadosNf)}}" name="dadosNf">

	<div class="row">
		<div class="col-sm-12">
			<div class="form-group">

				@if(is_null($default_location))
				<div class="row">
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="fa fa-map-marker"></i>
								</span>
								{!! Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm', 
								'placeholder' => __('lang_v1.select_location'),
								'id' => 'select_location_id', 
								'required', 'autofocus'], $bl_attributes); !!}
								<span class="input-group-addon">
									@show_tooltip('Local da devolução')
								</span> 
							</div>
						</div>
					</div>
				</div>
				@endif
				<h3 class="box-title">Fornecedor do XML</h3>
				@if($dadosNf['novoFornecedor'])
				<p class="text-danger">*Este é um novo fornecedor, será cadastrado se finalizar a compra!</p>
				@endif
				<div class="row">
					<div class="col-sm-6">

						<span>Nome: <strong>{{$contact['name']}}</strong></span><br>
						<span>CNPJ/CPF: <strong>{{$contact['cpf_cnpj']}}</strong></span><br>
						<span>IE/RG: <strong>{{$contact['ie_rg']}}</strong></span>
					</div>

					<div class="col-sm-6">

						<span>Rua: <strong>{{$contact['rua']}}, {{$contact['numero']}}</strong></span><br>
						<span>Bairro: <strong>{{$contact['bairro']}}</strong></span><br>
						<span>Cidade: <strong>{{$cidade->nome}} ({{$cidade->uf}})</strong></span>

					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('novo_fornecedor_id', 'Fornecedor:*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
							{!! Form::select('novo_fornecedor_id', 
							$fornecedores->pluck('name', 'id')->all(), $contact['id'], ['class' => 'form-control select2', 'id' => 'customer_id', 'placeholder' => '', 'required']); !!}

						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="form-group">
				<h3 class="box-title">Dados do Documento</h3>

				<div class="row">
					<div class="col-sm-12">

						<span>Chave: <strong>{{$dadosNf['chave']}}</strong></span><br>
						<span>Valor: <strong>{{number_format((double)$dadosNf['vProd'], 2, ',', '.')}}</strong></span><br>
						<span>Número: <strong>{{$dadosNf['nNf']}}</strong></span><br>
						<span>Valor do frete: <strong>{{number_format((double)$dadosNf['vFrete'], 2, ',', '.')}}</strong></span><br>
						<span>Valor de desconto: <strong>{{number_format((double)$dadosNf['vDesc'], 2, ',', '.')}}</strong></span><br>
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
									<div id="product_table_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
										<div class="row margin-bottom-20 text-center">
											<table class="table table-bordered table-striped ajax_view hide-footer dataTable no-footer table-devolucao" id="product_table" role="grid" aria-describedby="product_table_info">
												<thead>
													<tr role="row">
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="">Ação</th>
														<th class="sorting_disabled sticky-col first-col" rowspan="1" colspan="1" aria-label="">Produto</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="">Código</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">NCM</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">CFOP</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Quantidade</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Valor Unit.</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Subtotal</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Cod. Barras</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Unidade</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">CST/CSOSN</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">CST PIS</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">CST COFINS</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">CST IPI</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">%ICMS</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">VBC ICMS</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Valor ICMS</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">%RedBC ICMS</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">%PIS</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">VBC PIS</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Valor Pis</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">%COFINS</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">VBC COFINS</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Valor Cofins</th>

														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">%IPI</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">VBC IPI</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Valor IPI</th>
														<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Produto">Código de Benefício Fiscal</th>


													</tr>
												</thead>

												<tbody>

													@foreach($itens as $i)

													<input type="hidden" name="codigo_anp[]" value="{{$i['codigo_anp']}}">
													<input type="hidden" name="descricao_anp[]" value="{{$i['descricao_anp']}}">
													<input type="hidden" name="uf_cons[]" value="{{$i['uf_cons']}}">
													<input type="hidden" name="valor_partida[]" value="{{$i['valor_partida']}}">
													<input type="hidden" name="perc_glp[]" value="{{$i['perc_glp']}}">
													<input type="hidden" name="perc_gnn[]" value="{{$i['perc_gnn']}}">
													<input type="hidden" name="perc_gni[]" value="{{$i['perc_gni']}}">

													<input type="hidden" name="unidade_tributavel[]" value="{{$i['unidade_tributavel']}}">
													<input type="hidden" name="quantidade_tributavel[]" value="{{$i['quantidade_tributavel']}}">
													<input type="hidden" name="modBCST[]" value="{{$i['modBCST']}}">
													<input type="hidden" name="vBCST[]" value="{{$i['vBCST']}}">
													<input type="hidden" name="pICMSST[]" value="{{$i['pICMSST']}}">
													<input type="hidden" name="vICMSST[]" value="{{$i['vICMSST']}}">
													<input type="hidden" name="pMVAST[]" value="{{$i['pMVAST']}}">
													<input type="hidden" name="vBCSTRet[]" value="{{$i['vBCSTRet']}}">
													<input type="hidden" name="vICMSSubstituto[]" value="{{$i['vICMSSubstituto']}}">
													<input type="hidden" name="vICMSSTRet[]" value="{{$i['vICMSSTRet']}}">
													<input type="hidden" name="orig[]" value="{{$i['orig']}}">
													<input type="hidden" name="pST[]" value="{{$i['pST']}}">
													<input type="hidden" name="vICMS[]" value="{{$i['vICMS']}}">

													<tr id="tr_{{$i['codigo']}}">
														{{-- <td>
															<a onclick="removeItem('{{$i['codigo']}}')" class="btn btn-danger">
																<i class="fa fa-trash"></i>
															</a>
														</td> --}}

														<td>
															<button type="button" class="btn btn-danger btn-delete-row"><i class="fa fa-trash"></i></button>
														</td>

														<td class="sticky-col first-col">
															<input type="" class="form-control" value="{{$i['xProd']}}" name="nome[]">
														</td>
														<td>
															<input readonly type="" class="form-control" value="{{$i['codigo']}}" name="codigo[]">
														</td>
														<td>
															<input readonly type="tel" class="form-control" value="{{$i['NCM']}}" name="ncm[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control" value="{{$i['CFOP']}}" name="cfop[]">
														</td>

														<td>
															<input type="" class="form-control qtd" value="{{$i['qCom']}}" name="qtd[]">
														</td>

														<td>
															<input type="tel" class="form-control money value_unit" value="{{ number_format((float)$i['vUnCom'], $casas_decimais, ',', '.')}}" name="value_unit[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control money2" value="{{ number_format((float)$i['vUnCom']*$i['qCom'], 2, ',', '.')}}" name="sub_total[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control" value="{{$i['codBarras']}}" name="codBarras[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control" value="{{$i['uCom']}}" name="uCom[]">
														</td>

														<td>
															<select name="cst_csosn[]" class="form-control">
																@foreach(App\Models\Product::listaCSTCSOSN() as $key => $item)
																<option @if($i['cst_csosn'] == $key) selected @endif value="{{$key}}">
																	{{$item}}
																</option>
																@endforeach
															</select>
														</td>

														<td>
															<select name="cst_pis[]" class="form-control">
																@foreach(App\Models\Product::listaCST_PIS_COFINS() as $key => $item)
																<option @if($i['cst_pis'] == $key) selected @endif value="{{$key}}">
																	{{$item}}
																</option>
																@endforeach
															</select>
														</td>
														<td>
															<select name="cst_cofins[]" class="form-control">
																@foreach(App\Models\Product::listaCST_PIS_COFINS() as $key => $item)
																<option @if($i['cst_cofins'] == $key) selected @endif value="{{$key}}">
																	{{$item}}
																</option>
																@endforeach
															</select>
														</td>

														<td>
															<select name="cst_ipi[]" class="form-control">
																@foreach(App\Models\Product::listaCST_IPI() as $key => $item)
																<option @if($i['cst_ipi'] == $key) selected @endif value="{{$key}}">
																	{{$item}}
																</option>
																@endforeach
															</select>
														</td>

														<td>
															<input type="tel" class="form-control percentage" value="{{number_format($i['perc_icms'], 2, ',', '.')}}" name="perc_icms[]">
														</td>

														<td>
															<input type="tel" class="form-control money vbc_icms" value="{{number_format($i['vBC'], 2, ',', '.')}}" name="vBC[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control money" value="{{number_format($i['vBC']*($i['perc_icms']/100), 2, ',', '.')}}" name="valor_icms[]">
														</td>

														<td>
															<input type="tel" class="form-control percentage" value="{{number_format($i['pRedBC'], 2, ',', '.')}}" name="pRedBC[]">
														</td>

														<td>
															<input type="tel" class="form-control percentage" value="{{number_format($i['perc_pis'], 2, ',', '.')}}" name="perc_pis[]">
														</td>

														<td>
															<input type="tel" class="form-control money vbc_pis" value="{{number_format($i['vbcPis'], 2, ',', '.')}}" name="vbcPis[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control money" value="{{number_format($i['vbcPis']*($i['perc_pis']/100), 2, ',', '.')}}" name="valor_pis[]">
														</td>

														<td>
															<input type="tel" class="form-control percentage" value="{{number_format($i['perc_cofins'], 2, ',', '.')}}" name="perc_cofins[]">
														</td>

														<td>
															<input type="tel" class="form-control money vbc_cofins" value="{{number_format($i['vbcCofins'], 2, ',', '.')}}" name="vbcCofins[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control money" value="{{number_format($i['vbcCofins']*($i['perc_cofins']/100), 2, ',', '.')}}" name="valor_cofins[]">
														</td>

														<td>
															<input type="tel" class="form-control percentage" value="{{number_format($i['perc_ipi'], 2, ',', '.')}}" name="perc_ipi[]">
														</td>

														<td>
															<input type="tel" class="form-control money vbc_ipi" value="{{number_format($i['vbcIpi'], 2, ',', '.')}}" name="vbcIpi[]">
														</td>

														<td>
															<input readonly type="tel" class="form-control money" value="{{number_format($i['vbcIpi']*($i['perc_ipi']/100), 2, ',', '.')}}" name="valor_ipi[]">
														</td>

														<td>
															<input type="tel" class="form-control cbenef" value="{{ (isset($i->cBenef) ? $i->cBenef : 'SEM CBENEF' )}}" name="cBenef[]">
														</td>

													</tr>
													@endforeach
													
												</tbody>
											</table>
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

		<div class="row">
			<div class="col-sm-12">

				<div class="form-group">

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('natureza_id', 'Natureza de Operação para devolução'. ':*') !!}
							{!! Form::select('natureza_id', $naturezas, null, ['id' => 'natureza_id', 'class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]); !!}
						</div>
					</div>

					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('tipo', 'Tipo'. ':*') !!}
							{!! Form::select('tipo', ['1' => '1 - Saída', '0' => '0 - Entrada'], null, ['id' => 'tipo', 'class' => 'form-control select2', 'required']); !!}
						</div>
					</div>

					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('desconto', 'Desconto'. ':*') !!}
							{!! Form::text('desconto', $dadosNf['vDesc'], ['class' => 'form-control', 'required',
							'placeholder' => 'Desconto']); !!}
						</div>
					</div>

					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('valor_frete', 'Valor do frete'. ':*') !!}
							{!! Form::text('valor_frete', $dadosNf['vFrete'], ['class' => 'form-control', 'required',
							'placeholder' => 'Valor do frete']); !!}
						</div>
					</div>

					<div class="clearfix"></div>
					
					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('vSeguro', 'Valor do seguro'. ':*') !!}
							{!! Form::text('vSeguro', $dadosNf['vSeguro'], ['class' => 'form-control', 'required',
							'placeholder' => 'Valor do seguro']); !!}
						</div>
					</div>

					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('vOutro', 'Outras despesas'. ':*') !!}
							{!! Form::text('vOutro', $dadosNf['vOutro'], ['class' => 'form-control', 'required',
							'placeholder' => 'Outras despesas']); !!}
						</div>
					</div>


					<div class="col-sm-5">
						<div class="form-group">
							{!! Form::label('motivo', 'Motivo'. ':*') !!}
							{!! Form::text('motivo', null, ['class' => 'form-control', 'required',
							'placeholder' => 'Motivo']); !!}
						</div>
					</div>

					<div class="col-sm-3">
						<div class="form-group">
							{!! Form::label('observacao', 'Observação'. ':') !!}
							{!! Form::text('observacao', null, ['class' => 'form-control',
							'placeholder' => 'Observação']); !!}
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="row">
			<div class="col-sm-12">
				<div class="box @if(!empty($class)) {{$class}} @else box-danger @endif" id="accordion">
					<div class="box-header with-border" style="cursor: pointer;">
						<h3 class="box-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
								Transportadora
							</a>
						</h3>
					</div>
					<div id="collapseFilter" class="panel-collapse active collapse" aria-expanded="true">
						<div class="box-body">
							<div class="col-md-3">
								<div class="form-group">
									{!! Form::label('transportadora_nome', 'Nome:' ) !!}
									{!! Form::text('transportadora_nome', $infoFrete ? $infoFrete['transportadora_nome'] : '', ['class' => 'form-control','placeholder' => 'Nome']); !!}
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									{!! Form::label('transportadora_cidade', 'Cidade:' ) !!}
									{!! Form::text('transportadora_cidade', $infoFrete ? $infoFrete['transportadora_cidade'] : '', ['class' => 'form-control','placeholder' => 'Cidade']); !!}
								</div>
							</div>

							<div class="col-sm-1">
								<div class="form-group">
									{!! Form::label('transportadora_uf', 'UF'. ':') !!}
									{!! Form::select('transportadora_uf', $estados, $infoFrete ? $infoFrete['transportadora_uf'] : '', ['id' => 'transportadora_uf', 'class' => 'form-control select2', 'placeholder' => 'UF']); !!}
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									{!! Form::label('transportadora_cpf_cnpj', 'CPF/CNPJ:' ) !!}
									{!! Form::text('transportadora_cpf_cnpj', $infoFrete ? $infoFrete['transportadora_cpf_cnpj'] : '', ['class' => 'form-control','placeholder' => 'CPF/CNPJ']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('transportadora_ie', 'IE:' ) !!}
									{!! Form::text('transportadora_ie', $infoFrete ? $infoFrete['transportadora_ie'] : '', ['class' => 'form-control','placeholder' => 'IE']); !!}
								</div>
							</div>

							<div class="col-md-5">
								<div class="form-group">
									{!! Form::label('transportadora_endereco', 'Logradouro:' ) !!}
									{!! Form::text('transportadora_endereco', $infoFrete ? $infoFrete['transportadora_endereco'] : '', ['class' => 'form-control','placeholder' => 'Logradouro']); !!}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<div class="box @if(!empty($class)) {{$class}} @else box-info @endif" id="accordion">
					<div class="box-header with-border" style="cursor: pointer;">
						<h3 class="box-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter2">
								Frete
							</a>
						</h3>
					</div>
					<div id="collapseFilter2" class="panel-collapse active collapse" aria-expanded="true">
						<div class="box-body">
							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_quantidade', 'Quantidade:' ) !!}
									{!! Form::text('frete_quantidade', $infoFrete ? $infoFrete['frete_quantidade'] : '', ['class' => 'form-control','placeholder' => 'Quantidade']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_especie', 'Espécie:' ) !!}
									{!! Form::text('frete_especie', $infoFrete ? $infoFrete['frete_especie'] : '', ['class' => 'form-control','placeholder' => 'Espécie']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_marca', 'Marca:' ) !!}
									{!! Form::text('frete_marca', $infoFrete ? $infoFrete['frete_marca'] : '', ['class' => 'form-control','placeholder' => 'Marca']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_numero', 'Número:' ) !!}
									{!! Form::text('frete_numero', $infoFrete ? $infoFrete['frete_numero'] : '', ['class' => 'form-control','placeholder' => 'Número']); !!}
								</div>
							</div>


							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_tipo', 'Tipo do frete:' ) !!}

									{!! Form::select('frete_tipo', $tiposFrete, $infoFrete ? $infoFrete['frete_tipo'] : '', ['class' => 'form-control select2', 'data-default' => 'percentage']); !!}

								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_peso_bruto', 'Peso bruto:' ) !!}
									{!! Form::text('frete_peso_bruto', $infoFrete ? $infoFrete['frete_peso_bruto'] : '', ['class' => 'form-control','placeholder' => 'Peso bruto']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('frete_peso_liquido', 'Peso liquido:' ) !!}
									{!! Form::text('frete_peso_liquido', $infoFrete ? $infoFrete['frete_peso_liquido'] : '', ['class' => 'form-control','placeholder' => 'Peso liquido']); !!}
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group">
									{!! Form::label('veiculo_placa', 'Placa' ) !!}
									{!! Form::text('veiculo_placa', $infoFrete ? $infoFrete['veiculo_placa'] : '', ['class' => 'form-control','placeholder' => 'Placa', 'data-mask="AAA-AAAA"', 'data-mask-reverse="true"']); !!}
								</div>
							</div>

							<div class="col-sm-1">
								<div class="form-group">
									{!! Form::label('veiculo_uf', 'UF'. ':') !!}
									{!! Form::select('veiculo_uf', $estados, $infoFrete ? $infoFrete['veiculo_uf'] : '', ['id' => 'veiculo_uf', 'class' => 'form-control select2', 'placeholder' => 'UF']); !!}
								</div>
							</div>

							
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary pull-right btn-flat">Salvar Devolução</button>
			</div>
		</div>


	</div>

	@endcomponent
	{!! Form::close() !!}


</section>

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#product_table').doubleScroll();
	});
	$('product_table')
	$('#perc_venda').mask('000.00', {reverse: true})
	$('#valor_frete').mask('00000000,00', {reverse: true})
	$('#desconto').mask('00000000,00', {reverse: true})
	$('.qtd').mask('00000000,0000', {reverse: true})

	// function removeItem(id){
	// 	console.log("id: ",'#tr_' + id)
	// 	$('#tr_' + id).remove();
	// 	swal("Sucesso", "Item "+id+" removido", "success")
	// }

	$(".table-devolucao").on("click", ".btn-delete-row", function () {
    $(this).closest("tr").remove();
    swal("Sucesso", "Parcela removida!", "success");
});

	$('.qtd').keyup((target) => {
		let qtd = target.target.value
		let id = target.target.title

		// for(let i = 0; i < ITENS.length; i++){
		// 	if(ITENS[i].codigo == id){
		// 		ITENS[i].qCom = qtd
		// 	}
		// }

		// console.log(ITENS)
		// $('#itens').val(JSON.stringify(ITENS))

	})
</script>
<script type="text/javascript" src="/js/devolucao.js"></script>

@endsection


<!-- /.content -->

@endsection
