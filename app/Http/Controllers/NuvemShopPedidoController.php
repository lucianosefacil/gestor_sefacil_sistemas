<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Category;
use App\Models\City;
use App\Models\Contact;
use App\Models\NaturezaOperacao;
use App\Models\NuvemShopItemPedido;
use App\Models\NuvemShopPedido;
use App\Models\Pais;
use App\Models\Product;
use Dompdf\Dompdf;
use App\Models\PurchaseLine;
use App\Models\TaxRate;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\TransactionSellLine;
use App\Models\Transportadora;
use App\Models\Unit;
use App\Models\Variation;
use Illuminate\Http\Request;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\Util;
use Facade\Ignition\DumpRecorder\Dump;

class NuvemShopPedidoController extends Controller
{
    protected $contactUtil;
    protected $moduleUtil;
    protected $productUtil;
    protected $transactionUtil;
    protected $commonUtil;
    protected $businessUtil;


    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil,  BusinessUtil $businessUtil, ContactUtil $contactUtil, ProductUtil $productUtil, TransactionUtil $transactionUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->commonUtil = $commonUtil;
        $this->businessUtil = $businessUtil;

        $this->middleware(function ($request, $next) {
            $business_id = request()->session()->get('user.business_id');
            $business_id = $business_id;
            $value = auth()->user();
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // dd($request);
        try {
            $store_info = session('store_info');
            if (!$store_info) {
                return redirect('/nuvemshop');
            }

            $page = $request->page ? $request->page : 1;

            $cliente = $request->cliente;
            $data_inicial = $request->data_inicial;
            $data_final = $request->data_final;

            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
            // try {
            if ($cliente != "" || $data_inicial != "" || $data_final != "") {
                $sql = "orders?q=" . $cliente . "";
                if ($data_inicial != "") {
                    $sql .= "&created_at_min=" . \Carbon\Carbon::parse(str_replace("/", "-", $data_inicial))->format('Y-m-d') . "";
                }
                if ($data_final != "") {
                    $sql .= "&created_at_max=" . \Carbon\Carbon::parse(str_replace("/", "-", $data_final))->format('Y-m-d') . "";
                }
                $pedidos = (array)$api->get($sql . "&per_page=10");
            } else {
                $pedidos = (array)$api->get("orders?page=" . $page . "&per_page=10");
            }
            $pedidos = $pedidos['body'];
            // } catch (\Exception $e) {
            //     echo $e->getMessage();
            //     die;
            //     // $pedidos = [];

            $this->salvaPedidos($pedidos);

            foreach ($pedidos as $p) {
                $p->numero_nfe = 0;
            }
            $title = 'Pedidos';
            return view('nuvemshop_pedidos.pedidos', compact('pedidos', 'title', 'page', 'cliente', 'data_inicial', 'data_final'));
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => ('Não existe pedido no momento!')
            ];
            return redirect('/nuvemshop/produtos')->with(['status' => $output]);
        }
    }

    private function salvaPedidos($pedidos)
    {
        // dd($pedidos);
        foreach ($pedidos as $p) {
            $business_id = request()->session()->get('user.business_id');

            if (isset($p->customer)) {
                $name = $p->customer->name;
                $email = $p->customer->email;
                $doc = $p->customer->identification;
                $id = $p->customer->id;
            } else {
                $name = $p->contact_name;
                $email = $p->contact_email;
                $doc = $p->contact_identification;
                $id = $p->id;
            }
            $data = [
                'pedido_id' => $p->id,
                'rua' => $p->billing_address,
                'numero' => $p->billing_number ?? 0,
                'bairro' => $p->billing_locality ?? '',
                'cidade' => $p->billing_city,
                'cep' => $p->billing_zipcode,
                'total' => $p->total,
                'cliente_id' => $id,
                'observacao' => $p->shipping_option,
                'nome' => $name,
                'email' => $email,
                'documento' => $doc,
                'business_id' => $business_id,
                'subtotal' => $p->subtotal,
                'desconto' => $p->discount,
                'numero_nfe' => 0,
                'status_envio' => $p->shipping_status,
                'gateway' => $p->gateway,
                'status_pagamento' => $p->payment_status,
                'data' => $p->created_at
            ];

            $pedido = NuvemShopPedido::where('pedido_id', $p->id)->first();

            if ($pedido == null) {

                $this->salvaCliente($p, $id);

                $pedido = NuvemShopPedido::create($data);

                foreach ($p->products as $prod) {
                    $produto = $this->validaProduto($prod);
                    $itemPedido = [
                        'pedido_id' => $pedido->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $prod->quantity,
                        'valor' => $prod->price,
                        'nome' => $prod->name
                    ];
                    NuvemShopItemPedido::create($itemPedido);
                }
            } else {
                $this->atualizaCliente($p);
            }
        }
    }

    private function validaProduto($prod)
    {
        $product = Product::where('nuvemshop_id', $prod->product_id)->first();

        if ($product != null) return $product;

        $business_id = request()->session()->get('user.business_id');
        $categoria = Category::where('business_id', $business_id)->first();
        $user_id = request()->session()->get('user.id');
        $config = Business::where('id', $business_id)->first();
        $unit = Unit::where('business_id', $business_id)->first();
        $data = [
            'codigo_barras' => $prod->variants[0]->barcode ?? '',
            'codigo_anp' => $prod->codigo_anp ?? '',
            'perc_glp' => $prod->perc_glp ?? 0,
            'perc_gnn' => $prod->perc_gnn ?? 0,
            'perc_gni' => $prod->perc_gni ?? 0,
            'valor_partida' => $prod->valor_partida ?? 0,
            'unidade_tributavel' => $prod->unidade_tributavel ?? '',
            'quantidade_tributavel' => $prod->quantidade_tributavel ?? 0,
            'veicProd' => $prod->veicProd ?? '',
            'tpOp' => $prod->tpOp ?? '',
            'chassi' => $prod->chassi ?? '',
            'cCor' => $prod->cCor ?? '',
            'xCor' => $prod->xCor ?? '',
            'pot' => $prod->pot ?? 0,
            'cilin' => $prod->cilin ?? 0,
            'pesoL' => $prod->pesoL ?? '',
            'pesoB' => $prod->pesoB ?? '',
            'nSerie' => $prod->nSerie ?? '',
            'tpComb' => $prod->tpComb ?? '',
            'nMotor' => $prod->nMotor ?? '',
            'CMT' => $prod->CMT ?? '',
            'dist' => $prod->dist ?? '',
            'anoMod' => $prod->anoMod ?? '',
            'anoFab' => $prod->anoFab ?? '',
            'tpPint' => $prod->tpPint ?? '',
            'tpVeic' => $prod->tpVeic ?? '',
            'espVeic' => $prod->espVeic ?? '',
            'VIN' => $prod->VIN ?? '',
            'condVeic' => $prod->condVeic ?? '',
            'cMod' => $prod->cMod ?? '',
            'cCorDENATRAN' => $prod->cCorDENATRAN ?? '',
            'lota' => $prod->lota ?? '',
            'tpRest' => $prod->tpRest ?? '',
            'perc_icms_interestadual' => $prod->perc_icms_interestadual ?? 0,
            'perc_icms_interno' => $prod->perc_icms_interno ?? 0,
            'pCredSN' => $prod->pCredSN ?? 0,
            'perc_fcp_interestadual' => $prod->perc_fcp_interestadual ?? 0,
            'pICMSST' => $prod->pICMSST ?? 0,
            'cBenef' => $prod->cBenef ?? '',
            'category_id' => $categoria->id ?? null,
            'largura' => $prod->largura ?? '',
            'comprimento' => $prod->comprimento ?? '',
            'altura' => $prod->altura ?? '',
            'peso_liquido' => $prod->peso ?? '',
            'peso_bruto' => $prod->peso ?? '',
            'weight' => $prod->weight ?? '',
            'sku' => $prod->referencia ?? '',
            'business_id' => $business_id,
            'created_by' => $user_id,
            'unit_id' => $unit->id,
            'cenq_ipi' => '999',
            'tax_type' => 'exclusive',
            'enable_stock' => '1',
            'type' => 'opening_stock',
            'name' => $prod->name,
            'nuvemshop_id' => $prod->product_id
        ];
        $form_fields = [
            'name', 'brand_id', 'unit_id', 'category_id', 'tax', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description', 'sub_unit_ids', 'perc_icms', 'perc_cofins', 'perc_pis', 'perc_ipi', 'cfop_interno', 'cfop_externo', 'cst_csosn', 'cst_pis', 'cst_cofins', 'cst_ipi', 'ncm', 'cest', 'codigo_barras', 'codigo_anp', 'perc_glp', 'perc_gnn', 'perc_gni', 'valor_partida', 'unidade_tributavel', 'quantidade_tributavel', 'tipo', 'veicProd', 'tpOp', 'chassi', 'cCor', 'xCor', 'pot', 'cilin', 'pesoL', 'pesoB', 'nSerie', 'tpComb', 'nMotor', 'CMT', 'dist', 'anoMod', 'anoFab', 'tpPint', 'tpVeic', 'espVeic', 'VIN', 'condVeic', 'cMod', 'cCorDENATRAN', 'lota', 'tpRest', 'ecommerce', 'destaque', 'novo', 'altura', 'largura', 'comprimento', 'valor_ecommerce', 'origem', 'cenq_ipi',
            'perc_icms_interestadual', 'perc_icms_interno', 'perc_fcp_interestadual', 'pICMSST', 'modBC', 'modBCST', 'pCredSN', 'cBenef'
        ];
        $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
        if (!empty($module_form_fields)) {
            $form_fields = array_merge($form_fields, $module_form_fields);
        }
        $product = Product::create($data);
        $product_locations = $config;
        if (!empty($product_locations)) {
            $product->product_locations()->sync($product_locations);
        }
        $profit_percent = $config->default_profit_percent;
        if ($product->referencia != null) {
            $product->sku = $prod->referencia;
        } else {
            $product->sku = $this->productUtil->generateProductSku($product->id);
        }
        $product->type = 'single';
        $product->enable_stock = 1;
        $product->save();
        $this->productUtil->createSingleProductVariation(
            $product->id,
            $product->sku,
            $prod->cost,
            $prod->price,
            $profit_percent,
            $prod->price,
            $prod->price,
        );
        $enable_product_editing = request()->session()->get('business.enable_editing_product_from_purchase');
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $transaction_data = [
            'ref_no' => null,
            'status' => 'received',
            'contact_id' => null,
            'transaction_date' => date('Y-m-d'),
            'total_before_tax' => number_format($prod->price * $prod->quantity),
            'location_id' => $config->id,
            'discount_type' => null,
            'discount_amount' => 0,
            'tax_id' => null,
            'tax_amount' => 0,
            'shipping_details' => null,
            'shipping_charges' => 0,
            'final_total' => number_format($prod->price * $prod->quantity),
            'additional_notes' => null,
            'exchange_rate' => 1,
            'pay_term_number' => null,
            'pay_term_type' => null,
            'type' => 'opening_stock',
        ];
        $exchange_rate = $transaction_data['exchange_rate'];
        $transaction_data['opening_stock_product_id'] = $product->id;
        $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax']);
        if ($transaction_data['discount_type'] == 'fixed') {
            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount']);
        } elseif ($transaction_data['discount_type'] == 'percentage') {
            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount']);
        } else {
            $transaction_data['discount_amount'] = 0;
        }
        $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount']);
        $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges']);
        $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total']);
        $transaction_data['business_id'] = $business_id;
        $transaction_data['created_by'] = $user_id;
        $transaction_data['payment_status'] = 'paid';

        Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

        $transaction = Transaction::create($transaction_data);
        $variation = Variation::where('product_id', '=', $product->id)->first();
        $qtd = $prod->quantity;

        $this->productUtil->updateProductQuantity($config->id, $product->id, $variation->id, $qtd, 0, null, false);

        $valorCompra = 0;

        $dataItemPurchase = [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => $qtd,
            'pp_without_discount' => $valorCompra,
            'purchase_price' => $valorCompra,
            'purchase_price_inc_tax' => $valorCompra
        ];
        PurchaseLine::create($dataItemPurchase);

        return $product;
    }


    private function atualizaCliente($pedido)
    {
        if (isset($pedido->customer)) {
            $name = $pedido->customer->name;
            $email = $pedido->customer->email;
            $doc = $pedido->customer->identification;
            $id = $pedido->customer->id;
        } else {
            $name = $pedido->contact_name;
            $email = $pedido->contact_email;
            $doc = $pedido->contact_identification;
            $id = $pedido->id;
        }
        $cliente = Contact::where('nuvemshop_id', $pedido->id)->first();
        try {
            $cliente->name = $name;
            $cliente->cpf_cnpj = $doc;

            if (isset($pedido->shipping_address)) {
                $address = $pedido->shipping_address;

                $telefone = $address ? ($address->phone ? $address->phone : $pedido->billing_phone) : '';

                if (substr($telefone, 0, 3) == '+55') {
                    $telefone = substr($telefone, 3, strlen($telefone));
                }

                $cidade = City::where('nome', $address->city)
                    ->first();

                $cliente->telefone = $telefone;
                $cliente->cep = $address->zipcode;
                $cliente->bairro = $address->locality;
                $cliente->numero = $address->number;
                $cliente->rua = $address->address;
                $cliente->cidade_id = $cidade == null ? 1 : $cidade->id;
                $cliente->save();
            }
        } catch (\Exception $e) {
            // echo $e->getMessage(). "<br>";
            // echo $e->getLine(). "<br>";
            // die;
        }
    }


    public function clientes()
    {
        $business_id = request()->session()->get('user.business_id');

        $clientes = Contact::where('business_id', $business_id)
            ->where('nuvemshop_id', '!=', '')
            // ->where('inativo', false)
            ->get();

        return view('nuvemshop_pedidos/clientes')
            ->with('clientes', $clientes)
            ->with('title', 'Clientes');
    }

    public function salvaCliente($p, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        $city_id = City::where('nome', '=', $p->billing_city)->first();
        $input = [
            'type' => 'customer',
            'cpf_cnpj' => $p->contact_identification,
            'ie_rg' => '',
            'contribuinte' => 0,
            'consumidor_final' => '1',
            'city_id' => null,
            'rua' => $p->billing_address,
            'numero' => $p->billing_number,
            'bairro' => $p->billing_locality,
            'cep' => $p->billing_zipcode,
            'name' => $p->contact_name,
            'mobile' => $p->billing_phone,
            'city' => $p->billing_city,
            'state' => $p->billing_province,
            'country' => $p->billing_country,
            'email' => $p->contact_email,
            'position' => null,
            'business_id' => $business_id,
            'is_default' => '0',
            'created_by' => $user_id,
            'city_id' => $city_id->id,
            'nuvemshop_id' => $id
        ];

        $c = Contact::where('nuvemshop_id', $id)
        ->where('business_id', $business_id)
        ->first();

        if($c != null) return $c;

        $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');

        if (empty($input['contact_id'])) {
            //Generate reference number
            $input['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
        }

        return Contact::create($input);
    }


    public function detalhar($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $pedido = NuvemShopPedido::where('pedido_id', $id)->first();

        $transaction = Transaction::where('nuvemshop_id', $pedido->pedido_id)->first();

        $pTemp = (array)$api->get("orders/" . $pedido->pedido_id);
        $pTemp = $pTemp['body'];
        // echo "<pre>";
        // print_r($pTemp);
        // echo "</pre>";
        // die;
        foreach ($pedido->itens as $i) {
            foreach ($pTemp->products as $iTemp) {
                if ($i->nome == $iTemp->name) {
                    $i->src = $iTemp->image->src;
                }
            }
        }
        $doc = $pedido->cliente->cpf_cnpj;
        $erros = [];
        if (strlen($doc) == 14 && str_contains($doc, ".")) {
            if (!$this->validaCPF($doc)) {
                array_push($erros, "CPF cliente inválido");
            }
        }
        if (strlen($doc) == 18) {
            if (!$this->validaCNPJ($doc)) {
                array_push($erros, "CNPJ cliente inválido");
            }
        }
        if ($pedido->cliente->cidade_id == 1) {
            array_push($erros, "Cidade cliente inválida");
        }
        return view('nuvemshop_pedidos/detalhes')
            ->with('pedido', $pedido)
            ->with('transaction', $transaction)
            ->with('erros', $erros)
            ->with('title', 'Pedido Nuvem Shop ' . $pedido->pedido_id);
    }

    public function gerarNFe($id)
    {
        // dd($id);
        $business_id = request()->session()->get('user.business_id');

        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $pedido = NuvemShopPedido::find($id);

        // dd($pedido);
        if ($pedido == null) {
            abort(403, 'Unauthorized action.');
        }

        $naturezas = NaturezaOperacao::where('business_id', $business_id)
            ->get();
        $transportadoras = Transportadora::where('business_id', $business_id)
            ->get();

        $cidades = City::all();

        $erros = [];

        $doc = $pedido->cliente->cpf_cnpj;

        $payment_types = $this->productUtil->payment_types();
        if (strlen($doc) == 11) {
            if (!$this->validaCPF($doc)) {
                array_push($erros, "CPF cliente inválido");
            }
        }
        if (strlen($doc) == 14) {
            if (!$this->validaCNPJ($doc)) {
                array_push($erros, "CNPJ cliente inválido");
            }
        }
        if ($pedido->cliente->cidade_id == 1) {
            array_push($erros, "Cidade cliente inválida");
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('nuvemshop_pedidos/emitir_nfe')
            ->with('pedido', $pedido)
            ->with('erros', $erros)
            ->with('cidade', $cidades)
            ->with('business_locations', $business_locations)
            ->with('naturezas', $naturezas)
            ->with('payment_types', $payment_types)
            ->with('transportadoras', $transportadoras);
    }

    private function validaCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function validaCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }


    public function salvarVenda(Request $request)
    {
        $pedido = NuvemShopPedido::find($request->id);
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $config = Business::where('id', $business_id)->first();

        $transportadora = $request->transportadora ?? NULL;
        $natureza = $request->natureza;
        $cliente = $this->salvarCliente($pedido);
        $tipoPagamento = '01';
        if ($pedido->forma_pagamento == 'Pix') {
            $tipoPagamento = '17';
        } elseif ($pedido->forma_pagamento == 'Boleto') {
            $tipoPagamento = '15';
        } else {
            $tipoPagamento = '03';
        }
        try {
            $dataVenda = [
                'business_id' => $business_id,
                'type' => 'sell',
                'status' => 'final',
                'is_direct_sale' => 1,
                'payment_status' => $tipoPagamento == 'boleto' ? 'due' : 'paid',
                'contact_id' => $cliente->id,
                'transaction_date' => date('Y-m-d H:i:s'),
                'created_by' => $user_id,
                'estado' => 'NOVO',
                'location_id' => $config->id,
                'final_total' => $pedido->total,
                'total_before_tax' => $pedido->total,
                'discount_amount' => 0,
                'discount_type' => NULL,
                'natureza_id' => $natureza,
                'tipo' => $request->frete,
                'valor_frete' => $request->valor_frete ?? 0,
                'placa' => $request->placa ?? '',
                'uf' => $request->uf_placa ?? '',
                'qtd_volumes' => $request->qtd_volumes ?? 0,
                'numeracao_volumes' => $request->numeracao_volumes ?? 0,
                'especie' => $request->especie ?? '',
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'nuvemshop_id' => $pedido->pedido_id,
            ];

            $venda = Transaction::create($dataVenda);

            foreach ($pedido->itens as $i) {
                // dd($i->produto->variations);
                $dataItemSell = [
                    'transaction_id' => $venda->id,
                    'product_id' => $i->produto->id,
                    'variation_id' => $i->variacao_id > 0 ? $i->variacao_id : $i->produto->variations[0]->id,
                    'quantity' => $i->quantidade,
                    'unit_price' => $i->variacao_id > 0 ? $i->variacao->default_sell_price : $i->produto->valor_ecommerce,
                    'unit_price_before_discount' => $i->valor,
                    'unit_price' => $i->valor,
                    'unit_price_inc_tax' => $i->valor
                ];

                $res = TransactionSellLine::create($dataItemSell);
            }
            // $dataFatura = [
            // 	'business_id' => $business_id,
            // 	'type' => 'expense',
            // 	'status' => 'final',
            // 	'payment_status' => 'due',
            // 	'contact_id' => $cliente->id,
            // 	'transaction_date' => date('Y-m-d'),
            // 	'created_by' => $user_id,
            // 	'numero_nfe_entrada' => '',
            // 	'chave' => '',
            // 	'estado' => '',
            // 	'location_id' => $request->location_id,
            // 	'ref_no' => 'PEDIDO_' . $pedido->id,
            // 	'final_total' => $pedido->valor_total,
            // 	'total_before_tax' => $pedido->valor_total,
            // 	'discount_amount' => 0,
            // 	'discount_type' => NULL
            // ];

            // $fatura = Transaction::create($dataFatura);

            $formaPagamento = '';

            if (strtolower($pedido->forma_pagamento) == 'boleto') {
                $formaPagamento = 'boleto';
            } else if (strtolower($pedido->forma_pagamento) == 'cartão') {
                $formaPagamento = 'card';
            } else {
                $formaPagamento = 'other';
            }

            $payment_data = [
                'amount' => $pedido->total,
                'method' => $formaPagamento,
                'business_id' => $business_id,
                'is_return' => 0,
                'transaction_id' => $venda->id,
                'vencimento' => date('Y-m-d'),
                'paid_on' => !empty($payment['paid_on']) ? $payment['paid_on'] : \Carbon::now()->toDateTimeString(),
                'created_by' => $user_id,
                'payment_for' => $cliente->id,
                'payment_ref_no' => 'PEDIDO_' . $pedido->id,
                'account_id' => null
            ];
            TransactionPayment::create($payment_data);

            $pedido->venda_id = $venda->id;
            $pedido->save();

            $output = [
                'success' => 1,
                'msg' => "Venda gerada"
                // 'msg' => __('messages.something_went_wrong')
            ];

            return redirect('sells')->with('status', $output);
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
                // 'msg' => __('messages.something_went_wrong')
            ];

            echo $e->getMessage();
            die;

            return redirect()->back()->with('status', $output);
        }
    }

    private function salvarCliente($pedido)
    {
        try {
            $cliente = $pedido->cliente;
            $endereco = $pedido->rua;
            $clienteExist = Contact::where('cpf_cnpj', $cliente->cpf_cnpj)->first();
            $cidade = City::where('nome', $cliente->cidade)->first();
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            if ($clienteExist == null) {
                $data = [
                    'type' => 'customer',
                    'supplier_business_name' => "$cliente->nome $cliente->sobre_nome",
                    'cpf_cnpj' => $cliente->cpf,
                    'ie_rg' => '',
                    'contribuinte' => 1,
                    'consumidor_final' => 1,
                    'rua' => $endereco->rua,
                    'numero' => $endereco->numero,
                    'bairro' => $endereco->bairro,
                    'cep' => $endereco->cep,
                    'name' => "$cliente->nome $cliente->sobre_nome",
                    'tax_number' => '',
                    'pay_term_number' => '',
                    'pay_term_type' => '',
                    'mobile' => '',
                    'landline' => '',
                    'alternate_number' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'landmark' => '',
                    'customer_group_id' => '',
                    'contact_id' => '',
                    'custom_field1' => '',
                    'custom_field2' => '',
                    'custom_field3' => '',
                    'custom_field4' => '',
                    'email' => $cliente->email,
                    'shipping_address' => '',
                    'position' => '',
                    'city_id' => $cidade->id,
                    'business_id' => $business_id,
                    'cod_pais' => '1058',
                    'id_estrangeiro' => '',
                    'created_by' => $user_id,
                ];
                // print_r($data);
                return Contact::create($data);
            } else {
                //atualiza endereço
                $clienteExist->rua = $cliente->rua;
                $clienteExist->numero = $cliente->numero;
                $clienteExist->bairro = $cliente->bairro;
                $clienteExist->cep = $cliente->cep;
                $clienteExist->city_id = $cliente->cidade->id;
                $clienteExist->save();
                return $clienteExist;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    public function imprimir($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');

        $config = Business::where('id', $business_id)->first();
        // dd($config->cidade);

        $pedido = NuvemShopPedido::find($id);

        $p = view('nuvemshop_pedidos.print', compact('pedido', 'config'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        // dd($domPdf);
        $domPdf->stream("Pedido Nuvem Shop $pedido->pedido_id.pdf");
    }

    public function verVenda($id)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['contact', 'sell_lines' => function ($q) {
                $q->whereNull('parent_sell_line_id');
            }, 'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff', 'types_of_service', 'sell_lines.warranties']);

        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
        }

        $payment_types = $this->transactionUtil->payment_types();

        $order_taxes = [];
        if (!empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $order_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        // $shipping_status_colors = $this->shipping_status_colors;
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

        // dd($sell);

        return view('nuvemshop_pedidos.venda', compact(
            'taxes',
            'sell',
            'payment_types',
            'order_taxes',
            'pos_settings',
            'shipping_statuses',
            // 'shipping_status_colors',
            'is_warranty_enabled'
        ));
    }

    public function removerPedido($id)
    {
        if (!auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $pedido = NuvemShopPedido::where('pedido_id', $id)->first();
        // dd($pedido->itens);
        try {
            $pedido->itens()->delete();
            $pedido->delete();

            $output = [
                'success' => true,
                'msg' => 'Registro removido'
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect()->back()->with('status', $output);
        // return redirect('cte')->with('status', $output);

    }
}
