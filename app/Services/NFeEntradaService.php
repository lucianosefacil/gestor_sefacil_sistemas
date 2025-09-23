<?php

namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\Business;
use App\Models\Trnasaction;
use App\Models\Venda;
use NFePHP\NFe\Complements;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Tributacao;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeEntradaService
{

	private $config;
	private $tools;

	public function __construct($config)
	{
		$business_id = request()->session()->get('user.business_id');
		$certificado = Business::find($business_id);
		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado->certificado, base64_decode($certificado->senha_certificado)));
		$this->tools->model('55');
	}

	public function gerarNFe($compra, $natureza, $tipoPagamento)
	{

		$business_id = request()->session()->get('user.business_id');
		$config = Business::find($business_id);

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00';
		$stdInNFe->Id = null;
		$stdInNFe->pk_nItem = '';

		$infNFe = $nfe->taginfNFe($stdInNFe);

		$compraLast = $compra->lastNFe($compra);

		$stdIde = new \stdClass();
		$stdIde->cUF = $config->getcUF($config->cidade->uf);
		$stdIde->cNF = rand(11111, 99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

		$stdIde->mod = 55;
		$stdIde->serie = $config->numero_serie_nfe;;
		$stdIde->nNF = (int)$compraLast + 1;
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = 0; // 0 Entrada;
		$stdIde->idDest = $config->cidade->uf != $compra->contact->cidade->uf ? 2 : 1;
		$stdIde->cMunFG = $config->cidade->codigo;

		$stdIde->tpImp = 1;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = $config->ambiente;
		$stdIde->finNFe = 1;
		$stdIde->indFinal = 1;
		$stdIde->indPres = 1;
		$stdIde->procEmi = '0';
		$stdIde->verProc = '2.0';

		$tagide = $nfe->tagide($stdIde);

		$stdEmit = new \stdClass();
		$stdEmit->xNome = $config->razao_social;
		$stdEmit->xFant = $config->name;

		$ie = str_replace(".", "", $config->ie);
		$ie = str_replace("/", "", $ie);
		$ie = str_replace("-", "", $ie);
		$stdEmit->IE = $ie;
		$stdEmit->CRT = $config->regime;

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$stdEmit->CNPJ = $cnpj;
		$stdEmit->IM = $ie;
		$emit = $nfe->tagemit($stdEmit);



		// ENDERECO EMITENTE
		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $config->rua;
		$stdEnderEmit->nro = $config->numero;
		$stdEnderEmit->xCpl = "";

		$stdEnderEmit->xBairro = $config->bairro;
		$stdEnderEmit->cMun = $config->cidade->codigo;
		$stdEnderEmit->xMun = $config->cidade->nome;
		$stdEnderEmit->UF = $config->cidade->uf;

		$cep = str_replace("-", "", $config->cep);
		$cep = str_replace(".", "", $cep);
		$stdEnderEmit->CEP = $cep;
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'BRASIL';

		$enderEmit = $nfe->tagenderEmit($stdEnderEmit);



		// DESTINATARIO
		$stdDest = new \stdClass();
		$stdDest->xNome = $compra->contact->name;

		if ($compra->contact->contribuinte) {
			if ($compra->contact->ie_rg == 'ISENTO' || $compra->contact->ie_rg == NULL) {
				$stdDest->indIEDest = "2";
			} else {
				$stdDest->indIEDest = "1";
			}
		} else {
			$stdDest->indIEDest = "9";
		}

		$cnpj_cpf = str_replace(".", "", $compra->contact->cpf_cnpj);
		$cnpj_cpf = str_replace("/", "", $cnpj_cpf);
		$cnpj_cpf = str_replace("-", "", $cnpj_cpf);

		if (strlen($cnpj_cpf) == 14) {
			$stdDest->CNPJ = $cnpj_cpf;
			$ie = str_replace(".", "", $compra->contact->ie_rg);
			$ie = str_replace("/", "", $ie);
			$ie = str_replace("-", "", $ie);
			$stdDest->IE = $ie;
		} else {
			$stdDest->CPF = $cnpj_cpf;
		}

		$dest = $nfe->tagdest($stdDest);


		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $compra->contact->rua;
		$stdEnderDest->nro = $compra->contact->numero;
		$stdEnderDest->xCpl = "";
		$stdEnderDest->xBairro = $compra->contact->bairro;
		$stdEnderDest->cMun = $compra->contact->cidade->codigo;
		$stdEnderDest->xMun = strtoupper($compra->contact->cidade->nome);
		$stdEnderDest->UF = $compra->contact->cidade->uf;

		$cep = str_replace("-", "", $compra->contact->cep);
		$cep = str_replace(".", "", $cep);
		$stdEnderDest->CEP = $cep;
		$stdEnderDest->cPais = "1058";
		$stdEnderDest->xPais = "BRASIL";

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		$somaProdutos = 0;
		$somaICMS = 0;
		//PRODUTOS
		$itemCont = 0;

		$totalItens = is_array($compra->itens) ? sizeof($compra->itens) : 0;
		$somaFrete = 0;

		foreach ($compra->purchase_lines as $i) {
			$itemCont++;

			$stdProd = new \stdClass();
			$stdProd->item = $itemCont;
			$stdProd->cEAN = strlen($i->product->sku) < 7 ? 'SEM GTIN' : $i->product->sku;
			$stdProd->cEANTrib = strlen($i->product->sku) < 7 ? 'SEM GTIN' : $i->product->sku;
			$stdProd->cProd = $i->product->id;
			$stdProd->xProd = $i->product->name;
			$ncm = $i->product->ncm;
			$ncm = str_replace(".", "", $ncm);
			$stdProd->NCM = $ncm;



			$stdProd->CFOP = $config->cidade->uf != $compra->contact->cidade->uf ?
				$natureza->cfop_entrada_inter_estadual : $natureza->cfop_entrada_estadual;

			$cest = $i->product->cest;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;

			$stdProd->uCom = $i->product->unit->short_name;
			$stdProd->qCom = $i->quantity;
			$stdProd->vUnCom = $this->format($i->purchase_price);
			$stdProd->vProd = $this->format(($i->quantity * $i->purchase_price));
			$stdProd->uTrib = $i->product->unit->short_name;
			$stdProd->qTrib = $i->quantity;
			$stdProd->vUnTrib = $this->format($i->purchase_price);
			$stdProd->indTot = 1;
			$somaProdutos += ($i->quantity * $i->purchase_price);

			$prod = $nfe->tagprod($stdProd);


			//TAG IMPOSTO

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			$imposto = $nfe->tagimposto($stdImposto);

			if ($config->regime == 3) {

				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont;
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->product->cst_csosn;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $this->format($i->unit_price * $i->quantity);
				$stdICMS->pICMS = $this->format($i->product->perc_icms);
				$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

				$somaICMS += (($i->unit_price * $i->quantity)
					* ($stdICMS->pICMS / 100));
				$ICMS = $nfe->tagICMS($stdICMS);
			} else {

				$stdICMS = new \stdClass();

				$stdICMS->item = $itemCont;
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->product->cst_csosn;

				if ($i->product->cst_csosn == '500') {
					$stdICMS->vBCSTRet = 0.00;
					$stdICMS->pST = 0.00;
					$stdICMS->vICMSSTRet = 0.00;
				}

				$stdICMS->pCredSN = $this->format($i->product->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->product->perc_icms);
				$ICMS = $nfe->tagICMSSN($stdICMS);

				$somaICMS = 0;
			}




			//PIS
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont;
			$stdPIS->CST = $i->product->cst_pis;
			$stdPIS->vBC = $this->format($i->product->perc_pis) > 0 ? $stdProd->vProd : 0.00;
			$stdPIS->pPIS = $this->format($i->product->perc_pis);
			$stdPIS->vPIS = $this->format(($stdProd->vProd * $i->quantity) *
				($i->product->perc_pis / 100));
			$PIS = $nfe->tagPIS($stdPIS);

			//COFINS
			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont;
			$stdCOFINS->CST = $i->product->cst_cofins;
			$stdCOFINS->vBC = $this->format($i->product->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->product->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format(($stdProd->vProd * $i->quantity) *
				($i->product->perc_cofins / 100));
			$COFINS = $nfe->tagCOFINS($stdCOFINS);

			//IPI

			$stdIPI = new \stdClass();
			$stdIPI->item = $itemCont;
			//999 – para tributação normal IPI
			$stdIPI->cEnq = '999';
			$stdIPI->CST = $i->product->cst_ipi;
			$stdIPI->vBC = $this->format($i->product->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$stdIPI->pIPI = $this->format($i->product->perc_ipi);
			$stdIPI->vIPI = $stdProd->vProd * $this->format(($i->product->perc_ipi / 100));

			$nfe->tagIPI($stdIPI);
		}



		$stdICMSTot = new \stdClass();
		$stdICMSTot->vBC = 0.00;
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = $this->format($somaProdutos);
		$stdICMSTot->vFrete = 0.00;

		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format(0.00);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;
		// if($venda->frete){
		// 	$stdICMSTot->vNF = 
		// 	$this->format(($somaProdutos+$venda->frete->valor)-$venda->desconto);
		// } 
		$stdICMSTot->vNF = $this->format($somaProdutos);

		$stdICMSTot->vTotTrib = 0.00;

		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);


		$stdTransp = new \stdClass();
		$stdTransp->modFrete = '9';


		$transp = $nfe->tagtransp($stdTransp);

		$std = new \stdClass();
		$std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato = getenv('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = getenv('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std);






		// Define o tipo de pagamento (tPag) conforme seus métodos
		$tipoPagamento = $tipoPagamento;
		// dd($tipoPagamento);
		// Códigos considerados "à vista" segundo NT2016.002
		$pagamento_a_vista = in_array($tipoPagamento, [
			"01", // Dinheiro
			"04", // Cartão de débito
			"10", // Vale alimentação
			"11", // Vale refeição
			"12", // Vale presente
			"13", // Vale combustível
			"16", // Depósito bancário
			"17", // PIX
		]);

		// Gera tagfat e tagdup somente se NÃO for pagamento à vista
		// dd($pagamento_a_vista);
		if (!$pagamento_a_vista) {
			$stdFat = new \stdClass();
			$stdFat->nFat = (int)$compraLast + 1;
			$stdFat->vOrig = $this->format($somaProdutos);
			$stdFat->vDesc = $this->format(0.00);
			$stdFat->vLiq = $this->format($somaProdutos);
			$nfe->tagfat($stdFat);

			if (count($compra->payment_lines) > 1) {
				$contFatura = 1;
				foreach ($compra->payment_lines as $ft) {
					$stdDup = new \stdClass();
					$stdDup->nDup = "00" . $contFatura;
					$stdDup->dVenc = substr($ft->paid_on, 0, 10);
					$stdDup->vDup = $this->format($ft->amount);

					$nfe->tagdup($stdDup);
					$contFatura++;
				}
			} else {
				$stdDup = new \stdClass();
				$stdDup->nDup = '001';
				$stdDup->dVenc = Date('Y-m-d');
				$stdDup->vDup =  $this->format($somaProdutos);

				$nfe->tagdup($stdDup);
			}
		}
		// Gera tagpag e tagdetPag (sempre deve ser gerado)
		$stdPag = new \stdClass();
		$nfe->tagpag($stdPag);
		$stdDetPag = new \stdClass();
		$stdDetPag->tPag = $tipoPagamento;
		if ($tipoPagamento == '99') {
			$stdDetPag->xPag = "Multiplo pagamento";
		}
		$stdDetPag->vPag = $this->format($somaProdutos);
		// Informações adicionais para cartão de crédito/débito
		if (in_array($tipoPagamento, ['03', '04'])) {
			$stdDetPag->tpIntegra = 2;
		}
		// Define indPag com base em forma de pagamento
		$stdDetPag->indPag = $pagamento_a_vista ? 0 : 1;
		$nfe->tagdetPag($stdDetPag);






		// //Fatura

		// $stdFat = new \stdClass();
		// $stdFat->nFat = (int)$compraLast + 1;
		// $stdFat->vOrig = $this->format($somaProdutos);
		// $stdFat->vDesc = $this->format(0.00);
		// $stdFat->vLiq = $this->format($somaProdutos);

		// $fatura = $nfe->tagfat($stdFat);


		// //Duplicata

		// if (count($compra->payment_lines) > 1) {
		// 	$contFatura = 1;
		// 	foreach ($compra->payment_lines as $ft) {
		// 		$stdDup = new \stdClass();
		// 		$stdDup->nDup = "00" . $contFatura;
		// 		$stdDup->dVenc = substr($ft->paid_on, 0, 10);
		// 		$stdDup->vDup = $this->format($ft->amount);

		// 		$nfe->tagdup($stdDup);
		// 		$contFatura++;
		// 	}
		// } else {

		// 	$stdDup = new \stdClass();
		// 	$stdDup->nDup = '001';
		// 	$stdDup->dVenc = Date('Y-m-d');
		// 	$stdDup->vDup =  $this->format($somaProdutos);

		// 	$nfe->tagdup($stdDup);
		// }


		// $stdPag = new \stdClass();
		// $pag = $nfe->tagpag($stdPag);


		// $stdDetPag = new \stdClass();


		// $stdDetPag->tPag = $tipoPagamento;
		// $stdDetPag->vPag = $this->format($somaProdutos);
		// $stdDetPag->indPag = '0';
		// $detPag = $nfe->tagdetPag($stdDetPag);







		$stdInfoAdic = new \stdClass();
		$stdInfoAdic->infCpl = $compra->additional_notes;

		$infoAdic = $nfe->taginfAdic($stdInfoAdic);


		if (getenv("AUTXML")) {
			$std = new \stdClass();
			$std->CNPJ = getenv("AUTXML");
			$std->CPF = null;
			$nfe->tagautXML($std);
		}

		$aut_xml = preg_replace('/[^0-9]/', '', $config->aut_xml);

		if (strlen($aut_xml) > 10) {

			$std = new \stdClass();
			$std->CNPJ = $aut_xml;
			$nfe->tagautXML($std);
		}

		if ($nfe->montaNFe()) {
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'nNf' => $stdIde->nNF
			];
			return $arr;
		} else {
			throw new Exception("Erro ao gerar NFe");
		}
	}

	public function format($number, $dec = 2)
	{
		return number_format((float) $number, $dec, ".", "");
	}



	public function sign($xml)
	{
		return $this->tools->signNFe($xml);
	}


	public function transmitir($signXml, $chave, $cnpj)
	{
		try {
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);

			// Envio síncrono
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1, false);

			$std = (new \NFePHP\NFe\Common\Standardize())->toStd($resp);

			// ⚠️ Falha no processamento do lote
			if (!isset($std->cStat) || $std->cStat != 104) {
				return response()->json([
					'success' => false,
					'tipo' => 'lote',
					'mensagem' => "Erro no lote: [{$std->cStat}] - {$std->xMotivo}",
					'raw' => $std
				], 400);
			}

			// ⚠️ Rejeição da NF-e
			if (!isset($std->protNFe->infProt->cStat) || $std->protNFe->infProt->cStat != 100) {
				$cStat = $std->protNFe->infProt->cStat ?? '---';
				$xMotivo = $std->protNFe->infProt->xMotivo ?? 'Motivo não informado';

				return response()->json([
					'success' => false,
					'tipo' => 'rejeicao',
					'mensagem' => "Rejeição: [{$cStat}] - {$xMotivo}",
					'raw' => $std
				], 422);
			}

			// ✅ Autorizada
			$xmlAutorizado = \NFePHP\NFe\Complements::toAuthorize($signXml, $resp);
			$path = public_path("xml_nfe_entrada/{$cnpj}");
			if (!is_dir($path)) mkdir($path, 0777, true);
			file_put_contents("{$path}/{$chave}.xml", $xmlAutorizado);

			return response()->json([
				'success' => true,
				'mensagem' => 'NF-e autorizada com sucesso',
				'protocolo' => $std->protNFe->infProt->nProt,
				'xml_path' => "{$path}/{$chave}.xml"
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'tipo' => 'excecao',
				'mensagem' => 'Erro: ' . $e->getMessage()
			], 500);
		}
	}


	private function salvarXmlAutorizado($xml, $cnpj, $chave)
	{
		$caminho = public_path('xml_nfe_entrada/' . $cnpj);
		if (!is_dir($caminho)) {
			mkdir($caminho, 0777, true);
		}
		file_put_contents($caminho . '/' . $chave . '.xml', $xml);
	}


	// public function transmitir($signXml, $chave, $cnpj)
	// {
	// 	try {
	// 		$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
	// 		$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1, false);

	// 		$std = (new \NFePHP\NFe\Common\Standardize())->toStd($resp);

	// 		if ($std->cStat != 104) {
	// 			return [
	// 				'success' => false,
	// 				'mensagem' => "Erro: [{$std->cStat}] - {$std->xMotivo}"
	// 			];
	// 		}

	// 		if (!isset($std->protNFe->infProt->cStat) || $std->protNFe->infProt->cStat != 100) {
	// 			return [
	// 				'success' => false,
	// 				'mensagem' => "Rejeição: [{$std->protNFe->infProt->cStat}] - {$std->protNFe->infProt->xMotivo}"
	// 			];
	// 		}

	// 		$xmlAutorizado = \NFePHP\NFe\Complements::toAuthorize($signXml, $resp);

	// 		$path = public_path("xml_nfe_entrada/{$cnpj}");
	// 		if (!is_dir($path)) mkdir($path, 0777, true);
	// 		file_put_contents("{$path}/{$chave}.xml", $xmlAutorizado);

	// 		dd($std);
	// 		return [
	// 			'success' => true,
	// 			'mensagem' => 'NF-e autorizada com sucesso',
	// 			'protocolo' => $std->protNFe->infProt->nProt
	// 		];

	// 	} catch (\Exception $e) {
	// 		return [
	// 			'success' => false,
	// 			'mensagem' => 'Erro: ' . $e->getMessage()
	// 		];
	// 	}
	// }






	public function cancelar($compra, $justificativa, $cnpj)
	{
		try {
			$chave = $compra->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			// dd($arr);

			if (!isset($arr['protNFe']['infProt']['nProt'])) {
				return [
					'erro' => true,
					'mensagem' => $arr['xMotivo'] ?? 'NF-e não consta na base de dados da SEFAZ',
					'data' => $arr,
					'status' => 404,
					'tipo' => 'consulta'
				];
			}

			sleep(1);
			$xJust = $justificativa;

			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
				//TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
				if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
					//SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					if (!is_dir(public_path('xml_nfe_entrada_cancelada/' . $cnpj))) {
						mkdir(public_path('xml_nfe_entrada_cancelada/' . $cnpj), 0777, true);
					}
					file_put_contents(public_path('xml_nfe_entrada_cancelada/' . $cnpj . '/' . $chave . '.xml'), $xml);
					return $arr;
				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}
		} catch (\Exception $e) {
			return [
				'erro' => true,
				'mensagem' => 'Erro inesperado ao cancelar: ' . $e->getMessage(),
				'status' => 500,
				'tipo' => 'excecao'
			];

			// echo $e->getMessage();
		}
	}

	public function cartaCorrecao($compra, $correcao, $cnpj)
	{
		try {

			$chave = $compra->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $compra->sequencia_cce + 1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(5);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
				//TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
					$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
					//SUCESSO PROTOCOLAR A SOLICITAÇÂO ANTES DE GUARDAR
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);

					if (!is_dir(public_path('xml_nfe_entrada_correcao/' . $cnpj))) {
						mkdir(public_path('xml_nfe_entrada_correcao/' . $cnpj), 0777, true);
					}
					file_put_contents(public_path('xml_nfe_entrada_correcao/' . $cnpj . '/' . $chave . '.xml'), $xml);

					$compra->sequencia_cce = $compra->sequencia_cce + 1;
					$compra->save();
					return $arr;
				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
}
