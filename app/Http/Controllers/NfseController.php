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
			session()->flash('mensagem_erro', 'Realize a configuração do emitente!');
			return redirect()->back();
		}

		$nfses = Nfse::where('empresa_id', $business_id)
			->orderBy('id', 'desc')
			->get();

		$total = 0;
		foreach ($nfses as $item) {
			$total += $item->valor_total;
		}

		if (!$config->certificado) {
			return response()->json('Configure o certificado para consultar', 403);
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
				session()->flash('mensagem_sucesso', 'Nfse removida!');
			}
		} catch (\Exception $e) {
			session()->flash('mensagem_erro', 'Algo deu errado!');
		}
		return redirect()->back();
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
			session()->flash('mensagem_sucesso', 'Nfse atualizada!');
		} catch (\Exception $e) {
			// echo $e->getLine();
			// die;
			session()->flash('mensagem_erro', 'Algo deu errado!');
		}
		return redirect('/nfse');
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
			session()->flash('mensagem_sucesso', 'Nfse criada');
		} catch (\Exception $e) {
			echo $e->getMessage();
			die;
			session()->flash('mensagem_erro', 'Algo deu errado!');
		}
		return redirect('/nfse');
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
		try {

			$business_id = request()->session()->get('user.business_id');
			$config = Business::where('id', $business_id)->first();
			if (!$config || !$config->token_nfse) {
				return response()->json(['status' => 'erro', 'mensagem' => 'Token NFSe não configurado'], 422);
			}

			Connection::getInstance()->setBearerToken($config->token_nfse);

			if (!is_dir(public_path('nfse_doc'))) {
				@mkdir(public_path('nfse_doc'), 0777, true);
			}

			$item = Nfse::findOrFail($request->id); // evita colisão com o SDK
			$servico = $item->servico;

			$nfse = new NFSeWeb();

			// Mapeia dados do serviço
			$servicoData = [
				'valorServico' => (float) $servico->valor_servico,
				'discriminacao' => $this->retiraAcentos($servico->discriminacao),
				'codigoServico' => $servico->codigo_servico,
				'naturezaOperacao' => (int) $item->natureza_operacao,
				'issRetido' => (int) $servico->iss_retido,
			];

			// Adicionar deduções e descontos (nomes corretos da biblioteca Webmania)
			if (!empty($servico->valor_deducoes) && $servico->valor_deducoes > 0) {
				$servicoData['deducoes'] = (float) $servico->valor_deducoes;
			}
			if (!empty($servico->desconto_incondicional) && $servico->desconto_incondicional > 0) {
				$servicoData['descontoIncondicionado'] = (float) $servico->desconto_incondicional;
			}
			if (!empty($servico->desconto_condicional) && $servico->desconto_condicional > 0) {
				$servicoData['descontoCondicionado'] = (float) $servico->desconto_condicional;
			}
			if (!empty($servico->outras_retencoes) && $servico->outras_retencoes > 0) {
				$servicoData['outrasRetencoes'] = (float) $servico->outras_retencoes;
			}

			if (!empty($servico->codigo_tributacao_municipio)) {
				$servicoData['codigoTributacaoMunicipio'] = $servico->codigo_tributacao_municipio;
			}
			if (!empty($servico->codigo_cnae)) {
				$servicoData['codigoCnae'] = $servico->codigo_cnae;
			}
			if (!empty($servico->exigibilidade_iss)) {
				$servicoData['exigibilidadeIss'] = (int) $servico->exigibilidade_iss;
			}

			// Intermediário (quando iss_retido = 1)
			if ((int) $servico->iss_retido === 1) {
				$docInter = preg_replace('/[^0-9]/', '', $config->cnpj);
				$nfse->Servico->Intermediario->nomeCompleto = $config->razao_social;
				if (strlen($docInter) === 11) {
					$nfse->Servico->Intermediario->cpf = $docInter;
				} else {
					$nfse->Servico->Intermediario->cnpj = $docInter;
				}
			}

			// Log dos dados do serviço antes de enviar
			Log::info('=== DADOS DO SERVIÇO PARA ENVIAR À API ===', [
				'servicoData' => $servicoData,
				'nfse_id' => $item->id
			]);

			// Atribui serviço (sem verificar property_exists para permitir propriedades dinâmicas)
			foreach ($servicoData as $attr => $value) {
				if ($value !== null && $value !== '') {
					$nfse->Servico->{$attr} = $value;
				}
			}

			// Impostos (alíquotas)
			if (!empty($servico->aliquota_iss)) {
				if (property_exists($nfse->Servico->Impostos, 'iss')) {
					$nfse->Servico->Impostos->iss = (float) $servico->aliquota_iss;
				}
			}
			if (!empty($servico->aliquota_pis) && $servico->aliquota_pis > 0) {
				if (property_exists($nfse->Servico->Impostos, 'pis')) {
					$nfse->Servico->Impostos->pis = (float) $servico->aliquota_pis;
				}
			}
			if (!empty($servico->aliquota_cofins) && $servico->aliquota_cofins > 0) {
				if (property_exists($nfse->Servico->Impostos, 'cofins')) {
					$nfse->Servico->Impostos->cofins = (float) $servico->aliquota_cofins;
				}
			}
			if (!empty($servico->aliquota_inss) && $servico->aliquota_inss > 0) {
				if (property_exists($nfse->Servico->Impostos, 'inss')) {
					$nfse->Servico->Impostos->inss = (float) $servico->aliquota_inss;
				}
			}
			if (!empty($servico->aliquota_ir) && $servico->aliquota_ir > 0) {
				if (property_exists($nfse->Servico->Impostos, 'ir')) {
					$nfse->Servico->Impostos->ir = (float) $servico->aliquota_ir;
				}
			}
			if (!empty($servico->aliquota_csll) && $servico->aliquota_csll > 0) {
				if (property_exists($nfse->Servico->Impostos, 'csll')) {
					$nfse->Servico->Impostos->csll = (float) $servico->aliquota_csll;
				}
			}

			// Tomador (CPF ou CNPJ)
			$docTomador = preg_replace('/[^0-9]/', '', $item->documento);
			if (strlen($docTomador) === 11) {
				$nfse->Tomador->nomeCompleto = $this->retiraAcentos($item->razao_social);
				$nfse->Tomador->cpf = $docTomador;
			} else {
				$nfse->Tomador->razaoSocial = $this->retiraAcentos($item->razao_social);
				$nfse->Tomador->cnpj = $docTomador;
			}

			if (!empty($item->ie)) {
				$nfse->Tomador->inscricaoEstadual = preg_replace('/[^0-9]/', '', $item->ie);
			}
			if (!empty($item->im)) {
				$nfse->Tomador->inscricaoMunicipal = preg_replace('/[^0-9]/', '', $item->im);
			}

			$nfse->Tomador->cep = preg_replace('/[^0-9]/', '', $item->cep);
			$nfse->Tomador->endereco = $this->retiraAcentos($item->rua);
			$nfse->Tomador->numero = $item->numero;
			if (!empty($item->complemento)) {
				$nfse->Tomador->complemento = $this->retiraAcentos($item->complemento);
			}
			$nfse->Tomador->bairro = $this->retiraAcentos($item->bairro);
			$nfse->Tomador->cidade = $this->retiraAcentos($item->cidade->nome);
			$nfse->Tomador->uf = $item->cidade->uf;

			// Emissão (ambiente)
			$response = ($config->ambiente == 2)
				? $nfse->emitirHomologacao()
				: $nfse->emitir();

			$payload = $response->getMessage();
			$object = is_string($payload) ? json_decode($payload) : $payload;

			// Trata status de retorno
			if (isset($object->status)) {

				if ($object->status === 'reprovado') {
					$item->estado = 'rejeitado';
					$item->save();
					return response()->json($object, 422);
				}

				if ($object->status === 'processado') {
					$dados = $object->info_nfse[0] ?? $object;

					$item->codigo_verificacao = $dados->codigo_verificacao ?? '';
					$item->url_pdf_nfse = $dados->pdf_nfse ?? '';
					$item->url_pdf_rps = $dados->pdf_rps ?? '';
					$item->url_xml = $dados->xml ?? '';
					$item->numero_nfse = $dados->numero ?? 0;
					$item->uuid = $dados->uuid ?? '';
					$item->estado = 'aprovado';
					$item->save();

					if (!empty($item->url_xml)) {
						try {
							$xml = @file_get_contents($item->url_xml);
							if ($xml) {
								@file_put_contents(public_path('nfse_doc/') . "$item->uuid.xml", $xml);
							}
						} catch (\Throwable $t) { /* ignora erro de download */
						}
					}

					return response()->json($object, 200);
				}

				if ($object->status === 'processando') {
					$item->estado = 'processando';
					$item->uuid = $object->uuid ?? $item->uuid;
					$item->save();
					return response()->json($object, 202);
				}

				// fallback: tratado como aprovado
				$item->codigo_verificacao = $object->codigo_verificacao ?? '';
				$item->url_pdf_nfse = $object->pdf_nfse ?? '';
				$item->url_pdf_rps = $object->pdf_rps ?? '';
				$item->url_xml = $object->xml ?? '';
				$item->numero_nfse = $object->numero ?? 0;
				$item->uuid = $object->uuid ?? '';
				$item->estado = 'aprovado';
				$item->save();

				if (!empty($item->url_xml)) {
					try {
						$xml = @file_get_contents($item->url_xml);
						if ($xml) {
							@file_put_contents(public_path('nfse_doc/') . "$item->uuid.xml", $xml);
						}
					} catch (\Throwable $t) { /* ignora erro de download */
					}
				}

				return response()->json($object, 200);
			}

			// Quando a API retorna string de processamento
			$msg = is_string($payload) ? $payload : json_encode($payload);
			if (strpos($msg, 'Nota Fiscal já se encontra em processamento') === 0) {
				$item->estado = 'processando';
				$item->save();
				return response()->json(['status' => 'processando', 'mensagem' => $msg], 202);
			}

			return response()->json(['status' => 'erro', 'mensagem' => $msg], 400);
		} catch (APIException $a) {
			return response()->json(['status' => 'erro', 'mensagem' => $a->getMessage()], 400);
		} catch (\Throwable $th) {
			return response()->json([
				'status' => 'erro',
				'mensagem' => $th->getMessage(),
				'linha' => $th->getLine()
			], 500);
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
		try {
			Log::info('=== INICIANDO CANCELAMENTO NFSE ===', [
				'request_data' => $request->all()
			]);

			$business_id = request()->session()->get('user.business_id');
			$config = Business::findOrFail($business_id);

			Connection::getInstance()->setBearerToken($config->token_nfse);

			$item = Nfse::findOrFail($request->id);

			$nfse = new NFSeWeb();
			$nfse->uuid = $item->uuid;

			// Frontend envia "justificativa" ou "motivo"
			$motivo = $request->justificativa ?? $request->motivo ?? 'Cancelamento requerido';

			Log::info('Chamando API de cancelamento', [
				'uuid' => $item->uuid,
				'motivo' => $motivo
			]);

			$response = $nfse->cancelar($motivo);

			$payload = $response->getMessage();
			$object = is_string($payload) ? json_decode($payload) : $payload;

			// Log da resposta da API
			Log::info('Resposta API Cancelamento NFSe:', [
				'uuid' => $item->uuid,
				'response' => $object
			]);

			// Se vier dentro de array: info_nfse
			if (isset($object->info_nfse)) {
				$object = $object->info_nfse[0];
			}

			// Verificar se o cancelamento foi bem-sucedido (campo "sucesso" = true)
			if (isset($object->sucesso) && $object->sucesso === true) {

				// Atualizar estado no banco
				$item->estado = 'cancelado';
				$item->cancelado_em = now();
				$item->save();

				// Criar diretórios se não existirem
				if (!is_dir(public_path('nfse_cancelada_doc'))) {
					@mkdir(public_path('nfse_cancelada_doc'), 0777, true);
				}
				if (!is_dir(public_path('nfse_cancelada_xml'))) {
					@mkdir(public_path('nfse_cancelada_xml'), 0777, true);
				}

				$stamp = date('Ymd_His');
				$name = "cancelamento_nfse_{$item->numero_nfse}_{$stamp}";

				// Salvar XML (base64 ou URL)
				if (isset($object->xml) && $object->xml) {
					try {
						if (filter_var($object->xml, FILTER_VALIDATE_URL)) {
							$xml = file_get_contents($object->xml);
						} else {
							$xml = base64_decode($object->xml);
						}

						if ($xml) {
							file_put_contents(public_path("nfse_cancelada_xml/$name.xml"), $xml);
							$item->cancelamento_xml_path = "nfse_cancelada_xml/$name.xml";
						}
					} catch (\Exception $e) {
						Log::error('Erro ao salvar XML de cancelamento: ' . $e->getMessage());
					}
				}

				// Salvar PDF (base64 ou URL)
				if (isset($object->pdf) && $object->pdf) {
					try {
						if (filter_var($object->pdf, FILTER_VALIDATE_URL)) {
							$pdf = file_get_contents($object->pdf);
						} else {
							$pdf = base64_decode($object->pdf);
						}

						if ($pdf) {
							file_put_contents(public_path("nfse_cancelada_doc/$name.pdf"), $pdf);
							$item->cancelamento_pdf_path = "nfse_cancelada_doc/$name.pdf";
						}
					} catch (\Exception $e) {
						Log::error('Erro ao salvar PDF de cancelamento: ' . $e->getMessage());
					}
				}

				$item->save();

				// Log de sucesso
				Log::info('NFSe cancelada com sucesso:', [
					'id' => $item->id,
					'uuid' => $item->uuid,
					'numero_nfse' => $item->numero_nfse
				]);
			}

			// Retornar EXATAMENTE o que a API enviou (frontend espera isso)
			Log::info('=== FINALIZANDO CANCELAMENTO - RETORNANDO RESPOSTA ===', [
				'sucesso' => isset($object->sucesso) ? $object->sucesso : 'campo não existe',
				'response_completa' => $object
			]);

			return response()->json($object, 200);
		} catch (\Throwable $th) {
			Log::error('=== ERRO AO CANCELAR NFSE ===', [
				'mensagem' => $th->getMessage(),
				'linha' => $th->getLine(),
				'arquivo' => $th->getFile(),
				'trace' => $th->getTraceAsString()
			]);

			return response()->json([
				'erro' => $th->getMessage(),
				'linha' => $th->getLine()
			], 500);
		}
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
}
