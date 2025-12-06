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
use CloudDfe\SdkPHP\Nfse as NfseSdk;



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

		$nfseNumeros = $nfses->pluck('numero_nfse', 'id')->toArray();

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
		return view('nfse.index', compact('nfses', 'certificado', 'config', 'estado', 'total', 'nfseNumeros'))
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

		$nfseNumeros = $nfses->pluck('numero_nfse', 'id')->toArray();

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


		return view('nfse.index', compact('nfses', 'certificado', 'config', 'nfseNumeros'))
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
					'cidade_local_prestacao_servico_id' => $request->cidade_local_prestacao_servico_id ?? '',
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
					'aliquota_issqn' => $request->aliquota_issqn ? str_replace(',', '.', $request->aliquota_issqn) : 0,
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
					'cidade_local_prestacao_servico_id' => $request->cidade_local_prestacao_servico_id ?? '',
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
					'cidade_local_prestacao_servico_id' => $request->cidade_local_prestacao_servico_id ?? '',
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
			$format2 = function ($v) {
				return number_format((float)$v, 2, '.', '');
			};
			$format4 = function ($v) {
				return number_format((float)$v, 4, '.', '');
			};

			// ============================
			// IBGE emitente
			// ============================
			$codigoMunicipioEmitente = null;
			if (!empty($token->cidade_id)) {
				$city = City::find($token->cidade_id);
				$codigoMunicipioEmitente = $city ? (string)$city->codigo : null;
			}

			// Regime no BD: 'simples' ou 'normal'
			$regime = strtolower(trim((string)($token->regime ?? 'simples')));
			$optanteSimples = $regime === 'simples';

			if ($regime === 'simples') {
				$optanteSimples = true;
			} else {
				$optanteSimples = false;
			}

			$isMei = $regime === 'mei';

			// issRetido = 2 - não
			// issRetido = 1 - sim
			$issRetido = ((int)($servico->iss_retido ?? 2)) === 1;
			// ============================
			// MUNICÍPIO DA PRESTAÇÃO (local onde fez o serviço)
			// ============================
			$codigoMunicipioPrestacao = null; // <<< NOVO (nome mais claro)
			if (!empty($servico->cidade_local_prestacao_servico_id)) {
				$city = City::find($servico->cidade_local_prestacao_servico_id);
				$codigoMunicipioPrestacao = $city ? (string)$city->codigo : null;
			}

			// Se não tiver cidade de prestação definida, podemos usar a cidade do tomador
			$codigoMunicipioTomador = null; // <<< NOVO
			if (!empty($item->cidade_id) && $item->cidade) {
				$codigoMunicipioTomador = (string)($item->cidade->codigo ?? '');
			}

			// ============================
			// MUNICÍPIO DE INCIDÊNCIA (quem recolhe o ISS)
			// ============================
			$itemListaServico = (string)$servico->codigo_servico;
			$codigoMunicipioIncidencia = $this->definirMunicipioIncidencia( // <<< NOVO
				$itemListaServico,
				$codigoMunicipioEmitente,
				$codigoMunicipioPrestacao,
				$codigoMunicipioTomador
			);

			// ISS devido a outro município: compara INCIDÊNCIA x LOCAL DA PRESTAÇÃO
			// $issDevidoOutroMunicipio =
			// 	$codigoMunicipioIncidencia
			// 	&& $codigoMunicipioPrestacao
			// 	&& $codigoMunicipioIncidencia !== $codigoMunicipioPrestacao; // <<< AJUSTE

			// Quando ISS é devido a outro município, ou Simples + ISS retido,
			// a prefeitura exige que a alíquota seja informada.
			// $deveInformarAliquotaISS = ($issDevidoOutroMunicipio || ($optanteSimples && $issRetido)); // <<< AJUSTE

			// $isRegimeFixo = in_array($regime, ['fixo', 'fixo_mensal', 'fixo_iss'], true);

			// Por padrão, alíquota é obrigatória
			// $deveInformarAliquotaISS = true;

			// Exceções onde pode ser 0%
			// if ($isMei) {
			// 	$deveInformarAliquotaISS = false;
			// } elseif ($codigoMunicipioIncidencia === $codigoMunicipioEmitente) {
			// 	$deveInformarAliquotaISS = false;
			// }

			// Tomador docs
			$doc = preg_replace('/[^0-9]/', '', (string)$item->documento);
			$im = preg_replace('/[^0-9]/', '', (string)$item->im);
			$ie = preg_replace('/[^0-9]/', '', (string)$item->ie);
			$isCpfTomador = strlen($doc) === 11;

			// Competência YYYY-MM
			$competencia = $servico->data_competencia
				? \Carbon\Carbon::parse($servico->data_competencia)->format('Y-m-d\TH:i:sP')
				: \Carbon\Carbon::now()->format('Y-m-d\TH:i:sP');

			// Simples Nacional
			$incentivoFiscal = false;
			$outrasInfo = '';

			// ============================
			// CÁLCULOS DE VALORES
			// ============================
			$valorServicos = (float)$servico->valor_servico;
			$valorDeducoes = (float)($servico->valor_deducoes ?? 0);
			$baseCalculo = max($valorServicos - $valorDeducoes, 0);

			// aliquota_iss = percentual (ex: 3.44)
			// aliquota_issqn = fração (ex: 0.0344)
			$aliquotaIssPercent = (float)($servico->aliquota_iss ?? 0);

			$aliquotaIssqnFrac = $aliquotaIssPercent > 0 ? $aliquotaIssPercent / 100.0 : 0.0;

			// $aliquotaIssqnFrac = (float)($servico->aliquota_issqn ?? 0);

			// Se não precisa informar alíquota ISS, zera a fração para não gerar valor_iss
			// if (!$deveInformarAliquotaISS) { // <<< NOVO
			// 	$aliquotaIssPercent = 0;
			// 	$aliquotaIssqnFrac = 0;
			// }

			$valorIss = $baseCalculo * $aliquotaIssqnFrac;

			$valorPis = (float)($servico->valor_pis ?? 0);
			$valorCofins = (float)($servico->valor_cofins ?? 0);
			$valorInss = (float)($servico->valor_inss ?? 0);
			$valorIr = (float)($servico->valor_ir ?? 0);
			$valorCsll = (float)($servico->valor_csll ?? 0);
			$outrasRetencoes = (float)($servico->outras_retencoes ?? 0);
			$descontoIncond = (float)($servico->desconto_incondicional ?? 0);
			$descontoCond = (float)($servico->desconto_condicional ?? 0);

			$valorLiquidoNfse = $baseCalculo
				- $valorPis
				- $valorCofins
				- $valorInss
				- $valorIr
				- $valorCsll
				- $outrasRetencoes
				- $descontoIncond
				- $descontoCond;

			// Itens
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

			// ============================
			// BLOC VALORES SERVIÇO
			// ============================
			$valoresServico = [
				'valor_deducoes'           => $format2($valorDeducoes),
				'valor_pis'                => $format2($valorPis),
				'valor_cofins'             => $format2($valorCofins),
				'valor_inss'               => $format2($valorInss),
				'valor_ir'                 => $format2($valorIr),
				'valor_csll'               => $format2($valorCsll),
				'outras_retencoes'         => $format2($outrasRetencoes),
				'valor_iss'                => $format2($valorIss),
				'desconto_incondicionado'  => $format2($descontoIncond),
				'desconto_condicionado'    => $format2($descontoCond),
			];

			// Só informa valor_aliquota (Aliquota no XML) se a prefeitura exige
			$valoresServico['valor_aliquota'] = $format2($aliquotaIssPercent);

			// Item (estrutura da API)
			$itemServico = [
				'codigo'                      => $itemListaServico,
				'codigo_cnae'                 => (string)($servico->codigo_cnae ?? ''),
				'codigo_tributacao_municipio' => (string)($servico->codigo_tributacao_municipio ?? ''),
				'discriminacao'               => $this->retiraAcentos((string)$servico->discriminacao),
				'quantidade'                  => (string)$quantidadeItem,
				'valor_unitario'              => $format2($valorUnitarioItem),
				'valor_servicos'              => (float)$valorServicos,
			];

			// ============================
			// MONTAGEM DO PAYLOAD
			// ============================
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
						'valor_servicos' => $format2($valorServicos),
						'valores'        => $valoresServico,
					],
					'iss_retido' => ((int)($servico->iss_retido ?? 0)) === 1,
					'item_lista_servico' => $itemListaServico,

					// Local da prestação (EX: 5201405 - Aparecida) <<< AJUSTE
					'codigo_municipio'    => (string)$codigoMunicipioPrestacao,

					// Município de incidência (quem recolhe ISS – ex: 5208707 - Goiânia) <<< AJUSTE
					'municipio_incidencia' => (string)$codigoMunicipioIncidencia,

					'exigibilidade_iss' => (string)($servico->exigibilidade_iss),
					'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),

					// Fração da alíquota (0.0200, 0.0344, etc.)
					'aliquota_issqn' => $format4($aliquotaIssqnFrac), // <<< AJUSTE (já zero quando não precisa)

					'itens' => [
						$itemServico
					],
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
						'cep' => preg_replace('/[^0-9]/', '', (string)$item->cep ?? ''),
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
					if (!empty($consulta->sucesso)) {
						$item->estado = 'aprovado';
						$item->url_pdf_nfse = $consulta->link_pdf ?? '';
						$item->numero_nfse = $consulta->numero ?? 0;
						$item->serie = $consulta->serie ?? '';
						$item->codigo_verificacao = $consulta->codigo_verificacao ?? '';
						$item->save();

						if (isset($empresa->ultimo_numero_nfse) && !empty($consulta->numero)) {
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



	private function definirMunicipioIncidencia(
		string $itemListaServico,
		?string $codigoMunicipioEmitente,
		?string $codigoMunicipioPrestacao,
		?string $codigoMunicipioTomador
	): ?string {
		// Aqui você vai refinando conforme for mapeando a LC 116
		switch ($itemListaServico) {
			case '4.01':
				// Medicina: no seu cenário, ISS recolhido em Goiânia (emitente)
				return $codigoMunicipioEmitente;

				// Exemplo de outros casos futuramente:
				// case '7.02': // Consultoria
				//     return $codigoMunicipioTomador;
				//
				// case '3.05': // Intermediação
				//     return $codigoMunicipioEmitente;

			default:
				// Padrão: incide no emitente
				return $codigoMunicipioEmitente;
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
		$config = Business::where('id', $business_id)->first();
		$token = NfseConfig::where('empresa_id', $business_id)->first();
		$item = Nfse::findOrFail($request->id);

		// usar a classe da Webmania (NFSeWeb), não o model App\Models\Nfse
		$nfse = new NfseSdk([
			'token' => $token->token,
			'ambiente' => $config->ambiente,
			'options' => ['debug' => false, 'timeout' => 60, 'port' => 443, 'http_version' => CURL_HTTP_VERSION_NONE],
		]);

		try {
			$response = $nfse->consulta(['chave' => $item->chave]);

			$object = $response->info_nfse[0];

			if (isset($object->info_nfse)) {
				$object = $object->info_nfse[0];
			}

			if (isset($object->codigo_verificacao)) {
				$item->codigo_verificacao = $object->codigo_verificacao;
				if (isset($object->pdf_nfse)) {
					$item->url_pdf_nfse = $object->pdf_nfse;
				}
				$item->url_pdf_rps = $object->pdf_rps ?? null;
				$item->url_xml = $object->xml ?? null;
				$item->numero_nfse = $object->numero ?? null;
				$item->uuid = $object->uuid ?? $item->uuid;
				$item->estado = 'aprovado';
				$item->save();

				if (!empty($item->url_xml)) {
					$xml = file_get_contents($item->url_xml);
					file_put_contents(public_path('nfse_doc/') . "$item->uuid.xml", $xml);
				}
			}

			if (($object->status ?? null) == "reprovado") {
				$item->estado = 'rejeitado';
				$item->save();
				return response()->json($object, 401);
			}

			if (($object->status ?? null) == "cancelado") {
				$item->estado = 'cancelado';
				$item->save();
			}

			// retorno mais simples para ver o resultado no console do navegador
			return response()->json($object, 200);
		} catch (\Throwable $th) {
			return response()->json($th->getMessage(), 401);
		} catch (APIException $a) {
			return response()->json($a->getMessage(), 401);
		}
	}

	public function cancelar(Request $request)
	{
		// dd($request->all());
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

		// dd($resp);

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

		if (!empty($nota->cancelamento_pdf_path)) {
			$fullPath = public_path($nota->cancelamento_pdf_path);
			if (file_exists($fullPath)) {
				return response()->file($fullPath);
			}
		}
		// usa a chave exatamente como foi salva no cancelar()/finalizarNFSeAntiga()
		$this->sincronizarCancelamentoViaConsulta($nota);

		if (!empty($nota->cancelamento_pdf_path)) {
			$fullPath = public_path($nota->cancelamento_pdf_path);
		} else {
			// fallback: padrão antigo usando chave limpa
			$chaveLimpa = preg_replace('/[^0-9]/', '', $nota->chave);
			$fullPath = public_path('nfse_cancelada_doc/' . $chaveLimpa . '.pdf');
		}

		if (!file_exists($fullPath)) {
			abort(404, 'PDF de cancelamento não encontrado');
		}

		return response()->file($fullPath);
	}

	private function sincronizarCancelamentoViaConsulta(Nfse $nota): void
	{
		// Se já tem chave, tenta consultar na Integra Notas
		if (empty($nota->chave)) {
			return;
		}

		$business_id = request()->session()->get('user.business_id');
		if (!$business_id) {
			return;
		}

		$empresa = Business::find($business_id);
		$token   = NfseConfig::where('empresa_id', $business_id)->first();

		if (!$empresa || !$token) {
			return;
		}

		try {
			$sdk = new NfseSdk([
				'token'   => trim((string)$token->token),
				'ambiente' => (int)$empresa->ambiente,
				'options' => [
					'debug'        => false,
					'timeout'      => 60,
					'port'         => 443,
					'http_version' => CURL_HTTP_VERSION_NONE,
				],
			]);

			// Consulta pela CHAVE da NFSe (já cancelada por substituição)
			$resp  = $sdk->consulta(['chave' => $nota->chave]);
			$dados = $resp->nfse ?? $resp;

			Log::info('NFSE CANCELAMENTO - Consulta para sincronizar cancelamento', [
				'nfse_id'   => $nota->id,
				'chave'     => $nota->chave,
				'top_keys'  => is_object($resp)  ? array_keys(get_object_vars($resp)) : null,
				'nfse_keys' => (is_object($dados)) ? array_keys(get_object_vars($dados)) : null,
			]);

			// Na resposta que você mostrou, o XML do cancelamento vem em "xml"
			// e o PDF é acessado via link (link_pdf). Alguns provedores também
			// retornam "pdf" em base64 – então tentamos nas duas formas.
			$pdfBase64 = $dados->pdf  ?? $resp->pdf  ?? null;
			$xmlBase64 = $dados->xml  ?? $resp->xml  ?? null;
			$linkPdf   = $dados->link_pdf ?? $resp->link_pdf ?? null;

			// Garante pastas
			if (!is_dir(public_path('nfse_cancelada_doc'))) {
				@mkdir(public_path('nfse_cancelada_doc'), 0777, true);
			}
			if (!is_dir(public_path('nfse_cancelada_xml'))) {
				@mkdir(public_path('nfse_cancelada_xml'), 0777, true);
			}
			if (!is_dir(public_path('nfse_cancelada_log'))) {
				@mkdir(public_path('nfse_cancelada_log'), 0777, true);
			}

			$chaveLimpa = preg_replace('/[^0-9]/', '', $nota->chave);

			// Salvar XML de cancelamento (vem em base64 na resposta que você mandou)
			if (!empty($xmlBase64)) {
				$xmlPathRel = 'nfse_cancelada_xml/' . $chaveLimpa . '.xml';
				@file_put_contents(public_path($xmlPathRel), base64_decode($xmlBase64));
				$nota->cancelamento_xml_path = $xmlPathRel;
			}

			// Salvar PDF de cancelamento:
			// 1) se vier "pdf" em base64, usa direto
			if (!empty($pdfBase64)) {
				$pdfPathRel = 'nfse_cancelada_doc/' . $chaveLimpa . '.pdf';
				@file_put_contents(public_path($pdfPathRel), base64_decode($pdfBase64));
				$nota->cancelamento_pdf_path = $pdfPathRel;
			}
			// 2) se só vier "link_pdf" (URL), você pode manter o link na NFSe
			//    e opcionalmente baixar o PDF dessa URL no futuro, se precisar.
			if (!empty($linkPdf) && empty($nota->cancelamento_pdf_path)) {
				// aqui apenas guardamos o link para referência (se tiver campo pra isso)
				// se não tiver coluna específica, pode ignorar ou usar um campo de log
				// ex: $nota->cancelamento_mensagem = 'link_pdf: '.$linkPdf;
			}

			// Campos básicos de controle (opcional)
			$nota->cancelamento_codigo      = $resp->codigo   ?? $nota->cancelamento_codigo;
			$nota->cancelamento_mensagem    = $resp->mensagem ?? $nota->cancelamento_mensagem;
			$nota->cancelamento_data_evento = !empty($resp->data_hora_evento)
				? \Carbon\Carbon::parse($resp->data_hora_evento)
				: $nota->cancelamento_data_evento;

			// Log bruto da resposta para debug
			$logPathRel = 'nfse_cancelada_log/' . $chaveLimpa . '.json';
			@file_put_contents(public_path($logPathRel), json_encode($resp));
			$nota->cancelamento_log_path = $logPathRel;

			$nota->save();

			Log::info('NFSE CANCELAMENTO - Arquivos de cancelamento sincronizados via consulta', [
				'nfse_id'   => $nota->id,
				'pdf_path'  => $nota->cancelamento_pdf_path ?? null,
				'xml_path'  => $nota->cancelamento_xml_path ?? null,
				'link_pdf'  => $linkPdf ?? null,
			]);
		} catch (\Throwable $e) {
			Log::error('NFSE CANCELAMENTO - Erro ao consultar/sincronizar cancelamento', [
				'nfse_id'  => $nota->id ?? null,
				'chave'    => $nota->chave ?? null,
				'mensagem' => $e->getMessage(),
				'linha'    => $e->getLine(),
			]);
		}
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
				'data_competencia' => $servico->data_competencia ? \Carbon\Carbon::parse($servico->data_competencia)->format('Y-m-d\TH:i:sP') : \Carbon\Carbon::now()->format('Y-m-d\TH:i:sP'),
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
						'codigo_municipio' => $item->cidade->codigo ?? '',
						'uf' => $item->cidade->uf ?? '',
						'nome_municipio' => $item->cidade->nome ?? '',
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
					'aliquota_issqn' => $servico->aliquota_issqn,
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
						'aliquota_issqn' => $servico->aliquota_issqn,
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




	public function substituicaoForm($id)
	{
		$business_id = request()->session()->get('user.business_id');
		$item = Nfse::with('servico')
			->where('empresa_id', $business_id)
			->findOrFail($id);

		abort_if($item->estado !== 'aprovado', 403, 'Somente NFSe aprovada pode ser substituída.');

		$clientes = Contact::where('business_id', $business_id)->orderBy('name', 'asc')->get();
		$servicos = Servico::where('business_id', $business_id)->orderBy('nome', 'desc')->get();
		$config = BusinessLocation::where('business_id', $business_id)->first();
		$nfseConfig = NfseConfig::where('empresa_id', $business_id)->first();
		$types = Contact::getContactTypes();
		$tipo = 'customer';
		$usuario = User::allUsersDropdown($business_id, false);
		$cities = $this->prepareCities();

		return view('nfse.substituir', compact(
			'item',
			'clientes',
			'servicos',
			'config',
			'nfseConfig',
			'tipo',
			'types',
			'usuario',
			'cities'
		))->with('title', 'Substituir NFSe #' . $item->numero_nfse)
			->with('estados', $this->prepareUFs());
	}









	public function substituirSalvar(Request $request, $id)
	{
		$this->_validate($request);
		$request->validate([
			'justificativa_substituicao' => 'required|string|min:15',
		]);

		$business_id = request()->session()->get('user.business_id');

		$nfseAntiga = Nfse::with('servico')
			->where('empresa_id', $business_id)
			->findOrFail($id);

		abort_if($nfseAntiga->estado !== 'aprovado', 403, 'Somente NFSe aprovada pode ser substituída.');

		$nfseNova = DB::transaction(function () use ($request, $business_id, $nfseAntiga) {
			$nova = $this->duplicarNotaParaSubstituicao($request, $business_id);

			$nova->motivo_substituicao = $request->justificativa_substituicao;
			$nova->substituicao_de_id  = $nfseAntiga->id; // guarda a origem
			$nova->estado              = 'novo';          // usuário vai transmitir depois
			$nova->save();

			$nfseAntiga->motivo_substituicao = $request->justificativa_substituicao;
			$nfseAntiga->substituicao_por_id = $nova->id; // aponta para a nova
			$nfseAntiga->save();

			return $nova;
		});

		Log::info('NFSE SUBSTITUICAO - Nova NFSe criada para substituição', [
			'empresa_id'      => $business_id,
			'nfse_antiga_id'  => $nfseAntiga->id,
			'nfse_antiga_num' => $nfseAntiga->numero_nfse,
			'nfse_nova_id'    => $nfseNova->id,
			'nfse_nova_num'   => $nfseNova->numero_nfse,
			'estado_nova'     => $nfseNova->estado,
		]);

		return redirect()->route('nfse.index')->with('status', [
			'success' => 1,
			'msg'     => 'Nova NFSe criada para substituição (#' . $nfseNova->id . '). Edite ou transmita na lista normalmente.',
		]);
	}




	private function duplicarNotaParaSubstituicao(Request $request, int $business_id): Nfse
	{
		$valorServico  = (float)str_replace(',', '.', $request->valor_servico);
		$valorDeducoes = $request->valor_deducoes ? (float)str_replace(',', '.', $request->valor_deducoes) : 0;
		$base          = max($valorServico - $valorDeducoes, 0);

		$aliq = fn($campo) => $request->$campo ? (float)str_replace(',', '.', $request->$campo) : 0;

		$pis       = $base * ($aliq('aliquota_pis') / 100);
		$cofins    = $base * ($aliq('aliquota_cofins') / 100);
		$inss      = $base * ($aliq('aliquota_inss') / 100);
		$ir        = $base * ($aliq('aliquota_ir') / 100);
		$csll      = $base * ($aliq('aliquota_csll') / 100);
		$issRetido = $request->iss_retido == 1 ? $base * ($aliq('aliquota_iss') / 100) : 0;
		$issqn     = $base * ($aliq('aliquota_issqn') / 100);

		$descIncond = $request->desconto_incondicional ? (float)str_replace(',', '.', $request->desconto_incondicional) : 0;
		$descCond   = $request->desconto_condicional ? (float)str_replace(',', '.', $request->desconto_condicional) : 0;
		$outrasRet  = $request->outras_retencoes ? (float)str_replace(',', '.', $request->outras_retencoes) : 0;

		$valorLiquido = $base - ($pis + $cofins + $inss + $ir + $csll + $issRetido + $issqn) - $outrasRet - $descIncond - $descCond;
		$totalServico = max($valorLiquido, 0);

		$nfse = Nfse::create([
			'empresa_id'        => $business_id,
			'valor_total'       => $totalServico,
			'estado'            => 'novo',
			'serie'             => '',
			'codigo_verificacao' => '',
			'numero_nfse'       => 0,
			'url_xml'           => '',
			'url_pdf_nfse'      => '',
			'url_pdf_rps'       => '',
			'cliente_id'        => $request->cliente,
			'natureza_operacao' => $request->natureza_operacao,
			'documento'         => $request->documento,
			'razao_social'      => $request->razao_social,
			'nome_fantasia'     => $request->nome_fantasia,
			'im'                => $request->im ?? '',
			'ie'                => $request->ie ?? '',
			'cep'               => $request->cep ?? '',
			'rua'               => $request->rua,
			'numero'            => $request->numero,
			'bairro'            => $request->bairro,
			'complemento'       => $request->complemento ?? '',
			'cidade_id'         => $request->cidade_id,
			'email'             => $request->email ?? '',
			'telefone'          => $request->telefone ?? ''
		]);

		NfseServico::create([
			'nfse_id'                    => $nfse->id,
			'discriminacao'              => $request->discriminacao,
			'valor_servico'              => str_replace(',', '.', $request->valor_servico),
			'servico_id'                 => $request->servico_id,
			'codigo_cnae'                => $request->codigo_cnae ?? '',
			'codigo_servico'             => $request->codigo_servico ?? '',
			'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
			'exigibilidade_iss'          => $request->exigibilidade_iss,
			'iss_retido'                 => $request->iss_retido,
			'data_competencia'           => $request->data_competencia ?? null,
			'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
			'cidade_local_prestacao_servico_id' => $request->cidade_local_prestacao_servico_id ?? '',
			'valor_deducoes'             => $valorDeducoes,
			'desconto_incondicional'     => $descIncond,
			'desconto_condicional'       => $descCond,
			'outras_retencoes'           => $outrasRet,
			'aliquota_iss'               => $aliq('aliquota_iss'),
			'aliquota_pis'               => $aliq('aliquota_pis'),
			'aliquota_cofins'            => $aliq('aliquota_cofins'),
			'aliquota_inss'              => $aliq('aliquota_inss'),
			'aliquota_ir'                => $aliq('aliquota_ir'),
			'aliquota_csll'              => $aliq('aliquota_csll'),
			'intermediador'              => $request->intermediador ?? 'n',
			'documento_intermediador'    => $request->documento_intermediador ?? '',
			'nome_intermediador'         => $request->nome_intermediador ?? '',
			'im_intermediador'           => $request->im_intermediador ?? '',
			'responsavel_retencao_iss'   => $request->responsavel_retencao_iss ?? 1,
		]);

		return $nfse;
	}


	private function montarPayloadRPS(Nfse $nfse, Business $empresa, NfseConfig $token, bool $usarNumeroExistente = false): array
	{
		$servico = $nfse->servico;

		// Helpers
		$format2 = function ($v) {
			return number_format((float)$v, 2, '.', '');
		};
		$format4 = function ($v) {
			return number_format((float)$v, 4, '.', '');
		};

		// IBGE emitente
		$codigoMunicipioEmitente = null;
		if (!empty($token->cidade_id)) {
			$city = City::find($token->cidade_id);
			$codigoMunicipioEmitente = $city ? (string)$city->codigo : null;
		}


		// Tomador docs
		$doc = preg_replace('/[^0-9]/', '', (string)$nfse->documento);
		$isCpfTomador = strlen($doc) === 11;

		// Competência YYYY-MM
		$competencia = date('Y-m');

		// Simples Nacional
		$optanteSimples = ((int)($empresa->regime ?? 1)) === 1;
		$incentivoFiscal = false;

		// Cálculos
		$valorServicos = (float)$servico->valor_servico;
		$valorDeducoes = (float)($servico->valor_deducoes ?? 0);
		$baseCalculo = max($valorServicos - $valorDeducoes, 0);
		$aliquotaIssPercent = (float)($servico->aliquota_iss ?? 0);
		$aliquotaIssqnFrac = (float)($servico->aliquota_issqn ?? 0);
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

		// Prestador
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

		$numero = (!empty($nfse->numero_nfse) && (int)$nfse->numero_nfse > 0)
			? (int)$nfse->numero_nfse
			: ((int)($empresa->numero_rps ?? 0) + 1);

		$numeroSerie = !empty($nfse->serie)
			? (int)$nfse->serie
			: (int)($empresa->numero_serie_nfse ?? 1);

		return [
			'numero' => (string)$numero,
			'serie' => (string)$numeroSerie,
			'tipo' => '1',
			'data_emissao' => date('Y-m-d\TH:i:sP'),
			'competencia' => $competencia,
			'natureza_operacao' => (string)($nfse->natureza_operacao ?? '1'),
			'optante_simples_nacional' => $optanteSimples,
			'incentivo_fiscal' => $incentivoFiscal,
			'status' => '1',
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
					'valor_aliquota' => $format2($aliquotaIssPercent),
					'desconto_incondicionado' => $format2($descontoIncond),
					'desconto_condicionado' => $format2($descontoCond),
				],
				'iss_retido' => ((int)($servico->iss_retido ?? 0)) === 1,
				'item_lista_servico' => $itemListaServico,
				'codigo_municipio' => (string)$codigoMunicipioEmitente,
				'municipio_incidencia' => (string)$codigoMunicipioEmitente,
				'exigibilidade_iss' => (string)($servico->exigibilidade_iss),
				'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),
				'aliquota_issqn' => $format4($servico->aliquota_issqn),
				'itens' => [[
					'codigo' => $itemListaServico,
					'codigo_cnae' => (string)($servico->codigo_cnae ?? ''),
					'codigo_tributacao_municipio' => (string)($servico->codigo_tributacao_municipio ?? ''),
					'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),
					'quantidade' => '1',
					'valor_unitario' => $format2($valorServicos),
					'valor_servicos' => (float)$valorServicos,
					'valor_aliquota' => $format2($aliquotaIssPercent),
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
				'razao_social' => $this->retiraAcentos((string)$nfse->razao_social),
				'endereco' => [
					'logradouro' => $this->retiraAcentos((string)$nfse->rua),
					'numero' => $this->retiraAcentos((string)$nfse->numero),
					'complemento' => $this->retiraAcentos((string)($nfse->complemento ?? '')),
					'bairro' => $this->retiraAcentos((string)$nfse->bairro),
					'codigo_municipio' => (string)($nfse->cidade->codigo ?? ''),
					'uf' => (string)($nfse->cidade->uf ?? ''),
					'cep' => preg_replace('/[^0-9]/', '', (string)$nfse->cep),
				],
				'contato' => [
					'telefone' => (string)($nfse->telefone ?? ''),
					'email' => (string)($nfse->email ?? ''),
				],
			],
			'orgao_gerador' => [
				'codigo_municipio' => (string)$codigoMunicipioEmitente,
			],
			'nacional' => true,
			'codigo_aleatorio' => $codigoAleatorio,
			'token_prestador' => $tokenPrestador,
		];
	}

	private function executarSubstituicaoAutomatica(Nfse $nfseAntiga, Nfse $nfseNova, string $justificativa, string $codigoCancelamento = '1'): array
	{
		$business_id = request()->session()->get('user.business_id');
		$empresa = Business::find($business_id);
		$token   = NfseConfig::where('empresa_id', $business_id)->first();

		if (!$empresa || !$token) {
			return ['status' => 401, 'body' => ['sucesso' => false, 'mensagem' => 'Configuração NFSe não encontrada.']];
		}
		if (empty($nfseAntiga->chave)) {
			return ['status' => 422, 'body' => ['sucesso' => false, 'mensagem' => 'NFSe antiga sem chave.']];
		}

		if (!is_dir(public_path('nfse_doc'))) @mkdir(public_path('nfse_doc'), 0777, true);
		if (!is_dir(public_path('nfse_pdf'))) @mkdir(public_path('nfse_pdf'), 0777, true);

		$sdk = new NfseSdk([
			'token' => trim((string) $token->token),
			'ambiente' => (int) $empresa->ambiente,
			'options' => ['debug' => false, 'timeout' => 60, 'port' => 443, 'http_version' => CURL_HTTP_VERSION_NONE],
		]);

		$payload = array_merge(
			$this->montarPayloadRPS($nfseNova, $empresa, $token, false),
			[
				'chave' => $nfseAntiga->chave,
				'codigo_cancelamento' => $codigoCancelamento,
				'justificativa' => $justificativa,
			]
		);

		// dd($payload);

		Log::info('NFSE SUBSTITUICAO - Payload enviado para Integra Notas', [
			'nfse_nova_id'    => $nfseNova->id,
			'nfse_nova_num'   => $nfseNova->numero_nfse,
			'nfse_antiga_id'  => $nfseAntiga->id,
			'nfse_antiga_num' => $nfseAntiga->numero_nfse,
			'payload'         => $payload,
		]);


		try {
			$resp = $sdk->substitui($payload);

			dd($resp);

			Log::info('NFSE SUBSTITUICAO - DEBUG estrutura retorno substitui', [
				'tipo'          => gettype($resp),
				'top_keys'      => is_object($resp) ? array_keys(get_object_vars($resp)) : null,
				'has_nfse'      => (is_object($resp) && isset($resp->nfse)),
				'nfse_keys'     => (is_object($resp) && isset($resp->nfse) && is_object($resp->nfse))
					? array_keys(get_object_vars($resp->nfse))
					: null,
				// flags pra saber se tem pdf/xml em cada nível
				'has_pdf_top'   => (is_object($resp) && isset($resp->pdf)),
				'has_xml_top'   => (is_object($resp) && isset($resp->xml)),
				'has_pdf_nfse'  => (is_object($resp) && isset($resp->nfse) && is_object($resp->nfse) && isset($resp->nfse->pdf)),
				'has_xml_nfse'  => (is_object($resp) && isset($resp->nfse) && is_object($resp->nfse) && isset($resp->nfse->xml)),
			]);

			$dados = $resp->nfse ?? $resp;

			if (!empty($resp->sucesso) && !empty($dados->numero) && !empty($dados->chave)) {

				Log::info('NFSE SUBSTITUICAO - Sucesso direto na API', [
					'nfse_nova_id'    => $nfseNova->id,
					'nfse_nova_num'   => $dados->numero ?? null,
					'chave_nova'      => $dados->chave ?? null,
					'nfse_antiga_id'  => $nfseAntiga->id,
					'chave_antiga'    => $nfseAntiga->chave,
				]);

				$this->sincronizarNovaNFSe($nfseNova, $resp);
				$this->finalizarNFSeAntiga($nfseAntiga, $nfseNova, $resp);
				return ['status' => 200, 'body' => $resp];
			}

			if ((int)($resp->codigo ?? 0) === 5008 && !empty($resp->chave)) {

				Log::warning('NFSE SUBSTITUICAO - NFSe já existe na API (5008), consultando...', [
					'nfse_nova_id'   => $nfseNova->id,
					'chave_informada' => $resp->chave,
				]);


				$consulta = $sdk->consulta(['chave' => $resp->chave]);

				Log::info('NFSE SUBSTITUICAO - Resposta consulta após 5008', [
					'nfse_nova_id' => $nfseNova->id,
					'consulta'     => $consulta,
				]);


				$dadosConsulta = $consulta->nfse ?? $consulta;

				if (!empty($consulta->sucesso) && !empty($dadosConsulta->numero) && !empty($dadosConsulta->chave)) {
					$this->sincronizarNovaNFSe($nfseNova, $consulta);
					$this->finalizarNFSeAntiga($nfseAntiga, $nfseNova, $consulta);
					return ['status' => 200, 'body' => $consulta];
				}
			}

			Log::warning('NFSE SUBSTITUICAO - Falha na substituição', [
				'nfse_nova_id'   => $nfseNova->id,
				'nfse_antiga_id' => $nfseAntiga->id,
				'codigo'         => $resp->codigo ?? null,
				'mensagem'       => $resp->mensagem ?? null,
				'erros'          => $resp->erros ?? null,
			]);

			return ['status' => 422, 'body' => ['sucesso' => false, 'mensagem' => $resp->mensagem ?? $resp->erros ?? 'Erro na substituição', 'detalhes' => $resp]];
		} catch (\Throwable $e) {

			Log::error('NFSE SUBSTITUICAO - Exceção ao chamar API', [
				'nfse_nova_id'   => $nfseNova->id ?? null,
				'nfse_antiga_id' => $nfseAntiga->id ?? null,
				'mensagem'       => $e->getMessage(),
				'linha'          => $e->getLine(),
				'trace'          => $e->getTraceAsString(),
			]);

			return ['status' => 500, 'body' => ['sucesso' => false, 'mensagem' => 'Erro ao processar substituição: ' . $e->getMessage()]];
		}
	}

	private function sincronizarNovaNFSe(Nfse $nfseNova, $retorno): void
	{
		$dados = $retorno->nfse ?? $retorno;

		Log::info('NFSE SUBSTITUICAO - Sincronizando nova NFSe aprovada', [
			'nfse_nova_id'    => $nfseNova->id,
			'antes' => [
				'estado'      => $nfseNova->estado,
				'numero_nfse' => $nfseNova->numero_nfse,
				'serie'       => $nfseNova->serie,
			],
			'depois' => [
				'numero'      => $dados->numero ?? null,
				'serie'       => $dados->serie ?? null,
				'chave'       => $dados->chave ?? ($retorno->chave ?? null),
			],
		]);


		$nfseNova->estado = 'aprovado';
		$nfseNova->numero_nfse = $dados->numero ?? $nfseNova->numero_nfse;
		$nfseNova->serie = $dados->serie ?? $nfseNova->serie;
		$nfseNova->codigo_verificacao = $dados->codigo_verificacao ?? $nfseNova->codigo_verificacao;
		$nfseNova->chave = $dados->chave ?? $retorno->chave ?? $nfseNova->chave;
		$nfseNova->url_pdf_nfse = $dados->link_pdf ?? $nfseNova->url_pdf_nfse;
		$nfseNova->url_xml = $dados->xml ?? $nfseNova->url_xml;
		$nfseNova->save();

		$business_id = request()->session()->get('user.business_id');
		if ($business_id) {
			$empresa = Business::find($business_id);


			if ($business_id && isset($empresa)) {
				Log::info('NFSE SUBSTITUICAO - Atualizando contadores da empresa', [
					'empresa_id'          => $empresa->id,
					'novo_numero_rps'     => $empresa->numero_rps ?? null,
					'novo_ultimo_nfse'    => $empresa->ultimo_numero_nfse ?? null,
				]);
			}


			if ($empresa) {
				if (isset($dados->rps_numero)) {
					$empresa->numero_rps = (int) $dados->rps_numero;
				}
				if (isset($dados->numero)) {
					$empresa->ultimo_numero_nfse = (int) $dados->numero;
				}
				$empresa->save();
			}
		}

		if (!empty($dados->xml)) {
			@file_put_contents(public_path('nfse_doc/') . $nfseNova->chave . '.xml', base64_decode($dados->xml));
		}
		if (!empty($dados->pdf)) {
			@file_put_contents(public_path('nfse_pdf/') . $nfseNova->chave . '.pdf', base64_decode($dados->pdf));
		}
	}

	private function finalizarNFSeAntiga(Nfse $nfseAntiga, Nfse $nfseNova, $retorno = null): void
	{
		$nfseAntiga->estado = 'cancelado';
		$nfseAntiga->cancelado_em = now();
		$nfseAntiga->chave_referenciada = $nfseNova->chave;


		Log::info('NFSE SUBSTITUICAO - Atualizando NFSe antiga como cancelada', [
			'nfse_antiga_id'   => $nfseAntiga->id,
			'nfse_antiga_num'  => $nfseAntiga->numero_nfse,
			'chave_antiga'     => $nfseAntiga->chave,
			'chave_referenciada' => $nfseAntiga->chave_referenciada,
			'antes'            => [
				'estado'        => $antes['estado'] ?? null,
				'cancelado_em'  => $antes['cancelado_em'] ?? null,
			],
			'depois'           => [
				'estado'        => $nfseAntiga->estado,
				'cancelado_em'  => $nfseAntiga->cancelado_em,
			],
		]);


		if ($retorno) {
			// Garante pastas
			if (!is_dir(public_path('nfse_cancelada_doc'))) {
				@mkdir(public_path('nfse_cancelada_doc'), 0777, true);
			}
			if (!is_dir(public_path('nfse_cancelada_xml'))) {
				@mkdir(public_path('nfse_cancelada_xml'), 0777, true);
			}
			if (!is_dir(public_path('nfse_cancelada_log'))) {
				@mkdir(public_path('nfse_cancelada_log'), 0777, true);
			}

			// Usa chave limpa para manter padrão com imprimirCancelamento()
			$chaveLimpa = preg_replace('/[^0-9]/', '', $nfseAntiga->chave);


			Log::info('NFSE SUBSTITUICAO - Salvando arquivos de cancelamento da NFSe antiga', [
				'nfse_antiga_id' => $nfseAntiga->id,
				'pdf_path'       => !empty($pdfCancel) ? 'nfse_cancelada_doc/' . $chaveLimpa . '.pdf' : null,
				'xml_path'       => !empty($xmlCancel) ? 'nfse_cancelada_xml/' . $chaveLimpa . '.xml' : null,
			]);


			$pdfCancel = $retorno->pdf ?? null;
			$xmlCancel = $retorno->xml ?? null;

			if (!empty($pdfCancel)) {
				$pdfPathRel = 'nfse_cancelada_doc/' . $chaveLimpa . '.pdf';
				@file_put_contents(public_path($pdfPathRel), base64_decode($pdfCancel));
				$nfseAntiga->cancelamento_pdf_path = $pdfPathRel;
			}

			if (!empty($xmlCancel)) {
				$xmlPathRel = 'nfse_cancelada_xml/' . $chaveLimpa . '.xml';
				@file_put_contents(public_path($xmlPathRel), base64_decode($xmlCancel));
				$nfseAntiga->cancelamento_xml_path = $xmlPathRel;
			}

			// Campos de controle do cancelamento
			$nfseAntiga->cancelamento_codigo      = $retorno->codigo   ?? $nfseAntiga->cancelamento_codigo;
			$nfseAntiga->cancelamento_mensagem    = $retorno->mensagem ?? $nfseAntiga->cancelamento_mensagem;
			$nfseAntiga->cancelamento_data_evento = !empty($retorno->data_evento)
				? \Carbon\Carbon::parse($retorno->data_evento)
				: $nfseAntiga->cancelamento_data_evento;

			// Log bruto do retorno (opcional)
			$logPathRel = 'nfse_cancelada_log/' . $chaveLimpa . '.json';
			@file_put_contents(public_path($logPathRel), json_encode($retorno));
			$nfseAntiga->cancelamento_log_path = $logPathRel;
		}

		$nfseAntiga->save();
	}

	public function enviarSubstituicao(Request $request)
	{
		$nfseNova = Nfse::with('servico')->findOrFail($request->id);

		if ($nfseNova->estado === 'cancelado') {
			return response()->json(['sucesso' => false, 'mensagem' => 'Esta NFSe está cancelada.'], 401);
		}
		if (empty($nfseNova->substituicao_de_id)) {
			return response()->json(['sucesso' => false, 'mensagem' => 'Esta NFSe não é uma substituição.'], 422);
		}

		$nfseAntiga = Nfse::find($nfseNova->substituicao_de_id);
		if (!$nfseAntiga || $nfseAntiga->estado !== 'aprovado') {
			return response()->json(['sucesso' => false, 'mensagem' => 'NFSe original não encontrada ou não aprovada.'], 422);
		}

		Log::info('NFSE SUBSTITUICAO - Enviando substituição para API', [
			'nfse_nova_id'    => $nfseNova->id,
			'nfse_nova_num'   => $nfseNova->numero_nfse,
			'nfse_antiga_id'  => $nfseAntiga->id,
			'nfse_antiga_num' => $nfseAntiga->numero_nfse,
			'justificativa'   => $nfseNova->motivo_substituicao ?? $request->justificativa ?? null,
		]);

		$resultado = $this->executarSubstituicaoAutomatica(
			$nfseAntiga,
			$nfseNova,
			$nfseNova->motivo_substituicao ?? $request->justificativa ?? 'Substituição de NFSe',
			$request->codigo_cancelamento ?? '1'
		);

		if ($resultado['status'] === 200) {
			return response()->json([
				'sucesso' => true,
				'mensagem' => 'Substituição concluída com sucesso',
				'nfse_nova' => $nfseNova->numero_nfse,
				'nfse_antiga' => $nfseAntiga->numero_nfse,
			], 200);
		}

		return response()->json($resultado['body'], $resultado['status']);
	}

	public function previewPayload($id)
	{
		$business_id = request()->session()->get('user.business_id');

		$empresa = Business::where('id', $business_id)->firstOrFail();
		$token   = NfseConfig::where('empresa_id', $business_id)->firstOrFail();

		$item = Nfse::with('servico', 'cidade')
			->where('empresa_id', $business_id)
			->findOrFail($id);

		$servico = $item->servico;

		// Helpers
		$format2 = function ($v) {
			return number_format((float)$v, 2, '.', '');
		};
		$format4 = function ($v) {
			return number_format((float)$v, 4, '.', '');
		};

		// ============================
		// IBGE emitente
		// ============================
		$codigoMunicipioEmitente = null;
		if (!empty($token->cidade_id)) {
			$city = City::find($token->cidade_id);
			$codigoMunicipioEmitente = $city ? (string)$city->codigo : null;
		}

		// Regime no BD: 'simples' ou 'normal'
		$regime = strtolower(trim((string)($token->regime ?? 'simples')));
		$optanteSimples = $regime === 'simples';

		if ($regime === 'simples') {
			$optanteSimples = true;
		} else {
			$optanteSimples = false;
		}

		$isMei = $regime === 'mei';

		// issRetido = 2 - não
		// issRetido = 1 - sim
		$issRetido = ((int)($servico->iss_retido ?? 2)) === 1;

		// ============================
		// MUNICÍPIO DA PRESTAÇÃO (local onde fez o serviço)
		// ============================
		$codigoMunicipioPrestacao = null;
		if (!empty($servico->cidade_local_prestacao_servico_id)) {
			$city = City::find($servico->cidade_local_prestacao_servico_id);
			$codigoMunicipioPrestacao = $city ? (string)$city->codigo : null;
		}

		// Se não tiver cidade de prestação definida, podemos usar a cidade do tomador
		$codigoMunicipioTomador = null;
		if (!empty($item->cidade_id) && $item->cidade) {
			$codigoMunicipioTomador = (string)($item->cidade->codigo ?? '');
		}

		// ============================
		// MUNICÍPIO DE INCIDÊNCIA (quem recolhe o ISS)
		// ============================
		$itemListaServico = (string)$servico->codigo_servico;
		$codigoMunicipioIncidencia = $this->definirMunicipioIncidencia(
			$itemListaServico,
			$codigoMunicipioEmitente,
			$codigoMunicipioPrestacao,
			$codigoMunicipioTomador
		);

		// Por padrão, alíquota é obrigatória
		$deveInformarAliquotaISS = true;

		// Exceções onde pode ser 0%
		// if ($isMei) {
		// 	$deveInformarAliquotaISS = false;
		// } elseif ($codigoMunicipioIncidencia === $codigoMunicipioEmitente) {
		// 	$deveInformarAliquotaISS = false;
		// }

		// Tomador docs
		$doc = preg_replace('/[^0-9]/', '', (string)$item->documento);
		$im = preg_replace('/[^0-9]/', '', (string)$item->im);
		$ie = preg_replace('/[^0-9]/', '', (string)$item->ie);
		$isCpfTomador = strlen($doc) === 11;

		// Competência YYYY-MM
		$competencia = $servico->data_competencia
			? \Carbon\Carbon::parse($servico->data_competencia)->format('Y-m-d\TH:i:sP')
			: \Carbon\Carbon::now()->format('Y-m-d\TH:i:sP');

		// Simples Nacional
		$incentivoFiscal = false;
		$outrasInfo = '';

		// ============================
		// CÁLCULOS DE VALORES
		// ============================
		$valorServicos = (float)$servico->valor_servico;
		$valorDeducoes = (float)($servico->valor_deducoes ?? 0);
		$baseCalculo = max($valorServicos - $valorDeducoes, 0);

		// aliquota_iss = percentual (ex: 3.44)
		// aliquota_issqn = fração (ex: 0.0344)
		$aliquotaIssPercent = (float)($servico->aliquota_iss ?? 0);
		$aliquotaIssqnFrac = (float)($servico->aliquota_issqn ?? 0);

		// Se não precisa informar alíquota ISS, zera a fração para não gerar valor_iss
		// if (!$deveInformarAliquotaISS) {
		// 	$aliquotaIssPercent = 0;
		// 	$aliquotaIssqnFrac = 0;
		// }

		$valorIss = $baseCalculo * ($aliquotaIssqnFrac / 100);

		$valorPis = (float)($servico->valor_pis ?? 0);
		$valorCofins = (float)($servico->valor_cofins ?? 0);
		$valorInss = (float)($servico->valor_inss ?? 0);
		$valorIr = (float)($servico->valor_ir ?? 0);
		$valorCsll = (float)($servico->valor_csll ?? 0);
		$outrasRetencoes = (float)($servico->outras_retencoes ?? 0);
		$descontoIncond = (float)($servico->desconto_incondicional ?? 0);
		$descontoCond = (float)($servico->desconto_condicional ?? 0);

		$valorLiquidoNfse = $baseCalculo
			- $valorPis
			- $valorCofins
			- $valorInss
			- $valorIr
			- $valorCsll
			- $outrasRetencoes
			- $descontoIncond
			- $descontoCond;

		// Itens
		$quantidadeItem = 1;
		$valorUnitarioItem = $valorServicos;

		// Numeração (igual enviar: próximo RPS)
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

		// ============================
		// BLOCO VALORES SERVIÇO
		// ============================
		$valoresServico = [
			'valor_deducoes'           => $format2($valorDeducoes),
			'valor_pis'                => $format2($valorPis),
			'valor_cofins'             => $format2($valorCofins),
			'valor_inss'               => $format2($valorInss),
			'valor_ir'                 => $format2($valorIr),
			'valor_csll'               => $format2($valorCsll),
			'outras_retencoes'         => $format2($outrasRetencoes),
			'valor_iss'                => $format2($valorIss),
			'desconto_incondicionado'  => $format2($descontoIncond),
			'desconto_condicionado'    => $format2($descontoCond),
		];

		// Só informa valor_aliquota se a prefeitura exige
		if ($deveInformarAliquotaISS) {
			$valoresServico['valor_aliquota'] = $format2($aliquotaIssPercent);
		}

		// Item (estrutura da API)
		$itemServico = [
			'codigo'                      => $itemListaServico,
			'codigo_cnae'                 => (string)($servico->codigo_cnae ?? ''),
			'codigo_tributacao_municipio' => (string)($servico->codigo_tributacao_municipio ?? ''),
			'discriminacao'               => $this->retiraAcentos((string)$servico->discriminacao),
			'quantidade'                  => (string)$quantidadeItem,
			'valor_unitario'              => $format2($valorUnitarioItem),
			'valor_servicos'              => (float)$valorServicos,
		];

		// ============================
		// MONTAGEM DO PAYLOAD (IGUAL ENVIAR)
		// ============================
		$payload = [
			'numero' => (string)$numero,
			'serie'  => (string)$numeroSerie,
			'tipo'   => '1',
			'data_emissao' => date('Y-m-d\TH:i:sP'),
			'competencia'  => $competencia,
			'natureza_operacao' => (string)($item->natureza_operacao ?? '1'),
			'optante_simples_nacional' => $optanteSimples,
			'incentivo_fiscal' => $incentivoFiscal,
			'status' => '1',
			'outras_informacoes' => $outrasInfo,
			'valores_nfse' => [
				'base_calculo'       => $format2($baseCalculo),
				'valor_liquido_nfse' => $format2($valorLiquidoNfse),
			],
			'servico' => [
				'valor_servicos' => $format2($valorServicos),
				'valores' => [
					'valor_servicos' => $format2($valorServicos),
					'valores'        => $valoresServico,
				],
				'iss_retido' => ((int)($servico->iss_retido ?? 0)) === 1,
				'item_lista_servico' => $itemListaServico,
				'codigo_municipio'    => (string)$codigoMunicipioPrestacao,
				'municipio_incidencia' => (string)$codigoMunicipioIncidencia,
				'exigibilidade_iss' => (string)($servico->exigibilidade_iss),
				'discriminacao' => $this->retiraAcentos((string)$servico->discriminacao),
				'aliquota_issqn' => $format4($aliquotaIssqnFrac),
				'itens' => [
					$itemServico,
				],
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
					'cep' => preg_replace('/[^0-9]/', '', (string)$item->cep ?? ''),
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


		return response()->json([
			'nfse_id' => $item->id,
			'numero'  => $item->numero_nfse,
			'estado'  => $item->estado,
			'payload' => $payload,
		], 200, [], JSON_PRETTY_PRINT);
	}
}
