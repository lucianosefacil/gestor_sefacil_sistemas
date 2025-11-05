<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Business;
use App\Models\City;
use App\Models\Unit;
use App\Models\Variation;
use App\Models\ProductVariation;
use App\Models\VariationLocationDetails;
use App\Models\PurchaseLine;
use App\Models\BusinessLocation;
use App\Models\Devolucao;
use App\Models\TransactionPayment;
use App\Models\ProdutoSku;


class PurchaseXmlController extends Controller
{
	public function index()
	{
		$business_id = request()->session()->get('user.business_id');
		$business = Business::find($business_id);
		$erros = [];
		if ($business->cnpj == '00.000.000/0000-00') {
			$msg = 'Informe a configuração do emitente';
			array_push($erros, $msg);
		}

		if (sizeof($erros) > 0) {
			return view('nfe.erros')
				->with(compact('erros'));
		}

		return view('purchase_xml.index');
	}

	public function verXml(Request $request)
	{
		$business_id = request()->session()->get('user.business_id');
		$business = Business::find($business_id);

		$tipo_unidades = Unit::where('business_id', $business_id)->get();
		try {
			if ($request->hasFile('file')) {

				$arquivo = $request->hasFile('file');
				$xml = simplexml_load_file($request->file);
				$business_id = request()->session()->get('user.business_id');
				$msgImport  = "";
				if ($xml->NFe->infNFe) {
					$msgImport = $this->validaChave($xml->NFe->infNFe->attributes()->Id, $business_id);
				} else {
					$output = [
						'success' => 0,
						'msg' => 'Não foi possível ler este XML!!'
					];
					return redirect()->back()->with('status', $output);
				}

				if ($msgImport == "") {
					$user_id = $request->session()->get('user.id');

					$cidade = City::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
					$contact = [
						'business_id' => $business_id,
						'city_id' => $cidade->id,
						'cpf_cnpj' => $xml->NFe->infNFe->emit->CNPJ ?
							$this->formataCnpj($xml->NFe->infNFe->emit->CNPJ) :
							$this->formataCpf($xml->NFe->infNFe->emit->CPF),
						'ie_rg' => $xml->NFe->infNFe->emit->IE,
						'consumidor_final' => 1,
						'contribuinte' => 1,
						'rua' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
						'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
						'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
						'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
						'type' => 'supplier',
						'name' => $xml->NFe->infNFe->emit->xNome,
						'mobile' => '',
						'created_by' => $user_id
					];

					$cnpj = $contact['cpf_cnpj'];
					$fornecedorNovo = Contact::where('cpf_cnpj', $cnpj)
						->where('type', 'supplier')
						->where('business_id', $business->id)
						->first();

					// $resFornecedor = $this->validaFornecedorCadastrado($contact);

					$itens = [];
					$contSemRegistro = 0;
					foreach ($xml->NFe->infNFe->det as $item) {
						$produto = $this->validaProdutoCadastrado($item->prod->xProd, $item->prod->cEAN);
						$produtoNovo = $produto == null ? true : false;

						if ($produtoNovo) $contSemRegistro++;

						$cfop = $item->prod->CFOP;
						$lastCfop = substr($cfop, 1, 3);
						$cfop_externo = '6' . $lastCfop;

						// $cst = $lastCfop;
						$trib = Devolucao::getTrib($item->imposto);




						if ($produto !== null) {
							$produto_atribuido = $this->verificaAtribuido($produto->id);
							$produtoAtribuido = $produto_atribuido === null ? true : false;
						} else {
							$produtoAtribuido = true; // Caso não exista produto, define como "nenhuma atribuição"
							$produto_atribuido = null; // Também ajusta $produto_atribuido
						}

						if ($produto !== null) {
							$info_atribuido = $this->verificaInfoAtribuido($produto->id);
							// $info_atribuido = $produto_atribuido === null ? true : false;
						} else {
							$info_atribuido = true; // Caso não exista produto, define como "nenhuma atribuição"
							$info_atribuido = null; // Também ajusta $produto_atribuido
						}


						$item = [
							'codigo' => $item->prod->cProd,
							'xProd' => $item->prod->xProd,
							'NCM' => $item->prod->NCM,
							'uCom' => $item->prod->uCom,
							'vUnCom' => number_format((float)$item->prod->vUnCom, 4, ",", "."),
							'qCom' => $item->prod->qCom,
							'codBarras' => $item->prod->cEAN,
							'cfop_interno' => $item->prod->CFOP,
							'cfop_externo' => $cfop_externo,
							// 'cst' => $cst,
							'cst_csosn' => $trib['cst_csosn'],
							'produtoNovo' => $produtoNovo,
							'produtoId' => $produtoNovo ? '0' : $produto->id,
							'produtoReferenciado' => $produtoAtribuido ? 'Nenhuma Atribuição' : $produto_atribuido,
							// 'produtoReferenciado' => $produtoAtribuido ? 'Nenhuma Atribuição' : ($produto_atribuido['produto'] ? $produto_atribuido['produto']->name : 'Nome não disponível'),
							'productId' =>  $produtoAtribuido ? '0' : $info_atribuido->produto_referenciado,
							'produtoVariationAtribuido' => $produtoAtribuido ? '0' : $info_atribuido->variation_id,
							'idProdutoSku' => $produtoAtribuido ? '0' : $info_atribuido->id
						];
						array_push($itens, $item);
					}



					$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);

					$vFrete = number_format(
						(float) $xml->NFe->infNFe->total->ICMSTot->vFrete,
						2,
						",",
						"."
					);

					$vDesc = $xml->NFe->infNFe->total->ICMSTot->vDesc;

					$dadosNf = [
						'chave' => $chave,
						'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
						'indPag' => $xml->NFe->infNFe->ide->indPag,
						'nNf' => $xml->NFe->infNFe->ide->nNF,
						'vFrete' => $vFrete,
						'vDesc' => $vDesc,
						'vFinal' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd - (float)$vDesc,
						'novoFornecedor' => $fornecedorNovo == null ? true : false
					];

					$fatura = [];
					if (!empty($xml->NFe->infNFe->cobr->dup)) {
						foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
							$titulo = $dup->nDup;
							$vencimento = $dup->dVenc;
							$vencimento = explode('-', $vencimento);
							$vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
							$vlr_parcela = number_format((float) $dup->vDup, 2, ",", "");

							$parcela = [
								'numero' => $titulo,
								'vencimento' => $vencimento,
								'valor_parcela' => $vlr_parcela
							];
							array_push($fatura, $parcela);
						}
					}

					$business_id = request()->session()->get('user.business_id');

					$business = Business::find($business_id);
					$cnpj = $business->cnpj;

					$cnpj = str_replace(".", "", $cnpj);
					$cnpj = str_replace("/", "", $cnpj);
					$cnpj = str_replace("-", "", $cnpj);
					$cnpj = str_replace(" ", "", $cnpj);

					$file = $request->file;
					$file_name = $chave . ".xml";


					if (!is_dir(public_path('xml_entrada/' . $cnpj))) {
						mkdir(public_path('xml_entrada/' . $cnpj), 0777, true);
					}

					$pathXml = $file->move(public_path('xml_entrada/' . $cnpj), $file_name);
					$business_locations = BusinessLocation::forDropdown($business_id);

					return view('purchase.view_xml')
						->with('contact', $contact)
						->with('itens', $itens)
						->with('cidade', $cidade)
						->with('fatura', $fatura)
						->with('lucro', $business->default_profit_percent)
						->with('tipo_unidades', $tipo_unidades)
						->with('business_locations', $business_locations)
						->with('dadosNf', $dadosNf);
				} else {
					$output = [
						'success' => 0,
						'msg' => 'XML já importado na base de dados!!'
					];

					return back()->with('status', $output);
				}
			} else {
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	private function validaChave($chave, $business_id)
	{
		$msg = "";
		$chave = substr($chave, 3, 44);

		$cp = Transaction::where('chave_entrada', $chave)
			->where('business_id', $business_id)
			->first();

		// $manifesto = ManifestaDfe::
		// where('chave', $chave)
		// ->first();

		if ($cp != null) $msg = "XML já importado";
		// if($manifesto != null) $msg .= "XML já importado através do manifesto fiscal";
		return $msg;
	}

	private function validaFornecedorCadastrado($data)
	{
		$business_id = request()->session()->get('user.business_id');
		$business = Business::find($business_id);
		$cnpj = $data['cpf_cnpj'];
		$fornecedor = Contact::where('cpf_cnpj', $cnpj)
			->where('type', 'supplier')
			->where('business_id', $business->id)
			->first();

		if ($fornecedor == null) {
			$contact = Contact::create($data);

			$fornecedor = Contact::find($contact->id);
		}

		return $fornecedor;
	}

	private function validaProdutoCadastrado($nome, $ean)
	{
		$business_id = request()->session()->get('user.business_id');

		// Primeiro tenta buscar pelo EAN, se informado e válido
		if (!empty($ean) && $ean != 'SEM GTIN') {
			$result = Product::where('codigo_barras', $ean)
				->where('business_id', $business_id)
				->first();

			// Se encontrou pelo código de barras, retorna imediatamente
			if ($result) {
				return $result;
			}

			// Se NÃO encontrou pelo código de barras, NÃO procura pelo nome!
			// Mantém assim para não confundir produtos com mesma descrição e EAN diferentes.
			return null;
		}

		// Só busca por nome se EAN não foi informado ou é 'SEM GTIN'
		return Product::where('name', $nome)
			->where('business_id', $business_id)
			->orderByDesc('id')
			->first();
	}


	private function validaUnidadeCadastrada($nome, $user_id)
	{
		$business_id = request()->session()->get('user.business_id');
		$unidade = Unit::where('short_name', $nome)
			->where('business_id', $business_id)
			->first();

		if ($unidade != null) {
			return $unidade;
		}

		//vai inserir
		$data = [
			'business_id' => $business_id,
			'actual_name' => $nome,
			'short_name' => $nome,
			'allow_decimal' => 1,
			'created_by' => $user_id
		];

		$u = Unit::create($data);
		$unidade = Unit::find($u->id);

		return $unidade;
	}

	private function verificaAtribuido($id)
	{
		$produtosku = ProdutoSku::where('product_id', $id)->first();

		if ($produtosku != null) {

			$prod = $produtosku->produto_referenciado;

			$produto = Product::where('id', $prod)->first();

			if ($produto->type == 'variable') {

				$variation = Variation::where('id', $produtosku->variation_id)->first();

				$produto = $produto->name . ' (' . $variation->name . ')';

				return $produto;
			} else {
				return $produto->name;
			}
		}
	}

	private function verificaInfoAtribuido($id)
	{
		$produto = ProdutoSku::where('product_id', $id)->first();

		return $produto;
	}

	private function formataCnpj($cnpj)
	{
		$temp = substr($cnpj, 0, 2);
		$temp .= "." . substr($cnpj, 2, 3);
		$temp .= "." . substr($cnpj, 5, 3);
		$temp .= "/" . substr($cnpj, 8, 4);
		$temp .= "-" . substr($cnpj, 12, 2);
		return $temp;
	}

	private function formataCpf($cpf)
	{
		$temp = substr($cpf, 0, 3);
		$temp .= "." . substr($cpf, 3, 3);
		$temp .= "." . substr($cpf, 6, 3);
		$temp .= "-" . substr($cpf, 9, 2);

		return $temp;
	}

	public function save(Request $request)
	{
		// dd($request);
		try {
			$business_id = session('user.business_id');
			$user_id = session('user.id');
			$business = Business::find($business_id);

			$contact = json_decode($request->contact, true);
			$itens = json_decode($request->itens_json, true);
			$fatura = json_decode($request->fatura_json, true);
			$dadosNf = json_decode($request->dadosNf, true);
			$conversao = explode(",", $request->conversao);

			$contact = $this->validaFornecedorCadastrado([
				'business_id' => $contact['business_id'],
				'city_id' => $contact['city_id'],
				'cpf_cnpj' => $contact['cpf_cnpj'],
				'ie_rg' => $contact['ie_rg'][0],
				'consumidor_final' => 1,
				'contribuinte' => 1,
				'rua' => $contact['rua'][0],
				'numero' => $contact['numero'][0],
				'bairro' => $contact['bairro'][0],
				'cep' => $contact['cep'][0],
				'type' => 'supplier',
				'name' => $contact['name'][0],
				'mobile' => '',
				'created_by' => $contact['created_by']
			]);

			$purchase = Transaction::firstOrCreate(
				['chave_entrada' => $dadosNf['chave']],
				[
					'business_id' => $business_id,
					'type' => 'purchase',
					'status' => 'received',
					'payment_status' => 'due',
					'contact_id' => $contact->id,
					'transaction_date' => now(),
					'created_by' => $user_id,
					'numero_nfe_entrada' => $dadosNf['nNf'][0],
					'estado' => 'APROVADO',
					'location_id' => $request->location_id,
					'final_total' => $dadosNf['vFinal'],
					'total_before_tax' => $dadosNf['vFinal'],
					'discount_amount' => $dadosNf['vDesc'][0],
					'discount_type' => $dadosNf['vDesc'][0] > 0 ? 'fixed' : null
				]
			);

			foreach ($itens as $i => $item) {
				$taxa = (int)($conversao[$i] ?? 1);
				$quantidade = (float)$item['qCom'] * $taxa;
				$valorCompra = (float)str_replace(',', '.', $item['vUnCom']);

				if ($taxa > 1) {
					$valorCompra = number_format($valorCompra / $taxa, 4, '.', '');
				}

				$unidade = $this->validaUnidadeCadastrada($item['uCom'], $user_id);
				if ($taxa > 1) {
					$unidade = Unit::where('business_id', $business_id)
						->whereIn('short_name', ['UNID', 'UN'])
						->first();
				}

				$sku = $item['codBarras'] != 'SEM GTIN' ? $item['codBarras'] : '';

				$cfop_interno = $business->cfop_saida_estadual_padrao ?: $item['cfop_interno'];
				$cfop_externo = $business->cfop_saida_inter_estadual_padrao ?: $item['cfop_externo'];

				$produtoData = [
					'name' => $item['produto'],
					'business_id' => $business_id,
					'unit_id' => $item['unid_venda'],
					'tax_type' => 'inclusive',
					'barcode_type' => 2,
					'codigo_barras' => $sku,
					'sku' => $item['codigo'],
					'ncm' => $item['ncm'],
					'created_by' => $user_id,
					'perc_icms' => 0,
					'perc_pis' => 0,
					'perc_cofins' => 0,
					'perc_ipi' => 0,
					'cfop_interno' => $cfop_interno,
					'cfop_externo' => $cfop_externo,
					'type' => 'single',
					'enable_stock' => 1,
					'cst_csosn' => $item['cst_csosn'],
					'cst_pis' => $business->cst_cofins_padrao,
					'cst_cofins' => $business->cst_pis_padrao,
					'cst_ipi' => $business->cst_ipi_padrao,
					'cenq_ipi' => '999',
				];

				$prodNovo = $this->validaProdutoCadastrado($item['produto'], $sku);
				// dd($prodNovo);
				$prod = null;
				if ($prodNovo == null) {
					$prod = Product::create($produtoData);
				} else {
					$prod = $prodNovo;
				}

				$produtoVariacao = ProductVariation::firstOrCreate([
					'product_id' => $prod->id,
					'name' => 'DUMMY'
				]);

				$variacao = Variation::firstOrCreate(
					[
						'product_id' => $prod->id,
						'name' => 'DUMMY',
						'product_variation_id' => $produtoVariacao->id
					],
					[
						'sub_sku' => $item['codigo'],
						'cod_barras' => $sku,
						'default_purchase_price' => $this->__convert_value_bd($item['valor_custo']),
						'dpp_inc_tax' => $this->__convert_value_bd($item['valor_custo']),
						'profit_percent' => $item['margem_lucro'],
						'default_sell_price' => $this->__convert_value_bd($item['valor_venda']),
						'sell_price_inc_tax' => $this->__convert_value_bd($item['valor_venda'])
					]
				);

				PurchaseLine::create([
					'transaction_id' => $purchase->id,
					'product_id' => $item['product_id'] > 0 ? (int)$item['product_id'] : $prod->id,
					'variation_id' => $item['variation_id'] > 0 ? (int)$item['variation_id'] : $variacao->id,
					'quantity' => $quantidade,
					'pp_without_discount' => $this->__convert_value_bd($valorCompra),
					'purchase_price' => $this->__convert_value_bd($valorCompra),
					'purchase_price_inc_tax' => $this->__convert_value_bd($valorCompra)
				]);

				\DB::table('product_locations')->updateOrInsert([
					'product_id' => $prod->id,
					'location_id' => $request->location_id
				]);

				if ($item['product_id'] > 0) {

					$existeSku = ProdutoSku::where('product_id', $prod->id)
						->where('produto_referenciado', $item['product_id'])
						->where('variation_id', $item['variation_id'])
						->first();

					if (!$existeSku) {
						ProdutoSku::create([
							'product_id' => $prod->id,
							'produto_referenciado' => $item['product_id'],
							'variation_id' => $item['variation_id']
						]);
					}

					$current_stock = VariationLocationDetails::firstOrNew([
						'product_id' => $item['product_id'],
						'location_id' => $request->location_id
					]);

					if (!$current_stock->exists) {
						$this->openStock($business_id, Product::find($item['product_id']), $valorCompra, $quantidade, $user_id, $request->location_id, $item['variation_id'], $produtoVariacao->id);
					} else {
						$current_stock->qty_available += $quantidade;
						$current_stock->save();
					}
				} else {
					$current_stock = VariationLocationDetails::firstOrNew([
						'product_id' => $prod->id,
						'location_id' => $request->location_id
					]);

					if (!$current_stock->exists) {
						$this->openStock($business_id, $prod, $valorCompra, $quantidade, $user_id, $request->location_id, $variacao->id, $produtoVariacao->id);
					} else {
						$current_stock->qty_available += $quantidade;
						$current_stock->save();
					}
				}
			}

			if ($request->finalize && count($fatura) > 0) {
				$existeFatura = Transaction::where('type', 'expense')
					->where('ref_no', 'NOTA_' . $dadosNf['nNf'][0])
					->where('business_id', $business_id)
					->exists();

				if (!$existeFatura) {
					$len = count($fatura);
					foreach ($fatura as $f) {
						$vencimento = \Carbon\Carbon::createFromFormat('d/m/Y', $f['vencimento'])->format('Y-m-d H:i:s');
						$valor = str_replace(",", ".", $f['valor_parcela']);
						$desconto = (float)$dadosNf['vDesc'][0] / $len;

						Transaction::create([
							'business_id' => $business_id,
							'type' => 'expense',
							'status' => 'final',
							'payment_status' => 'due',
							'contact_id' => $contact->id,
							'transaction_date' => $vencimento,
							'created_by' => $user_id,
							'location_id' => $request->location_id,
							'ref_no' => 'NOTA_' . $dadosNf['nNf'][0],
							'final_total' => $valor,
							'total_before_tax' => $valor,
							'discount_amount' => $desconto,
							'discount_type' => $desconto > 0 ? 'fixed' : null
						]);
					}
				}
			}

			return response()->json(['success' => true, 'message' => 'Bloco salvo com sucesso.']);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => $e->getMessage()]);
		}
	}

	private function __convert_value_bd($valor)
	{
		if (strlen($valor) >= 8) {
			$valor = str_replace(".", "", $valor);
		}
		$valor = str_replace(",", ".", $valor);
		return $valor;
	}

	private function lastCodeProduct()
	{
		$prod = Product::orderBy('id', 'desc')->first();
		if ($prod == null) {
			return '0001';
		} else {
			$v = (int) $prod->sku;
			if ($v < 10) return '000' . ($v + 1);
			elseif ($v < 100) return '00' . ($v + 1);
			elseif ($v < 1000) return '0' . ($v + 1);
			else return $v + 1;
		}
	}


	private function openStock(
		$business_id,
		$produto,
		$valorUnit,
		$quantidade,
		$user_id,
		$location_id,
		$variacao_id,
		$product_variation_id
	) {

		$transaction = Transaction::create(
			[
				'type' => 'opening_stock',
				'opening_stock_product_id' => $produto->id,
				'status' => 'received',
				'business_id' => $business_id,
				'transaction_date' => date('Y-m-d H:i:s'),
				'total_before_tax' => $valorUnit * $quantidade,
				'location_id' => $location_id,
				'final_total' => $valorUnit * $quantidade,
				'payment_status' => 'paid',
				'created_by' => $user_id
			]
		);

		VariationLocationDetails::create([
			'product_id' => $produto->id,
			'location_id' => $location_id,
			'variation_id' => $variacao_id,
			'product_variation_id' => $product_variation_id,
			'qty_available' => $quantidade
		]);
	}

	public function baixarXml($id)
	{
		$business_id = request()->session()->get('user.business_id');

		$purchase = Transaction::where('business_id', $business_id)
			->where('id', $id)
			->where('type', 'purchase')
			->first();

		if ($purchase == null) {
			return redirect('/purchases')
				->with('status', [
					'success' => 0,
					'msg' => 'Não autorizado!!'
				]);
		}

		$business = Business::find($business_id);
		$cnpj = $business->cnpj;

		$cnpj = str_replace(".", "", $cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if (file_exists(public_path('xml_entrada/' . $cnpj . '/' . $purchase->chave . '.xml'))) {
			return response()->download(public_path('xml_entrada/' . $cnpj . '/' . $purchase->chave . '.xml'));
		} else {
			return redirect('/purchases')
				->with('status', [
					'success' => 0,
					'msg' => 'Arquivo XML não encontrado!!'
				]);
		}
	}

	public function baixarXmlEntrada($id)
	{
		$business_id = request()->session()->get('user.business_id');

		$purchase = Transaction::where('business_id', $business_id)
			->where('id', $id)
			->where('type', 'purchase')
			->first();

		if ($purchase == null) {
			return redirect('/purchases')
				->with('status', [
					'success' => 0,
					'msg' => 'Não autorizado!!'
				]);
		}

		$business = Business::find($business_id);
		$cnpj = $business->cnpj;

		$cnpj = str_replace(".", "", $cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if (file_exists(public_path('xml_entrada/' . $cnpj . '/' . $purchase->chave_entrada . '.xml'))) {
			return response()->download(public_path('xml_entrada/' . $cnpj . '/' . $purchase->chave_entrada . '.xml'));
		} else {
			return redirect('/purchases')
				->with('status', [
					'success' => 0,
					'msg' => 'Arquivo XML não encontrado!!'
				]);
		}
	}


	public function desassociarProd(Request $request)
	{
		$id = $request->input('id');

		$produtoSku = ProdutoSku::find($id);

		if ($produtoSku) {
			$produtoSku->delete();
			return response()->json([
				'success' => true,
				'message' => 'Produto desassociado com sucesso!.'
			]);
		} else {
			return response()->json([
				'success' => false,
				'message' => 'Falha ao executar o processo!.'
			]);
		}
	}
}
