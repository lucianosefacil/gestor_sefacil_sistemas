<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certificado;
use App\Services\Nfse\NFSeService;
use NFePHP\Common\Certificate;
use App\Models\ConfigNota;
use App\Models\Contact;
use App\Models\Servico;
use App\Models\NfseServico;
use App\Models\NfseConfig;
use App\Models\OrdemServico;
use App\Models\Nfse;
use App\Models\Business;
use App\Models\BusinessLocation;
use Webmaniabr\Nfse\Api\Connection;
use Webmaniabr\Nfse\Api\Exceptions\APIException;
use Webmaniabr\Nfse\Interfaces\APIResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webmaniabr\Nfse\Models\NFSe as NFSeWeb;
use App\Models\User;
use App\Models\City;
use CloudDfe\SdkPHP\NFSe as NfseSdk;



class NfseController extends Controller
{
	protected $empresa_id = null;
	public function __construct()
	{
		// if (!is_dir(public_path('nfse_pdf'))) {
		// 	mkdir(public_path('nfse_pdf'), 0777, true);
		// }
		// $this->middleware(function ($request, $next) {
		// 	$this->empresa_id = $request->empresa_id;
		// 	$value = session('user_logged');
		// 	if (!$value) {
		// 		return redirect("/login");
		// 	}
		// 	return $next($request);
		// });
	}

	public function index()
	{
		// $config = Business::where('empresa_id', $this->empresa_id)
		// 	->first();

		$business_id = request()->session()->get('user.business_id');

		$config = Business::find($business_id);

		// $config = BusinessLocation::where('business_id', $business_id)->first();


		if ($config == null) {
			$output = [
				'success' => 0,
				'msg' => 'Realize a configuração do emitente!'
			];
			return redirect()->back()->with('status', $output);
		}

		$nfses = Nfse::where('empresa_id', $business_id)
			->orderBy('id', 'desc')
			->get();

		$total = 0;
		foreach ($nfses as $item) {
			$total += $item->valor_total;
		}

		if (!$config->certificado) {
			$output = [
				'success' => 0,
				'msg' => 'Configure o certificado para consultar'
			];
			return redirect()->back()->with('status', $output);
		}

		$certificado = Business::where('id', $business_id)
			->first();

		$clientes = Contact::where('business_id', $business_id)
			->get();


		$estado = 'TODOS';
		return view('nfse.index', compact('nfses', 'certificado', 'config', 'estado', 'total'))
			->with('links', true)
			->with('clientes', $clientes)
			->with('title', 'NFSe');
	}

	public function filtro(Request $request)
	{
		$business_id = request()->session()->get('user.business_id');
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$estado = $request->estado;
		$cliente_id = $request->cliente_id;

		$nfses = Nfse::where('nfses.empresa_id', $business_id)
			->select('nfses.*');

		if (($dataInicial) && ($dataFinal)) {
			$nfses->whereBetween('created_at', [
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal, true)
			]);
		}

		if ($estado) {
			$nfses->where('estado', $estado);
		}

		// if ($cliente_id != 'null') {
		// 	$nfses->where('cliente_id', $cliente_id);
		// }

		$nfses = $nfses->get();

		$total = 0;
		foreach ($nfses as $item) {
			$total += $item->valor_total;
		}

		$config = Business::find($business_id);

		if (!$config->certificado) {
			return response()->json('Configure o certificado para consultar', 403);
		}

		$certificado = $config->certificado;

		$clientes = Contact::where('business_id', $business_id)
			->get();

		$config = BusinessLocation::where('business_id', $business_id)
			->first();


		return view('nfse.index', compact('nfses', 'certificado', 'config'))
			->with('dataInicial', $dataInicial)
			->with('dataFinal', $dataFinal)
			->with('estado', $estado)
			->with('total', $total)
			// ->with('cliente_id', $cliente_id)
			->with('clientes', $clientes)
			->with('tipoPesquisa', $request->tipo_pesquisa)
			->with('title', 'NFSe');
	}

	private function parseDate($date, $plusDay = false)
	{
		if ($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day", strtotime(str_replace("/", "-", $date))));
	}

	public function create()
	{
		$business_id = request()->session()->get('user.business_id');
		$clientes = Contact::where('business_id', $business_id)
			->orderBy('name', 'asc')
			->get();

		$servicos = Servico::where('business_id', $business_id)
			->orderBy('nome', 'desc')
			->get();

		$config = BusinessLocation::where('business_id', $business_id)
			->first();

		$nfseConfig = NfseConfig::where('empresa_id', $business_id)
			->first();

		$types = Contact::getContactTypes();
		$tipo = 'customer';

		$usuario = User::allUsersDropdown($business_id, false);

		$cities = $this->prepareCities();

		return view('nfse.create', compact('clientes', 'config', 'servicos', 'nfseConfig', 'tipo', 'types', 'usuario'))
			->with('title', 'Nova NFSe')
			->with('estados', $this->prepareUFs())
			->with('cities', $cities);
	}

	public function clone($id)
	{
		$business_id = request()->session()->get('user.business_id');
		$item = Nfse::findOrFail($id);
		$clientes = Contact::where('business_id', $business_id)
			->orderBy('name', 'desc')
			->get();

		$config = BusinessLocation::where('business_id', $business_id)
			->first();

		$servicos = Servico::where('business_id', $business_id)
			->orderBy('name', 'desc')
			->get();

		return view('nfse.create', compact('clientes', 'config', 'item', 'servicos'))
			->with('clone', 1)
			->with('title', 'Clonar NFSe');
	}

	public function edit($id)
	{
		$business_id = request()->session()->get('user.business_id');
		$item = Nfse::findOrFail($id);
		$clientes = Contact::where('business_id', $business_id)
			->orderBy('name', 'desc')
			->get();

		$config = BusinessLocation::where('business_id', $business_id)
			->first();

		$servicos = Servico::where('business_id', $business_id)
			->orderBy('nome', 'desc')
			->get();

		$types = Contact::getContactTypes();
		$tipo = 'customer';

		$usuario = User::allUsersDropdown($business_id, false);

		$cities = $this->prepareCities();

		return view('nfse.create', compact('clientes', 'config', 'item', 'servicos', 'tipo', 'types', 'usuario'))
			->with('title', 'Editar NFSe')
			->with('estados', $this->prepareUFs())
			->with('cities', $cities);
	}

	public function delete($id)
	{
		$item = Nfse::findOrFail($id);
		try {
			if ($item) {
				$item->servico()->delete();
				$item->delete();
				$output = [
					'success' => 1,
					'msg' => 'Nfse removida!'
				];
				return redirect()->back()->with('status', $output);
			}
		} catch (\Exception $e) {
			$output = [
				'success' => 0,
				'msg' => 'Algo deu errado: ' . $e->getMessage()
			];
			return redirect()->back()->with('status', $output);
		}
	}

	public function update(Request $request, $id)
	{
		$this->_validate($request);
		$item = Nfse::findOrFail($id);
		try {
			$result = DB::transaction(function () use ($request, $item) {

				// Calcular valor líquido final (igual à fórmula da view)
				$valorServico = (float)str_replace(',', '.', $request->valor_servico);
				$valorDeducoes = $request->valor_deducoes ? (float)str_replace(',', '.', $request->valor_deducoes) : 0;

				// Base de cálculo = valor_servico - deduções
				$base = max($valorServico - $valorDeducoes, 0);

				// Impostos calculados sobre a base
				$aliqPIS = $request->aliquota_pis ? (float)str_replace(',', '.', $request->aliquota_pis) : 0;
				$aliqCOFINS = $request->aliquota_cofins ? (float)str_replace(',', '.', $request->aliquota_cofins) : 0;
				$aliqINSS = $request->aliquota_inss ? (float)str_replace(',', '.', $request->aliquota_inss) : 0;
				$aliqIR = $request->aliquota_ir ? (float)str_replace(',', '.', $request->aliquota_ir) : 0;
				$aliqCSLL = $request->aliquota_csll ? (float)str_replace(',', '.', $request->aliquota_csll) : 0;
				$aliqISS = $request->aliquota_iss ? (float)str_replace(',', '.', $request->aliquota_iss) : 0;
				$aliqISSQN = $request->aliquota_issqn ? (float)str_replace(',', '.', $request->aliquota_issqn) : 0;

				$pis = $base * ($aliqPIS / 100);
				$cofins = $base * ($aliqCOFINS / 100);
				$inss = $base * ($aliqINSS / 100);
				$ir = $base * ($aliqIR / 100);
				$csll = $base * ($aliqCSLL / 100);
				$issRetido = ($request->iss_retido == 1) ? $base * ($aliqISS / 100) : 0;
				$issqn = $base * ($aliqISSQN / 100);

				// Descontos e outras retenções
				$descIncond = $request->desconto_incondicional ? (float)str_replace(',', '.', $request->desconto_incondicional) : 0;
				$descCond = $request->desconto_condicional ? (float)str_replace(',', '.', $request->desconto_condicional) : 0;
				$outrasRet = $request->outras_retencoes ? (float)str_replace(',', '.', $request->outras_retencoes) : 0;

				// VALOR LÍQUIDO FINAL (igual ao JavaScript da view)
				$valorLiquido = $base - ($pis + $cofins + $inss + $ir + $csll + $issRetido + $issqn) - $outrasRet - $descIncond - $descCond;
				$totalServico = max($valorLiquido, 0);

				$request->merge([
					'valor_total' => $totalServico,
					'cliente_id' => $request->cliente
				]);

				$item->fill($request->all())->update();

				$item->servico->delete();
				NfseServico::create([
					'nfse_id' => $item->id,
					'discriminacao' => $request->discriminacao,
					'valor_servico' => str_replace(',', '.', $request->valor_servico),
					'servico_id' => $request->servico_id,
					'codigo_cnae' => $request->codigo_cnae ?? '',
					'codigo_servico' => $request->codigo_servico ?? '',
					'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
					'exigibilidade_iss' => $request->exigibilidade_iss,
					'iss_retido' => $request->iss_retido,
					'data_competencia' => $request->data_competencia ?? null,
					'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
					'cidade_local_prestacao_servico' => $request->cidade_local_prestacao_servico ?? '',
					'valor_deducoes' => $request->valor_deducoes ? str_replace(',', '.', $request->valor_deducoes) : 0,
					'desconto_incondicional' => $request->desconto_incondicional ? str_replace(',', '.', $request->desconto_incondicional) : 0,
					'desconto_condicional' => $request->desconto_condicional ? str_replace(',', '.', $request->desconto_condicional) : 0,
					'outras_retencoes' => $request->outras_retencoes ? str_replace(',', '.', $request->outras_retencoes) : 0,
					'valor_aliquota' => $request->valor_aliquota ? str_replace(',', '.', $request->valor_aliquota) : 0,
					'aliquota_iss' => $request->aliquota_iss ? str_replace(',', '.', $request->aliquota_iss) : 0,
					'aliquota_pis' => $request->aliquota_pis ? str_replace(',', '.', $request->aliquota_pis) : 0,
					'aliquota_cofins' => $request->aliquota_cofins ? str_replace(',', '.', $request->aliquota_cofins) : 0,
					'aliquota_inss' => $request->aliquota_inss ? str_replace(',', '.', $request->aliquota_inss) : 0,
					'aliquota_ir' => $request->aliquota_ir ? str_replace(',', '.', $request->aliquota_ir) : 0,
					'aliquota_csll' => $request->aliquota_csll ? str_replace(',', '.', $request->aliquota_csll) : 0,
					'intermediador' => $request->intermediador ?? 'n',
					'documento_intermediador' => $request->documento_intermediador ?? '',
					'nome_intermediador' => $request->nome_intermediador ?? '',
					'im_intermediador' => $request->im_intermediador ?? '',
					'responsavel_retencao_iss' => $request->responsavel_retencao_iss ?? 1,

				]);
			});
			$output = [
				'success' => 1,
				'msg' => 'Nfse atualizada!'
			];
			return redirect()->route('nfse.index')->with('status', $output);
		} catch (\Exception $e) {
			// echo $e->getLine();
			// die;
			$output = [
				'success' => 0,
				'msg' => 'Algo deu errado: ' . $e->getMessage()
			];
			return redirect()->route('nfse.index')->with('status', $output);
		}
	}

	public function store(Request $request)
	{
		$this->_validate($request);
		try {
			$result = DB::transaction(function () use ($request) {
				// Calcular valor líquido final (igual à fórmula da view)
				$valorServico = (float)str_replace(',', '.', $request->valor_servico);
				$valorDeducoes = $request->valor_deducoes ? (float)str_replace(',', '.', $request->valor_deducoes) : 0;

				// Base de cálculo = valor_servico - deduções
				$base = max($valorServico - $valorDeducoes, 0);

				// Impostos calculados sobre a base
				$aliqPIS = $request->aliquota_pis ? (float)str_replace(',', '.', $request->aliquota_pis) : 0;
				$aliqCOFINS = $request->aliquota_cofins ? (float)str_replace(',', '.', $request->aliquota_cofins) : 0;
				$aliqINSS = $request->aliquota_inss ? (float)str_replace(',', '.', $request->aliquota_inss) : 0;
				$aliqIR = $request->aliquota_ir ? (float)str_replace(',', '.', $request->aliquota_ir) : 0;
				$aliqCSLL = $request->aliquota_csll ? (float)str_replace(',', '.', $request->aliquota_csll) : 0;
				$aliqISS = $request->aliquota_iss ? (float)str_replace(',', '.', $request->aliquota_iss) : 0;
				$aliqISSQN = $request->aliquota_issqn ? (float)str_replace(',', '.', $request->aliquota_issqn) : 0;

				$pis = $base * ($aliqPIS / 100);
				$cofins = $base * ($aliqCOFINS / 100);
				$inss = $base * ($aliqINSS / 100);
				$ir = $base * ($aliqIR / 100);
				$csll = $base * ($aliqCSLL / 100);
				$issRetido = ($request->iss_retido == 1) ? $base * ($aliqISS / 100) : 0;
				$issqn = $base * ($aliqISSQN / 100);

				// Descontos e outras retenções
				$descIncond = $request->desconto_incondicional ? (float)str_replace(',', '.', $request->desconto_incondicional) : 0;
				$descCond = $request->desconto_condicional ? (float)str_replace(',', '.', $request->desconto_condicional) : 0;
				$outrasRet = $request->outras_retencoes ? (float)str_replace(',', '.', $request->outras_retencoes) : 0;

				// VALOR LÍQUIDO FINAL (igual ao JavaScript da view)
				$valorLiquido = $base - ($pis + $cofins + $inss + $ir + $csll + $issRetido + $issqn) - $outrasRet - $descIncond - $descCond;
				$totalServico = max($valorLiquido, 0);

				$business_id = request()->session()->get('user.business_id');
				$nfse = Nfse::create([
					'empresa_id' => $business_id,
					'filial_id' => NULL,
					'valor_total' => $totalServico,
					'estado' => 'novo',
					'serie' => '',
					'codigo_verificacao' => '',
					'numero_nfse' => 0,
					'url_xml' => '',
					'url_pdf_nfse' => '',
					'url_pdf_rps' => '',
					'cliente_id' => $request->cliente,
					'natureza_operacao' => $request->natureza_operacao,
					'documento' => $request->documento,
					'razao_social' => $request->razao_social,
					'nome_fantasia' => $request->nome_fantasia,
					'im' => $request->im ?? '',
					'ie' => $request->ie ?? '',
					'cep' => $request->cep ?? '',
					'rua' => $request->rua,
					'numero' => $request->numero,
					'bairro' => $request->bairro,
					'complemento' => $request->complemento ?? '',
					'cidade_id' => $request->cidade_id,
					'email' => $request->email ?? '',
					'telefone' => $request->telefone ?? ''
				]);

				NfseServico::create([
					'nfse_id' => $nfse->id,
					'discriminacao' => $request->discriminacao,
					'valor_servico' => str_replace(',', '.', $request->valor_servico),
					'servico_id' => $request->servico_id,
					'codigo_cnae' => $request->codigo_cnae ?? '',
					'codigo_servico' => $request->codigo_servico ?? '',
					'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
					'exigibilidade_iss' => $request->exigibilidade_iss,
					'iss_retido' => $request->iss_retido,
					'data_competencia' => $request->data_competencia ?? null,
					'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
					'cidade_local_prestacao_servico' => $request->cidade_local_prestacao_servico ?? '',
					'valor_deducoes' => $request->valor_deducoes ? str_replace(',', '.', $request->valor_deducoes) : 0,
					'desconto_incondicional' => $request->desconto_incondicional ? str_replace(',', '.', $request->desconto_incondicional) : 0,
					'desconto_condicional' => $request->desconto_condicional ? str_replace(',', '.', $request->desconto_condicional) : 0,
					'outras_retencoes' => $request->outras_retencoes ? str_replace(',', '.', $request->outras_retencoes) : 0,
					'valor_aliquota' => $request->valor_aliquota ? str_replace(',', '.', $request->valor_aliquota) : 0,
					'aliquota_iss' => $request->aliquota_iss ? str_replace(',', '.', $request->aliquota_iss) : 0,
					'aliquota_pis' => $request->aliquota_pis ? str_replace(',', '.', $request->aliquota_pis) : 0,
					'aliquota_cofins' => $request->aliquota_cofins ? str_replace(',', '.', $request->aliquota_cofins) : 0,
					'aliquota_inss' => $request->aliquota_inss ? str_replace(',', '.', $request->aliquota_inss) : 0,
					'aliquota_ir' => $request->aliquota_ir ? str_replace(',', '.', $request->aliquota_ir) : 0,
					'aliquota_csll' => $request->aliquota_csll ? str_replace(',', '.', $request->aliquota_csll) : 0,
					'intermediador' => $request->intermediador ?? 'n',
					'documento_intermediador' => $request->documento_intermediador ?? '',
					'nome_intermediador' => $request->nome_intermediador ?? '',
					'im_intermediador' => $request->im_intermediador ?? '',
					'responsavel_retencao_iss' => $request->responsavel_retencao_iss ?? 1,

				]);

				if (isset($request->os_id)) {
					$ordem = OrdemServico::findOrFail($request->os_id);
					$ordem->nfse_id = $nfse->id;
					$ordem->save();
				}
			});
			$output = [
				'success' => 1,
				'msg' => 'Nfse criada'
			];
			return redirect()->route('nfse.index')->with('status', $output);
		} catch (\Exception $e) {
			$output = [
				'success' => 0,
				'msg' => 'Algo deu errado: ' . $e->getMessage()
			];
			return redirect()->route('nfse.index')->with('status', $output);
		}
	}

	public function storeAjax(Request $request)
	{

		try {
			$result = DB::transaction(function () use ($request) {
				$request = (object)$request->data;

				// Calcular valor líquido final (igual à fórmula da view)
				$valorServico = (float)str_replace(',', '.', $request->valor_servico);
				$valorDeducoes = isset($request->valor_deducoes) && $request->valor_deducoes ? (float)str_replace(',', '.', $request->valor_deducoes) : 0;

				// Base de cálculo = valor_servico - deduções
				$base = max($valorServico - $valorDeducoes, 0);

				// Impostos calculados sobre a base
				$aliqPIS = isset($request->aliquota_pis) && $request->aliquota_pis ? (float)str_replace(',', '.', $request->aliquota_pis) : 0;
				$aliqCOFINS = isset($request->aliquota_cofins) && $request->aliquota_cofins ? (float)str_replace(',', '.', $request->aliquota_cofins) : 0;
				$aliqINSS = isset($request->aliquota_inss) && $request->aliquota_inss ? (float)str_replace(',', '.', $request->aliquota_inss) : 0;
				$aliqIR = isset($request->aliquota_ir) && $request->aliquota_ir ? (float)str_replace(',', '.', $request->aliquota_ir) : 0;
				$aliqCSLL = isset($request->aliquota_csll) && $request->aliquota_csll ? (float)str_replace(',', '.', $request->aliquota_csll) : 0;
				$aliqISS = isset($request->aliquota_iss) && $request->aliquota_iss ? (float)str_replace(',', '.', $request->aliquota_iss) : 0;
				$aliqISSQN = isset($request->aliquota_issqn) && $request->aliquota_issqn ? (float)str_replace(',', '.', $request->aliquota_issqn) : 0;

				$pis = $base * ($aliqPIS / 100);
				$cofins = $base * ($aliqCOFINS / 100);
				$inss = $base * ($aliqINSS / 100);
				$ir = $base * ($aliqIR / 100);
				$csll = $base * ($aliqCSLL / 100);
				$issRetido = (isset($request->iss_retido) && $request->iss_retido == 1) ? $base * ($aliqISS / 100) : 0;
				$issqn = $base * ($aliqISSQN / 100);

				// Descontos e outras retenções
				$descIncond = isset($request->desconto_incondicional) && $request->desconto_incondicional ? (float)str_replace(',', '.', $request->desconto_incondicional) : 0;
				$descCond = isset($request->desconto_condicional) && $request->desconto_condicional ? (float)str_replace(',', '.', $request->desconto_condicional) : 0;
				$outrasRet = isset($request->outras_retencoes) && $request->outras_retencoes ? (float)str_replace(',', '.', $request->outras_retencoes) : 0;

				// VALOR LÍQUIDO FINAL (igual ao JavaScript da view)
				$valorLiquido = $base - ($pis + $cofins + $inss + $ir + $csll + $issRetido + $issqn) - $outrasRet - $descIncond - $descCond;
				$totalServico = max($valorLiquido, 0);

				$business_id = request()->session()->get('user.business_id');
				$nfse = Nfse::create([
					'empresa_id' => $business_id,
					'filial_id' => NULL,
					'valor_total' => $totalServico,
					'estado' => 'novo',
					'serie' => '',
					'codigo_verificacao' => '',
					'numero_nfse' => 0,
					'url_xml' => '',
					'url_pdf_nfse' => '',
					'url_pdf_rps' => '',
					'cliente_id' => $request->cliente,
					'natureza_operacao' => $request->natureza_operacao,
					'documento' => $request->documento,
					'razao_social' => $request->razao_social,
					'nome_fantasia' => $request->nome_fantasia ?? '',
					'im' => $request->im ?? '',
					'ie' => $request->ie ?? '',
					'cep' => $request->cep ?? '',
					'rua' => $request->rua,
					'numero' => $request->numero,
					'bairro' => $request->bairro,
					'complemento' => $request->complemento ?? '',
					'cidade_id' => $request->cidade_id,
					'email' => $request->email ?? '',
					'telefone' => $request->telefone ?? ''
				]);

				NfseServico::create([
					'nfse_id' => $nfse->id,
					'discriminacao' => $request->discriminacao,
					'valor_servico' => str_replace(',', '.', $request->valor_servico),
					'servico_id' => $request->servico_id,
					'codigo_cnae' => $request->codigo_cnae ?? '',
					'codigo_servico' => $request->codigo_servico ?? '',
					'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
					'exigibilidade_iss' => $request->exigibilidade_iss,
					'iss_retido' => $request->iss_retido,
					'data_competencia' => $request->data_competencia ?? null,
					'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
					'cidade_local_prestacao_servico' => $request->cidade_local_prestacao_servico ?? '',
					'valor_deducoes' => $request->valor_deducoes ? str_replace(',', '.', $request->valor_deducoes) : 0,
					'desconto_incondicional' => $request->desconto_incondicional ? str_replace(',', '.', $request->desconto_incondicional) : 0,
					'desconto_condicional' => $request->desconto_condicional ? str_replace(',', '.', $request->desconto_condicional) : 0,
					'outras_retencoes' => $request->outras_retencoes ? str_replace(',', '.', $request->outras_retencoes) : 0,
					'aliquota_pis' => $request->aliquota_pis ? str_replace(',', '.', $request->aliquota_pis) : 0,
					'aliquota_cofins' => $request->aliquota_cofins ? str_replace(',', '.', $request->aliquota_cofins) : 0,
					'aliquota_inss' => $request->aliquota_inss ? str_replace(',', '.', $request->aliquota_inss) : 0,
					'aliquota_ir' => $request->aliquota_ir ? str_replace(',', '.', $request->aliquota_ir) : 0,
					'aliquota_csll' => $request->aliquota_csll ? str_replace(',', '.', $request->aliquota_csll) : 0,
					'intermediador' => $request->intermediador ?? 'n',
					'documento_intermediador' => $request->documento_intermediador ?? '',
					'nome_intermediador' => $request->nome_intermediador ?? '',
					'im_intermediador' => $request->im_intermediador ?? '',
					'responsavel_retencao_iss' => $request->responsavel_retencao_iss ?? 1,

				]);

				if (isset($request->os_id)) {
					$ordem = OrdemServico::findOrFail($request->os_id);
					$ordem->nfse_id = $nfse->id;
					$ordem->save();
				}
				return $nfse;
			});
			return response()->json($result, 200);
		} catch (\Exception $e) {
			// echo $e->getMessage();
			// die;
			return response()->json($e->getMessage(), 403);
		}
	}

	private function _validate(Request $request)
	{
		$rules = [
			'cliente' => 'required',
			'natureza_operacao' => 'required',
			'razao_social' => 'required|max:80',
			'documento' => ['required'],
			'rua' => 'required|max:80',
			'numero' => 'required|max:10',
			'bairro' => 'required|max:50',
			'telefone' => 'max:20',
			'celular' => 'max:20',
			'email' => 'max:40',
			'cep' => 'required',
			'cidade_id' => 'required',
			'discriminacao' => 'required',
			'valor_servico' => 'required',
			'codigo_servico' => 'required',
		];

		$messages = [
			'cliente.required' => 'Selecione',
			'razao_social.required' => 'O campo Razão social é obrigatório.',
			'natureza_operacao.required' => 'O campo Natureza de Operação é obrigatório.',
			'razao_social.max' => '100 caracteres maximos permitidos.',
			'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
			'nome_fantasia.max' => '80 caracteres maximos permitidos.',
			'documento.required' => 'O campo CPF/CNPJ é obrigatório.',
			'rua.required' => 'O campo Rua é obrigatório.',
			'ie_rg.max' => '20 caracteres maximos permitidos.',
			'rua.max' => '80 caracteres maximos permitidos.',
			'numero.required' => 'O campo Numero é obrigatório.',
			'cep.required' => 'O campo CEP é obrigatório.',
			'cidade_id.required' => 'O campo Cidade é obrigatório.',
			'numero.max' => '10 caracteres maximos permitidos.',
			'bairro.required' => 'O campo Bairro é obrigatório.',
			'bairro.max' => '50 caracteres maximos permitidos.',
			'telefone.required' => 'O campo Celular é obrigatório.',
			'telefone.max' => '20 caracteres maximos permitidos.',
			'celular.required' => 'O campo Celular 2 é obrigatório.',
			'celular.max' => '20 caracteres maximos permitidos.',

			'email.required' => 'O campo Email é obrigatório.',
			'email.max' => '40 caracteres maximos permitidos.',
			'email.email' => 'Email inválido.',
			'discriminacao.required' => 'Campo obrigatório.',
			'valor_servico.required' => 'Campo obrigatório.',
			'codigo_servico.required' => 'Campo obrigatório.',


		];
		$this->validate($request, $rules, $messages);
	}

	public function teste()
	{
		$business_id = request()->session()->get('user.business_id');
		$config = Business::where('id', $business_id)
			->first();

		$token = $config->token_nfse;
		Connection::getInstance()->setBearerToken($token);

		$nfse = new NFSeWeb();
		$nfse->Servico->valorServico = 243;
		$nfse->Servico->discriminacao = "Instlacao eletrica";
		$nfse->Servico->codigoServico = "0702";
		$nfse->Servico->naturezaOperacao = "1";
		$nfse->Servico->issRetido = 0;
		$nfse->Servico->exigibilidadeIss = 1;
		// $nfse->Servico->tipoTributacao = 1;
		$nfse->Servico->Impostos->iss = 2;
		// $nfse->Tomador->razaoSocial = "Marcos Bueno";
		$nfse->Tomador->nomeCompleto = "Marcos Bueno";
		$nfse->Tomador->cpf = "09520985980";
		$nfse->Tomador->cep = "84200000";
		$nfse->Tomador->endereco = "Aldo Ribas";
		$nfse->Tomador->numero = "190";
		$nfse->Tomador->complemento = "Casa";
		$nfse->Tomador->bairro = "Cidade Alta";
		$nfse->Tomador->cidade = "Jaguariaiva";
		$nfse->Tomador->uf = "PR";

		try {
			$response = $nfse->emitirHomologacao();
			// dd($response);
			// die;
			$object = json_decode($response->getMessage());
			if (isset($object->status)) {
				if ($object->status == 'reprovado') {
					echo "erro";
				}
				dd($object);
			} else {
				dd($response->getMessage());
			}
		} catch (\Throwable $th) {
			die;
			dd((object) ['exception' => $th->getMessage()]);
		} catch (APIException $a) {
			die;
			dd((object) ['error' => $a->getMessage()]);
		}
	}

	public function enviar(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $empresa = Business::where('id', $business_id)->first();
        $token = NfseConfig::where('empresa_id', $business_id)->first();


        $item = Nfse::findOrFail($request->id);
        if ($item->estado === 'aprovado') return response()->json('Este documento esta aprovado', 401);
        if ($item->estado === 'cancelado') return response()->json('Este documento esta cancelado', 401);

        if (!is_dir(public_path('nfse_doc'))) @mkdir(public_path('nfse_doc'), 0777, true);
        if (!is_dir(public_path('nfse_pdf'))) @mkdir(public_path('nfse_pdf'), 0777, true);

        $ambiente = ((int)($empresa->ambiente));
        $nfse = new NfseSdk([
            'token' => trim((string) $token->token),
            'ambiente' => $ambiente,
            'options' => ['debug' => false, 'timeout' => 60, 'port' => 443, 'http_version' => CURL_HTTP_VERSION_NONE],
        ]);

        $servico = $item->servico;

		try {
			// Helpers
			$format2 = function ($v) { return number_format((float)$v, 2, '.', ''); };
			$format4 = function ($v) { return number_format((float)$v, 4, '.', ''); };

			// IBGE emitente
			$codigoMunicipioEmitente = null;
			if (!empty($token->cidade_id)) {
				$city = City::find($token->cidade_id);
				$codigoMunicipioEmitente = $city ? (string)$city->codigo : null;
			}

			// Tomador docs
			$doc = preg_replace('/[^0-9]/', '', (string)$item->documento);
			$im = preg_replace('/[^0-9]/', '', (string)$item->im);
			$ie = preg_replace('/[^0-9]/', '', (string)$item->ie);
			$isCpfTomador = strlen($doc) === 11;

			// Competência YYYY-MM
			$competencia = date('Y-m');

			// Simples Nacional
			$optanteSimples = ((int)($empresa->regime ?? 1)) === 1;
			$incentivoFiscal = false;
			$outrasInfo = '';

			// Cálculos
			$valorServicos = (float)$servico->valor_servico;
			$valorDeducoes = (float)($servico->valor_deducoes ?? 0);
			$baseCalculo = max($valorServicos - $valorDeducoes, 0);
			$aliquotaIssPercent = (float)($servico->aliquota_iss ?? 0);
			$aliquotaIssqnFrac = $aliquotaIssPercent / 100; // ex: 2 => 0.02
			$valorIss = $baseCalculo * $aliquotaIssqnFrac;
			$valorPis = (float)($servico->valor_pis ?? 0);
			$valorCofins = (float)($servico->valor_cofins ?? 0);
			$valorInss = (float)($servico->valor_inss ?? 0);
			$valorIr = (float)($servico->valor_ir ?? 0);
			$valorCsll = (float)($servico->valor_csll ?? 0);
			$outrasRetencoes = (float)($servico->outras_retencoes ?? 0);
			$descontoIncond = (float)($servico->desconto_incondicional ?? 0);
			$descontoCond = (float)($servico->desconto_condicional ?? 0);
			$valorLiquidoNfse = $baseCalculo - $valorPis - $valorCofins - $valorInss - $valorIr - $valorCsll - $outrasRetencoes - $descontoIncond - $descontoCond;

			// Itens
			$itemListaServico = (string)$servico->codigo_servico;
			$quantidadeItem = 1;
			$valorUnitarioItem = $valorServicos;

			// Numeração
			$numero = (int)($empresa->numero_rps ?? 0) + 1;
			$numeroSerie = (int)($empresa->numero_serie_nfse ?? 1);

			// Prestador (Business)
			$cnpjPrest = preg_replace('/[^0-9]/', '', (string)$empresa->cnpj);
			$imPrest = (string)($token->im ?? '');
			$razaoPrest = (string)($token->razao_social ?? $empresa->name ?? '');
			$fantasiaPrest = (string)($token->nome ?? $empresa->name ?? '');
			$telefonePrest = (string)($token->telefone ?? '');
			$emailPrest = (string)($token->email ?? '');
			$cepPrest = preg_replace('/[^0-9]/', '', (string)($token->cep ?? ''));
			$logradouroPrest = (string)($token->rua ?? '');
			$numeroPrest = (string)($token->numero ?? '');
			$complPrest = (string)($token->complemento ?? '');
			$bairroPrest = (string)($token->bairro ?? '');
			$codigoCnaePrest = (string)($empresa->cnae ?? ($servico->codigo_cnae ?? ''));
			$tokenPrestador = trim((string)$token->token);
			$codigoAleatorio = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

			$payload = [
				'numero' => (string)$numero,
				'serie' => (string)$numeroSerie,
				'tipo' => '1',
				'data_emissao' => date('Y-m-d\TH:i:sP'),
				'competencia' => $competencia,
				'natureza_operacao' => (string)($item->natureza_operacao ?? '1'),
				'optante_simples_nacional' => $optanteSimples,
				'incentivo_fiscal' => $incentivoFiscal,
				'status' => '1',
				'outras_informacoes' => $outrasInfo,
				'valores_nfse' => [
					'base_calculo' => $format2($baseCalculo),
					'valor_liquido_nfse' => $format2($valorLiquidoNfse),
				],
				'servico' => [
					'valor_servicos' => $format2($valorServicos),
					'valores' => [
						'valor_deducoes' => $format2($valorDeducoes),
						'valor_pis' => $format2($valorPis),
						'valor_cofins' => $format2($valorCofins),
						'valor_inss' => $format2($valorInss),
						'valor_ir' => $format2($valorIr),
						'valor_csll' => $format2($valorCsll),
						'outras_retencoes' => $format2($outrasRetencoes),
						'valor_iss' => $format2($valorIss),
						'aliquota' => $format2($aliquotaIssPercent),
						'desconto_incondicionado' => $format2($descontoIncond),
						'desconto_condicionado' => $format2($descontoCond),
					],
					'iss_retido' => ((int)($servico->iss_retido ?? 0)) === 1,
					'item_lista_servico' => $itemListaServico,
					'codigo_municipio' => (string)$codigoMunicipioEmitente,
					'municipio_incidencia' => (string)$codigoMunicipioEmitente,
					'exigibilidade_iss' => (string)($servico->exigibilidade_iss),
					'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),
					'aliquota_issqn' => $format4($aliquotaIssqnFrac),
					'itens' => [[
						'codigo' => $itemListaServico,
						'codigo_cnae' => (string)($servico->codigo_cnae ?? ''),
						'codigo_tributacao_municipio' => (string)($servico->codigo_tributacao_municipio ?? ''),
						'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),
						'quantidade' => (string)$quantidadeItem,
						'valor_unitario' => $format2($valorUnitarioItem),
						'valor_servicos' => (float)$valorServicos,
					]],
				],
				'prestador' => [
					'cnpj' => $cnpjPrest,
					'inscricao_municipal' => $imPrest,
					'razao_social' => $this->retiraAcentos($razaoPrest),
					'nome_fantasia' => $this->retiraAcentos($fantasiaPrest),
					'codigo_cnae' => $codigoCnaePrest,
					'endereco' => [
						'logradouro' => $this->retiraAcentos($logradouroPrest),
						'numero' => $this->retiraAcentos($numeroPrest),
						'complemento' => $this->retiraAcentos($complPrest),
						'bairro' => $this->retiraAcentos($bairroPrest),
						'codigo_municipio' => (string)$codigoMunicipioEmitente,
						'cep' => $cepPrest,
					],
					'contato' => [
						'telefone' => $telefonePrest,
						'email' => $emailPrest,
					],
					'token' => $tokenPrestador,
				],
				'tomador' => [
					'identificacao_tomador' => $isCpfTomador ? ['cpf' => $doc] : ['cnpj' => $doc],
					($isCpfTomador ? 'cpf' : 'cnpj') => $doc,
					'razao_social' => $this->retiraAcentos((string)$item->razao_social),
					'endereco' => [
						'logradouro' => $this->retiraAcentos((string)$item->rua),
						'numero' => $this->retiraAcentos((string)$item->numero),
						'complemento' => $this->retiraAcentos((string)($item->complemento ?? '')),
						'bairro' => $this->retiraAcentos((string)$item->bairro),
						'codigo_municipio' => (string)($item->cidade->codigo ?? ''),
						'uf' => (string)($item->cidade->uf ?? ''),
						'cep' => preg_replace('/[^0-9]/', '', (string)$item->cep),
					],
					'contato' => [
						'telefone' => (string)($item->telefone ?? ''),
						'email' => (string)($item->email ?? ''),
					],
				],
				'orgao_gerador' => [
					'codigo_municipio' => (string)$codigoMunicipioEmitente,
				],
				'nacional' => true,
				'codigo_aleatorio' => $codigoAleatorio,
				'token_prestador' => $tokenPrestador,
			];

            // dd($payload);

            $resp = $nfse->cria($payload);

            if (!empty($resp->sucesso)) {
                if (isset($resp->chave)) {
                    $item->chave = $resp->chave;
                    $item->save();
                }

                sleep(10); // muitos provedores processam em background

                $consulta = $nfse->consulta(['chave' => $resp->chave]);

                if (($consulta->codigo ?? null) != 5023) {
                    // dd($consulta);
                    if (!empty($consulta->sucesso)) {
                        $item->estado = 'aprovado';
                        $item->url_pdf_nfse = $consulta->link_pdf ?? '';
                        $item->numero_nfse = $consulta->numero ?? 0;
                        $item->serie = $consulta->serie ?? '';
                        $item->codigo_verificacao = $consulta->codigo_verificacao ?? '';
                        $item->save();

                        if (isset($empresa->ultimo_numero_nfse) && !empty($consulta->numero)) {
                            // dd($consulta);
                            $empresa->ultimo_numero_nfse = (int)$consulta->numero;
                            $empresa->numero_rps = (int)$consulta->rps_numero;
                            $empresa->save();
                        }
                        if (!empty($consulta->xml)) {
                            $xml = base64_decode($consulta->xml);
                            @file_put_contents(public_path('nfse_doc/') . $resp->chave . '.xml', $xml);
                        }
                        if (!empty($consulta->pdf)) {
                            $pdf = base64_decode($consulta->pdf);
                            @file_put_contents(public_path('nfse_pdf/') . $resp->chave . '.pdf', $pdf);
                        }
                        return response()->json($consulta, 200);
                    }

                    $item->estado = 'rejeitado';
                    $item->save();
                    return response()->json($consulta, 422);
                }

                $item->estado = 'processando';
                $item->save();
                return response()->json($consulta, 202);
            }

            $item->estado = 'rejeitado';
            $item->save();
            return response()->json($resp, 422);
        } catch (\Throwable $e) {
            return response()->json(['sucesso' => false, 'mensagem' => $e->getMessage(), 'linha' => $e->getLine()], 500);
        }
    }

	private function retiraAcentos($texto)
	{
		return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(&)/"), explode(" ", "a A e E i I o O u U n N c e"), $texto);
	}

	public function baixarXml($id)
	{
		$item = Nfse::findOrFail($id);
		if (($item)) {
			if (file_exists(public_path('nfse_doc/') . "$item->uuid.xml")) {
				return response()->download(public_path('nfse_doc/') . "$item->uuid.xml");
			} elseif (file_exists(public_path('nfse_doc/') . "$item->chave.xml")) {
				return response()->download(public_path('nfse_doc/') . "$item->chave.xml");
			} else {
				echo "Arquivo XML não encontrado!!";
			}
		} else {
			return redirect('/403');
		}
	}

	public function imprimir($id)
	{
		$item = Nfse::findOrFail($id);
		if (($item)) {
			// if ($item->url_pdf_nfse) {
			// 	return redirect($item->url_pdf_nfse);
			// } else {
			if (file_exists(public_path('nfse_pdf/') . $item->chave . ".pdf")) {
				$pdf = file_get_contents(public_path('nfse_pdf/') . $item->chave . ".pdf");
				return response($pdf)
					->header('Content-Type', 'application/pdf');
			}

			if ($item->url_pdf_rps) {
				return redirect($item->url_pdf_rps);
			}
			// }
		} else {
			return redirect('/403');
		}
	}

	public function consultar(Request $request)
	{
		$business_id = request()->session()->get('user.business_id');
		$config = Business::where('id', $business_id)
			->first();

		Connection::getInstance()->setBearerToken($config->token_nfse);
		$item = Nfse::findOrFail($request->id);
		$nfse = new NFSe();

		$nfse->uuid = $item->uuid;
		try {
			$response = $nfse->consultar();
			$object = json_decode($response->getMessage());
			// return response()->json($object, 401);

			if (isset($object->info_nfse)) {
				$object = $object->info_nfse[0];
			}

			if (isset($object->codigo_verificacao)) {
				$item->codigo_verificacao = $object->codigo_verificacao;
				if (isset($object->pdf_nfse)) {
					$item->url_pdf_nfse = $object->pdf_nfse;
				}
				$item->url_pdf_rps = $object->pdf_rps;
				$item->url_xml = $object->xml;
				$item->numero_nfse = $object->numero;
				$item->uuid = $object->uuid;
				$item->estado = 'aprovado';
				$item->save();
				$xml = file_get_contents($item->url_xml);
				file_put_contents(public_path('nfse_doc/') . "$item->uuid.xml", $xml);
			}

			if ($object->status == "reprovado") {
				$item->estado = 'rejeitado';
				$item->save();

				return response()->json($object->motivo[0], 401);
			}

			if ($object->status == "cancelado") {
				$item->estado = 'cancelado';
				$item->save();
			}

			return response()->json($response->getMessage(), 200);
		} catch (\Throwable $th) {
			// response((object) [ 'exception' => $th->getMessage() ]);
			return response()->json($th->getMessage(), 401);
		} catch (APIException $a) {
			// response((object) [ 'error' => $a->getMessage() ]);
			return response()->json($a->getMessage(), 401);
		}
	}

	public function cancelar(Request $request)
	{
		// Criar pastas se não existirem
		if (!is_dir(public_path('nfse_cancelada_doc'))) {
			@mkdir(public_path('nfse_cancelada_doc'), 0777, true);
		}
		if (!is_dir(public_path('nfse_cancelada_xml'))) {
			@mkdir(public_path('nfse_cancelada_xml'), 0777, true);
		}

		$business_id = request()->session()->get('user.business_id');
		$token = NfseConfig::where('empresa_id', $business_id)->first();

		$empresa = Business::where('id', $business_id)->first();
		$ambiente = ((int)($empresa->ambiente));

		$nfse = new NfseSdk([
			'token' => trim((string)$token->token),
			'ambiente' => ((int)$ambiente),
			'options' => ['debug' => false, 'timeout' => 60, 'port' => 443, 'http_version' => CURL_HTTP_VERSION_NONE],
		]);

		$item = Nfse::findOrFail($request->id);

		// dd($item->chave);
		// dd($request->all());
		$resp = $nfse->cancela([
			'chave' => $item->chave,
			'justificativa' => $request->justificativa ?? $request->motivo,
			'codigo_cancelamento' => $request->codigo_cancelamento ?? '2',
		]);

		// Log para debug
		Log::info('=== RESPOSTA CANCELAMENTO NFSe (Integra Notas) ===', [
			'nfse_id' => $request->id,
			'chave' => $item->chave,
			'resposta' => $resp
		]);

		if (!empty($resp->sucesso) && $resp->sucesso === true) {
			// Atualizar banco
			$item->estado = 'cancelado';
			$item->cancelado_em = now();
			$item->save();

			// Salvar PDF do cancelamento
			if (!empty($resp->pdf)) {
				$pdf = base64_decode($resp->pdf);
				@file_put_contents(public_path('nfse_cancelada_doc/') . $item->chave . '.pdf', $pdf);

				Log::info('PDF de cancelamento salvo', [
					'arquivo' => 'nfse_cancelada_doc/' . $item->chave . '.pdf'
				]);
			}

			// Salvar XML do cancelamento
			if (!empty($resp->xml)) {
				$xml = base64_decode($resp->xml);
				@file_put_contents(public_path('nfse_cancelada_xml/') . $item->chave . '.xml', $xml);

				Log::info('XML de cancelamento salvo', [
					'arquivo' => 'nfse_cancelada_xml/' . $item->chave . '.xml'
				]);
			}

			Log::info('NFSe cancelada com sucesso (Integra Notas)', [
				'id' => $item->id,
				'chave' => $item->chave
			]);

			return response()->json($resp, 200);
		}

		// Se chegou aqui, não foi sucesso
		return response()->json($resp, 422);
	}

	public function enviarXml(Request $request)
	{
		$email = $request->email;
		$id = $request->id;
		$item = Nfse::findOrFail($id);
		if (($item)) {
			$value = session('user_logged');
			Mail::send('mail.xml_send_nfse', ['nfse' => $item, 'usuario' => $value['nome']], function ($m) use ($item, $email) {
				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				$nomeEmpresa = env('MAIL_NAME');
				$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
				$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
				$emailEnvio = env('MAIL_USERNAME');

				$m->from($emailEnvio, $nomeEmpresa);
				$m->subject('Envio de XML NFse ' . $item->nuero_emissao);
				$m->attach($public . 'nfse_doc/' . $item->uuid . '.xml');
				$m->to($email);
			});
			return "ok";
		} else {
			return redirect('/403');
		}
	}

	public function imprimirCancelamento($id)
	{
		$nota = Nfse::findOrFail($id);
		if (!$nota->cancelamento_pdf_path || !file_exists(public_path($nota->cancelamento_pdf_path))) {
			abort(404, 'PDF de cancelamento não encontrado');
		}
		$fullPath = public_path($nota->cancelamento_pdf_path);
		return response()->file($fullPath); // abre no navegador
		// ou ->download($fullPath, "cancelamento_nfse_{$nota->numero}.pdf");
	}

	private function prepareUFs()
	{
		return [
			"AC" => "AC",
			"AL" => "AL",
			"AM" => "AM",
			"AP" => "AP",
			"BA" => "BA",
			"CE" => "CE",
			"DF" => "DF",
			"ES" => "ES",
			"GO" => "GO",
			"MA" => "MA",
			"MG" => "MG",
			"MS" => "MS",
			"MT" => "MT",
			"PA" => "PA",
			"PB" => "PB",
			"PE" => "PE",
			"PI" => "PI",
			"PR" => "PR",
			"RJ" => "RJ",
			"RN" => "RN",
			"RS" => "RS",
			"RO" => "RO",
			"RR" => "RR",
			"SC" => "SC",
			"SE" => "SE",
			"SP" => "SP",
			"TO" => "TO"

		];
	}

	private function prepareCities()
	{
		$cities = City::all();
		$temp = [];
		foreach ($cities as $c) {
			// array_push($temp, $c->id => $c->nome);
			$temp[$c->id] = $c->nome . " ($c->uf)";
		}
		return $temp;
	}

	public function previewXml($id)
	{
		if (!is_dir(public_path('nfse_temp'))) {
			mkdir(public_path('nfse_temp'), 0777, true);
		}
		$item = Nfse::findOrFail($id);

		$business_id = request()->session()->get('user.business_id');
		$empresa = Business::where('id', $business_id)->first();

		// $config = ConfigNota::where('empresa_id', $item->empresa_id)
		//     ->first();

		$token = NfseConfig::where('empresa_id', $business_id)->first();

		$ambiente = ((int)($empresa->ambiente));

		$params = [
			'token' => $token->token,
			'ambiente' => $ambiente,
			// 'ambiente' => $config->ambiente == 2 ? Nfse::AMBIENTE_HOMOLOGACAO : Nfse::AMBIENTE_PRODUCAO,
			'options' => [
				'debug' => false,
				'timeout' => 60,
				'port' => 443,
				'http_version' => CURL_HTTP_VERSION_NONE
			]
		];
		$nfse = new NfseSdk($params);
		$servico = $item->servico;

		try {
			$codigoMunicipioEmitente = null;
			if (!empty($empresa->cidade_id)) {
				$city = City::find($token->cidade_id);
				$codigoMunicipioEmitente = $city ? $city->codigo : null;
			}

			$doc = preg_replace('/[^0-9]/', '', $item->documento);
			$im = preg_replace('/[^0-9]/', '', $item->im);
			$ie = preg_replace('/[^0-9]/', '', $item->ie);

			$numeroSerie = (int)($empresa->numero_serie_nfse ?? 1);
			$proximoNumero = (int)($empresa->ultimo_numero_nfse ?? 0) + 1;

			// Calcular valor líquido final (igual à fórmula da view)
			$valorServico = (float)$servico->valor_servico;
			$valorDeducoes = (float)($servico->valor_deducoes ?? 0);

			// Base de cálculo = valor_servico - deduções
			$base = max($valorServico - $valorDeducoes, 0);

			// Impostos calculados sobre a base
			$aliqPIS = (float)($servico->aliquota_pis ?? 0);
			$aliqCOFINS = (float)($servico->aliquota_cofins ?? 0);
			$aliqINSS = (float)($servico->aliquota_inss ?? 0);
			$aliqIR = (float)($servico->aliquota_ir ?? 0);
			$aliqCSLL = (float)($servico->aliquota_csll ?? 0);
			$aliqISS = (float)($servico->aliquota_iss ?? 0);
			$aliqISSQN = (float)($servico->aliquota_issqn ?? 0);

			$pis = $base * ($aliqPIS / 100);
			$cofins = $base * ($aliqCOFINS / 100);
			$inss = $base * ($aliqINSS / 100);
			$ir = $base * ($aliqIR / 100);
			$csll = $base * ($aliqCSLL / 100);
			$issRetido = ($servico->iss_retido == 1) ? $base * ($aliqISS / 100) : 0;
			$issqn = $base * ($aliqISSQN / 100);

			// Descontos e outras retenções
			$descIncond = (float)($servico->desconto_incondicional ?? 0);
			$descCond = (float)($servico->desconto_condicional ?? 0);
			$outrasRet = (float)($servico->outras_retencoes ?? 0);

			// VALOR LÍQUIDO FINAL (igual ao JavaScript da view)
			$valorLiquido = $base - ($pis + $cofins + $inss + $ir + $csll + $issRetido + $issqn) - $outrasRet - $descIncond - $descCond;
			$valorLiquido = max($valorLiquido, 0);

			$payload = [
				'numero' => $proximoNumero,
				'serie' => $numeroSerie,
				'tipo' => '1',
				'status' => '1',
				'data_emissao' => date('Y-m-d\TH:i:sP'),
				'data_competencia' => date('Y-m-d\TH:i:sP'),
				'regime_tributacao' => '6',
				'tomador' => [
					'cnpj' => strlen($doc) === 14 ? $doc : null,
					'cpf'  => strlen($doc) === 11 ? $doc : null,
					'im' => $im ?: null,
					'ie' => $ie ?: null,
					'razao_social' => $item->razao_social,
					'nome_fantasia' => $item->nome_fantasia,
					'email' => $item->email,
					'endereco' => [
						'logradouro' => $this->retiraAcentos($item->rua),
						'numero' => $this->retiraAcentos($item->numero),
						'complemento' => $this->retiraAcentos($item->complemento),
						'bairro' => $this->retiraAcentos($item->bairro),
						'codigo_municipio' => $item->cidade->codigo,
						'uf' => $item->cidade->uf,
						'nome_municipio' => $item->cidade->nome,
						'cep' => preg_replace('/[^0-9]/', '', $item->cep),
					],
				],
				'servico' => [
					'codigo_tributacao_municipio' => $servico->codigo_tributacao_municipio,
					'discriminacao' => $this->retiraAcentos($servico->discriminacao),
					'codigo_municipio' => $codigoMunicipioEmitente,
					'valor_servicos' => $valorServico,
					'unidade_valor' => $valorServico,
					'valor_liquido' => $valorLiquido,
					'valor_deducoes' => $valorDeducoes > 0 ? $valorDeducoes : null,
					'desconto_incondicionado' => $descIncond > 0 ? $descIncond : null,
					'desconto_condicionado' => $descCond > 0 ? $descCond : null,
					'outras_retencoes' => $outrasRet > 0 ? $outrasRet : null,
					'valor_pis' => $pis > 0 ? $pis : null,
					'valor_cofins' => $cofins > 0 ? $cofins : null,
					'valor_inss' => $inss > 0 ? $inss : null,
					'valor_ir' => $ir > 0 ? $ir : null,
					'valor_csll' => $csll > 0 ? $csll : null,
					'valor_aliquota' => (float)($servico->valor_aliquota ?? 0),
					'codigo_cnae' => $servico->codigo_cnae,
					'codigo' => $servico->codigo_servico,
					'itens' => [[
						'codigo' => $servico->codigo_servico,
						'codigo_tributacao_municipio' => $servico->codigo_tributacao_municipio,
						'discriminacao' => $this->retiraAcentos($servico->discriminacao),
						'codigo_municipio' => $codigoMunicipioEmitente,
						'valor_servicos' => $valorServico,
						'unidade_valor' => $valorServico,
						'valor_liquido' => $valorLiquido,
						'valor_aliquota' => (float)($servico->valor_aliquota ?? 0),
						'codigo_cnae' => $servico->codigo_cnae,
					]],
				],
			];

			// Log para debug
			Log::info('=== PAYLOAD PREVIEW NFSe ===', [
				'nfse_id' => $id,
				'valor_servico' => $valorServico,
				'valor_deducoes' => $valorDeducoes,
				'desconto_incondicionado' => $descIncond,
				'desconto_condicionado' => $descCond,
				'outras_retencoes' => $outrasRet,
				'valor_liquido' => $valorLiquido,
				'payload_completo' => $payload
			]);

			// return response()->json($payload, 404);
			$rute = "nfse_temp/temp.pdf";
			$resp = $nfse->preview($payload);

			if (isset($resp->pdf)) {
				$pdf_b64 = base64_decode($resp->pdf);

				if (file_put_contents($rute, $pdf_b64)) {
					header("Content-type: application/pdf");
					echo $pdf_b64;
				}
			} else {
				dd($resp);
			}
		} catch (\Exception $e) {
			$output = [
				'success' => 0,
				'msg' => 'Algo deu errado: ' . $e->getMessage()
			];
			return redirect()->route('nfse.index')->with('status', $output);
		}
	}
}
