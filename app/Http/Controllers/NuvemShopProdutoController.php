<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\PurchaseLine;
use App\Models\SellingPriceGroup;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\Variation;
use App\Models\VariationLocationDetails;
use App\Models\VariationTemplate;
use App\Models\VariationValueTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Testing\Fakes\BusFake;

class NuvemShopProdutoController extends Controller
{
    protected $productUtil;
    protected $moduleUtil;
    private $barcode_types;
    protected $transactionUtil;


    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil,  TransactionUtil $transactionUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    public function index(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $search = $request->search;
        $store_info = session('store_info');
        if (!$store_info) {
            return redirect('/nuvemshop');
        }
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');

        if ($search != "") {
            $produtos = (array)$api->get("products?q='" . $search . "'&per_page=21");
        } else {
            $produtos = (array)$api->get("products?page=" . $page . "&per_page=12");
        }
        $produtos = $produtos['body'];

        $this->validaProdutos($produtos);

        return view('nuvemshop.produtos')
            ->with('produtos', $produtos)
            ->with('page', $page)
            ->with('search', $search)
            ->with('title', 'Produtos');
    }


    public function produto_new()
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        // echo "<pre>";
        // print_r($produto);
        // echo "</pre>";
        // die;
        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];
        return view('nuvemshop/produtos_form')
            ->with('categorias', $categorias)
            ->with('contratoJs', true)
            ->with('title', 'Novo Produto');
    }

    private function validaProdutos($produtos)
    {
        $business_id = request()->session()->get('user.business_id');

        foreach ($produtos as $p) {
            // echo "<pre>";
            // print_r($p);
            // echo "</pre>";
            $rand = Str::random(20);
            if (sizeof($p->variants) > 1) {

                $ean = $p->variants[0]->barcode;
                
                $result = Product::where('codigo_barras', $ean)
                    ->where('codigo_barras', '!=', 'SEM GTIN')
                    ->where('business_id', $business_id)
                    ->where('nuvemshop_id', $p->variants[0]->product_id)
                    ->first();

                if ($result == null) {
                    $str = "";
                    foreach ($p->variants[0]->values as $s) {
                        $str .= $s->pt . " ";
                    }
                    $result = Product::where('business_id', $business_id)
                        ->where('nuvemshop_id', $p->variants[0]->product_id)
                        ->first();
                }
                if ($result == null) {
                    $this->salvarProdutoBanco2($p, $p->variants[0], $rand, $str);
                }
                //teste
                // $this->salvarProdutoBanco2($p, $p->variants[0], $rand, $str);
            } else {
                $result = Product::where('business_id', $business_id)
                    ->where('nuvemshop_id', $p->id)
                    ->first();
                if ($result == null) {
                    $ean = $p->variants[0]->barcode;
                    $result = Product::where('codigo_barras', $ean)
                        ->where('codigo_barras', '!=', 'SEM GTIN')
                        ->where('business_id', $business_id)
                        ->where('nuvemshop_id', $p->variants[0]->product_id)
                        ->first();
                }
                if ($result == null) {

                    $this->salvarProdutoBanco2($p, null, $rand);
                }
            }
        }
    }

    public function produto_galeria($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $produto = (array)$api->get("products/" . $id);
        $produto = $produto['body'];

        $prodBd = Product::where('nuvemshop_id', $produto->id)
            ->first();

        return view('nuvemshop/produtos_galery')
            ->with('produto', $produto)
            ->with('prodBd', $prodBd)
            ->with('title', 'Galeria do Produto');
    }

    public function save_imagem(Request $request)
    {
        // dd($request);
        if ($request->hasFile('file')) {
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');

            $image = base64_encode(file_get_contents($request->file('file')->path()));

            $ext = $request->file('file')->getClientOriginalExtension();
            $response = $api->post("products/$request->id/images", [
                "filename" => Str::random(20) . "." . $ext,
                "attachment" => $image
            ]);

            $output = [
                'success' => 1,
                'msg' => "Imagem Salva"
            ];
        } else {
            $output = [
                'success' => 0,
                'msg' => "Algo deu errado"
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function delete_imagem($produto_id, $image_id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        try {
            $response = $api->delete("products/$produto_id/images/$image_id");

            $output = [
                'success' => 1,
                'msg' => "Imagem Deletada"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => "Algo deu errado"
            ];
        }
        return redirect()->back()->with('status', $output);
    }


    private function salvarProdutoBanco2($prod, $variants = null, $rand)
    {
        // dd($prod);
        if (!auth()->user()->can('product.opening_stock')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $config = Business::where('id', $business_id)->first();
        $unit = Unit::where('business_id', $business_id)->first();
        if ($prod->categories == 1) {
            $categoria = Category::where('nuvemshop_id', $prod->categories[0]->id)->first();
        }
        try {
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
                'name' => $prod->name->pt,
                'category_id' => $categoria->id ?? null,
                'largura' => $prod->variants[0]->width ?? '',
                'comprimento' => $prod->variants[0]->depth ?? '',
                'altura' => $prod->variants[0]->height ?? '',
                'pesoL' => $prod->variants[0]->weight ?? '',
                'pesoB' => $prod->variants[0]->weight ?? '',
                'weight' => $prod->variants[0]->weight ?? '',
                'sku' => $prod->variants[0]->sku ?? '',
                'business_id' => $business_id,
                'created_by' => $user_id,
                'unit_id' => $unit->id,
                'cenq_ipi' => '999',
                'tax_type' => 'exclusive',
                'enable_stock' => '1',
                'type' => 'opening_stock',
                'nuvemshop_id' => $prod->variants[0]->product_id
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


            $enable_product_editing = request()->session()->get('business.enable_editing_product_from_purchase');
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            $transaction_data = [
                'ref_no' => null,
                'status' => 'received',
                'contact_id' => null,
                'transaction_date' => date('Y-m-d'),
                'total_before_tax' => number_format($prod->variants[0]->price * $prod->variants[0]->stock),
                'location_id' => $config->id,
                'discount_type' => null,
                'discount_amount' => 0,
                'tax_id' => null,
                'tax_amount' => 0,
                'shipping_details' => null,
                'shipping_charges' => 0,
                'final_total' => number_format($prod->variants[0]->price * $prod->variants[0]->stock),
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

            if (sizeof($prod->variants) <= 1) {
                if ($product->sku != null) {
                    $product->sku = $product->sku;
                } else {
                    $product->sku = $this->productUtil->generateProductSku($product->id);
                }
                $product->type = 'single';
                $product->enable_stock = 1;
                $product->save();
                $this->productUtil->createSingleProductVariation(
                    $product->id,
                    $product->sku,
                    $prod->variants[0]->cost,
                    $prod->variants[0]->price,
                    $profit_percent,
                    $prod->variants[0]->price,
                    $prod->variants[0]->price,
                );

                $variation = Variation::where('product_id', '=', $product->id)->first();

                $qtd = $prod->variants[0]->stock ? $prod->variants[0]->stock : 0;

                $this->productUtil->updateProductQuantity($config->id, $product->id, $variation->id, $qtd, 0, null, false);

                $valorCompra = 0;
                $purchase_line = new PurchaseLine();
                $purchase_line->product_id = $product->id;

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
            } else {
                //variacao aqui
                $product->type = 'variable';
                $product->save();

                $variation_template = VariationTemplate::updateOrCreate([
                    'name' => $prod->attributes[0]->pt,
                    'business_id' => $business_id
                ]);

                $product_variation_data = [
                    'name' => $prod->attributes[0]->pt,
                    'product_id' => $product->id,
                    'is_dummy' => 0,
                    'variation_template_id' => $variation_template->id
                ];
                $product_variation = ProductVariation::create($product_variation_data);

                $variants = $prod->variants;

                for ($i = 0; $i < sizeof($variants); $i++) {
                    $variation_value =  VariationValueTemplate::create([
                        'name' => $variants[$i]->values[0]->pt,
                        'variation_template_id' => $variation_template->id
                    ]);
                }

                if ($product->sku != null) {
                    $product->sku = $product->sku;
                } else {
                    $product->sku = $this->productUtil->generateProductSku($product->id);
                }

                $product->save();

                for ($i = 0; $i < sizeof($variants); $i++) {
                    $variation_data = [
                        'name' => $variants[$i]->values[0]->pt,
                        'variation_value_id' => $variation_value,
                        'product_id' => $product->id,
                        'sub_sku' => $product->sku,
                        'cod_barras' => $prod->barcode ?? null,
                        'default_purchase_price' => $variants[$i]->cost ?? 0,
                        'dpp_inc_tax' => $prod->variants[$i]->cost ?? 0,
                        'profit_percent' => '',
                        'default_sell_price' =>  $prod->variants[$i]->price,
                        'sell_price_inc_tax' =>  $prod->variants[$i]->price,
                        'product_variation_id' => $product_variation->id
                    ];
                    $v = Variation::create($variation_data);

                    VariationLocationDetails::create([
                        'product_id' => $product->id,
                        'location_id' => $config->id,
                        'variation_id' => $v->id,
                        'product_variation_id' => $product_variation->id,
                        'qty_available' => $variants[$i]->stock ?? 0
                    ]);
                }

                // dd($variations);

                // for ($i = 0; $i < sizeof($variants); $i++) {

                    
                // }
            }


            DB::beginTransaction();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('product.product_added_success')
            ];
        } catch (\Exception $e) {
            print_r($e->getMessage());
            die();
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
            return redirect('products')->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    public function saveProduto(Request $request)
    {
        $nome = $request->nome;
        $descricao = $request->descricao;
        $valor = $request->valor;
        $id = $request->id;
        $categoria_id = $request->categoria_id;
        $estoque = $request->estoque;
        $valor_promocional = $request->valor_promocional ?? 0;
        $codigo_barras = $request->codigo_barras ?? '';
        $peso = $request->peso ? $request->peso : 0;
        $largura = $request->largura ? $request->largura : 0;
        $altura = $request->altura ? $request->altura : 0;
        $comprimento = $request->comprimento ? $request->comprimento : 0;
        // $this->_validate($request);
        try {
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
            if ($id > 0) {
                $response = $api->put("products/$id", [
                    'name' => $nome,
                    'description' => $descricao,
                    'categories' => $categoria_id ? [$categoria_id] : []
                ]);
                $produto = (array)$api->get("products/" . $id);
                $produto = $produto['body'];
                if (sizeof($produto->variants) == 1) {
                    $resp = $response = $api->put("products/$id/variants/" . $produto->variants[0]->id, [
                        'price' => $this->__convert_value_bd($valor),
                        'stock' => $this->__convert_value_bd($estoque),
                        'promotional_price' => $this->__convert_value_bd($valor_promocional),
                        'barcode' => $this->__convert_value_bd($codigo_barras),
                        "weight" => $this->__convert_value_bd($peso),
                        "width" => $largura,
                        "height" => $altura,
                        "depth" => $comprimento,
                    ]);
                }
                $prodBd = Product::where('nuvemshop_id', $request->id)
                    ->first();
                if ($prodBd == null) {
                    $this->salvarProdutoBanco($request, $request->id);
                } else {
                    $this->atualizarProdutoBanco($request, $request->id);
                }
                if ($response) {
                    $output = [
                        'success' => 1,
                        'msg' => "Produto Alterado"
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => "Algo deu errado"
                    ];
                }
            } else {
                // dd($request);
                $response = $api->post("products", [
                    'name' => $nome,
                    'parent' => $categoria_id,
                    'description' => $descricao
                ]);
                $produto = $response->body;
                $resp = $response = $api->put("products/$produto->id/variants/" . $produto->variants[0]->id, [
                    'price' => $this->__convert_value_bd($valor),
                    'stock' => $this->__convert_value_bd($estoque),
                    'promotional_price' => $this->__convert_value_bd($valor_promocional),
                    'barcode' => $this->__convert_value_bd($codigo_barras),
                    "weight" => $peso,
                    "width" => $largura,
                    "height" => $altura,
                    "depth" => $comprimento,
                ]);
                $this->salvarProdutoBanco($request, $produto->id);
                if ($response) {
                    $output = [
                        'success' => 1,
                        'msg' => "Produto Criado"
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => "Algo deu errado"
                    ];
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => 0,
                'msg' => "Algo deu errado"
            ];
        }
        return redirect('/nuvemshop/produtos')->with('status', $output);
    }

    private function __convert_value_bd($valor)
    {
        if (strlen($valor) >= 8) {
            $valor = str_replace(".", "", $valor);
        }
        $valor = str_replace(",", ".", $valor);
        return $valor;
    }

    private function _validate(Request $request)
    {
        $rules = [
            'referencia' => 'required',
            'nome' => 'required',
            'descricao' => 'required',
            'valor' => 'required',
        ];
        $messages = [
            'referencia.required' => 'O campo referência é obrigatório.',
            'descricao.required' => 'O campo descricao é obrigatório.',
            'nome.required' => 'O campo nome é obrigatório.',
            'valor.required' => 'O campo valor é obrigatório.',
            'estoque.required' => 'O campo estoque é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    private function salvarProdutoBanco($request, $nuvemshop_id)
    {
        // dd($request);
        if ($request->produto_id == 0) {
            $business_id = request()->session()->get('user.business_id');
            if ($request->categoria_id != null) {
                $categoria = Category::where('nuvemshop_id', $request->categoria_id)->first();
            }
            $user_id = request()->session()->get('user.id');
            $config = Business::where('id', $business_id)->first();
            $unit = Unit::where('business_id', $business_id)->first();
            $data = [
                'codigo_barras' => $request->variants[0]->barcode ?? '',
                'codigo_anp' => $request->codigo_anp ?? '',
                'perc_glp' => $request->perc_glp ?? 0,
                'perc_gnn' => $request->perc_gnn ?? 0,
                'perc_gni' => $request->perc_gni ?? 0,
                'valor_partida' => $request->valor_partida ?? 0,
                'unidade_tributavel' => $request->unidade_tributavel ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
                'veicProd' => $request->veicProd ?? '',
                'tpOp' => $request->tpOp ?? '',
                'chassi' => $request->chassi ?? '',
                'cCor' => $request->cCor ?? '',
                'xCor' => $request->xCor ?? '',
                'pot' => $request->pot ?? 0,
                'cilin' => $request->cilin ?? 0,
                'pesoL' => $request->pesoL ?? '',
                'pesoB' => $request->pesoB ?? '',
                'nSerie' => $request->nSerie ?? '',
                'tpComb' => $request->tpComb ?? '',
                'nMotor' => $request->nMotor ?? '',
                'CMT' => $request->CMT ?? '',
                'dist' => $request->dist ?? '',
                'anoMod' => $request->anoMod ?? '',
                'anoFab' => $request->anoFab ?? '',
                'tpPint' => $request->tpPint ?? '',
                'tpVeic' => $request->tpVeic ?? '',
                'espVeic' => $request->espVeic ?? '',
                'VIN' => $request->VIN ?? '',
                'condVeic' => $request->condVeic ?? '',
                'cMod' => $request->cMod ?? '',
                'cCorDENATRAN' => $request->cCorDENATRAN ?? '',
                'lota' => $request->lota ?? '',
                'tpRest' => $request->tpRest ?? '',
                'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                'pCredSN' => $request->pCredSN ?? 0,
                'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                'pICMSST' => $request->pICMSST ?? 0,
                'cBenef' => $request->cBenef ?? '',
                'category_id' => $categoria->id ?? null,
                'largura' => $request->largura ?? '',
                'comprimento' => $request->comprimento ?? '',
                'altura' => $request->altura ?? '',
                'pesoL' => $request->peso ?? '',
                'pesoB' => $request->peso ?? '',
                'weight' => $request->weight ?? '',
                'sku' => $request->referencia ?? '',
                'business_id' => $business_id,
                'created_by' => $user_id,
                'unit_id' => $unit->id,
                'cenq_ipi' => '999',
                'tax_type' => 'exclusive',
                'enable_stock' => '1',
                'type' => 'opening_stock',
                'name' => $request->nome,
                'nuvemshop_id' => $nuvemshop_id
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
            // dd($product);
            $product_locations = $config;
            if (!empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }
            $profit_percent = $config->default_profit_percent;

            if ($product->referencia != null) {
                $product->sku = $$request->referencia;
            } else {
                $product->sku = $this->productUtil->generateProductSku($product->id);
            }
            $product->type = 'single';
            $product->enable_stock = 1;
            $product->save();
            $this->productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                $request->cost,
                $request->valor,
                $profit_percent,
                $request->valor,
                $request->valor,
            );

            $transaction_data = [
                'ref_no' => null,
                'status' => 'received',
                'contact_id' => null,
                'transaction_date' => date('Y-m-d'),
                'total_before_tax' => $this->__convert_value_bd($request->valor) * $this->__convert_value_bd($request->estoque),
                'location_id' => $config->id,
                'discount_type' => null,
                'discount_amount' => 0,
                'tax_id' => null,
                'tax_amount' => 0,
                'shipping_details' => null,
                'shipping_charges' => 0,
                'final_total' => $this->__convert_value_bd($request->valor) * $this->__convert_value_bd($request->estoque),
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
            $qtd = $request->estoque;
            $purchase_line = new PurchaseLine();
            $purchase_line->product_id = $product->id;
            // $purchase_line->variation_id = $k;
            $valorCompra = 0;
            $this->productUtil->updateProductQuantity($config->id, $product->id, $variation->id, $qtd, 0, null, false);

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

            DB::beginTransaction();

            DB::commit();
        } else {
            $produto = Product::find($request->produto_id);
            $produto->nuvemshop_id = $nuvemshop_id;
            $produto->save();
        }
    }

    private function atualizarProdutoBanco($request, $nuvemshop_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $categoria = Category::where('business_id', $business_id)->first();
        $user_id = request()->session()->get('user.id');
        $config = Business::where('id', $business_id)->first();
        $unit = Unit::where('business_id', $business_id)->first();

        $product = Product::where('business_id', $business_id)->where('nuvemshop_id', $nuvemshop_id)
            ->with(['product_variations'])
            ->first();

        if ($request->categoria_id != null) {
            $categoria = Category::where('nuvemshop_id', $request->categoria_id)->first();
        }

        $product->codigo_barras = $request->variants[0]->barcode ?? '';
        $product->codigo_anp = $request->codigo_anp ?? '';
        $product->perc_glp = $request->perc_glp ?? 0;
        $product->perc_gnn = $request->perc_gnn ?? 0;
        $product->perc_gni = $request->perc_gni ?? 0;
        $product->valor_partida = $request->valor_partida ?? 0;
        $product->unidade_tributavel = $request->unidade_tributavel ?? '';
        $product->quantidade_tributavel = $request->quantidade_tributavel ?? 0;
        $product->veicProd = $request->veicProd ?? '';
        $product->tpOp = $request->tpOp ?? '';
        $product->chassi = $request->chassi ?? '';
        $product->cCor = $request->cCor ?? '';
        $product->xCor = $request->xCor ?? '';
        $product->pot = $request->pot ?? 0;
        $product->cilin = $request->cilin ?? 0;
        $product->pesoL = $request->pesoL ?? '';
        $product->pesoB = $request->pesoB ?? '';
        $product->nSerie = $request->nSerie ?? '';
        $product->tpComb = $request->tpComb ?? '';
        $product->nMotor = $request->nMotor ?? '';
        $product->CMT = $request->CMT ?? '';
        $product->dist = $request->dist ?? '';
        $product->anoMod = $request->anoMod ?? '';
        $product->anoFab = $request->anoFab ?? '';
        $product->tpPint = $request->tpPint ?? '';
        $product->tpVeic = $request->tpVeic ?? '';
        $product->espVeic = $request->espVeic ?? '';
        $product->VIN = $request->VIN ?? '';
        $product->condVeic = $request->condVeic ?? '';
        $product->cMod = $request->cMod ?? '';
        $product->cCorDENATRAN = $request->cCorDENATRAN ?? '';
        $product->lota = $request->lota ?? '';
        $product->tpRest = $request->tpRest ?? '';
        $product->perc_icms_interestadual = $request->perc_icms_interestadual ?? 0;
        $product->perc_icms_interno = $request->perc_icms_interno ?? 0;
        $product->pCredSN = $request->pCredSN ?? 0;
        $product->perc_fcp_interestadual = $request->perc_fcp_interestadual ?? 0;
        $product->pICMSST = $request->pICMSST ?? 0;
        $product->cBenef = $request->cBenef ?? '';
        $product->category_id = $categoria->id ?? null;
        $product->largura = $request->largura ?? '';
        $product->comprimento = $request->comprimento ?? '';
        $product->altura = $request->altura ?? '';
        $product->pesoL = $request->peso ?? '';
        $product->pesoB = $request->peso ?? '';
        $product->weight = $request->weight ?? '';
        $product->sku = $product->sku ?? '';
        $product->business_id = $business_id;
        $product->created_by = $user_id;
        $product->unit_id = $unit->id;
        $product->cenq_ipi = '999';
        $product->tax_type = 'exclusive';
        $product->enable_stock = '1';
        $product->type = 'single';
        $product->name = $request->nome;
        $product->category_id = $categoria->id ?? null;

        $form_fields = [
            'name', 'brand_id', 'unit_id', 'category_id', 'tax', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description', 'sub_unit_ids', 'perc_icms', 'perc_cofins', 'perc_pis', 'perc_ipi', 'cfop_interno', 'cfop_externo', 'cst_csosn', 'cst_pis', 'cst_cofins', 'cst_ipi', 'ncm', 'cest', 'codigo_barras', 'codigo_anp', 'perc_glp', 'perc_gnn', 'perc_gni', 'valor_partida', 'unidade_tributavel', 'quantidade_tributavel', 'tipo', 'veicProd', 'tpOp', 'chassi', 'cCor', 'xCor', 'pot', 'cilin', 'pesoL', 'pesoB', 'nSerie', 'tpComb', 'nMotor', 'CMT', 'dist', 'anoMod', 'anoFab', 'tpPint', 'tpVeic', 'espVeic', 'VIN', 'condVeic', 'cMod', 'cCorDENATRAN', 'lota', 'tpRest', 'ecommerce', 'destaque', 'novo', 'altura', 'largura', 'comprimento', 'valor_ecommerce', 'origem', 'cenq_ipi',
            'perc_icms_interestadual', 'perc_icms_interno', 'perc_fcp_interestadual', 'pICMSST', 'modBC', 'modBCST', 'pCredSN', 'cBenef'
        ];

        $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
        if (!empty($module_form_fields)) {
            $form_fields = array_merge($form_fields, $module_form_fields);
        }

        $product_locations = $config;
        if (!empty($product_locations)) {
            $product->product_locations()->sync($product_locations);
        }
        $profit_percent = $config->default_profit_percent;

        // atualizar variacoes do produto
        $var = Variation::where('product_id', $product->id)->first();

        $var->sub_sku = $product->sku;
        $var->dpp_inc_tax = $var->dpp_inc_tax;
        $var->profit_percent = $var->profit_percent;
        $var->default_sell_price = $request->valor;
        $var->sell_price_inc_tax = $request->valor;
        $var->save();

        $product->save();
        $product->touch();

        // atualizar variacoes de localizacao e detalhes do produto 
        $qtd = $request->estoque;

        $variation_location_d = VariationLocationDetails::where('variation_id', $var->id)
            ->where('product_id', $product->id)
            ->where('product_variation_id', $var->product_variation_id)
            ->first();

        if ($variation_location_d == null) {
            $this->productUtil->updateProductQuantity($config->id, $product->id, $var->id, $qtd, 0, null, false);
        } else {
            $variation_location_d->qty_available = $qtd;
            $variation_location_d->save();
        }

        // atualizar purchase line de produto 
        $transaction = Transaction::where('opening_stock_product_id', $product->id)->first();

        $dataItemPurchase = PurchaseLine::where('variation_id', $var->id)
            ->where('product_id', $product->id)->where('transaction_id', $transaction->id)->first();

        $qtd_vendida = $dataItemPurchase->quantity_sold;

        $dataItemPurchase->quantity_sold = $qtd_vendida;
        $dataItemPurchase->quantity = $qtd + $qtd_vendida;

        $dataItemPurchase->save();

        DB::commit();
    }

    public function produto_delete($id)
    {
        // dd($id);
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        try {
            $response = $api->delete("products/$id");

            $prodBd = Product::where('nuvemshop_id', $id)
                ->first();
            // dd($prodBd->id);
            DB::beginTransaction();
            //Delete variation location details
            VariationLocationDetails::where('product_id', $prodBd->id)
                ->delete();
            $prodBd->delete();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('Produto removido!')
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => 1,
                'msg' => __('Produto removido!')
            ];
        }
        return redirect('/nuvemshop/produtos')->with(['status' => $output]);
    }

    public function produto_edit($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $produto = (array)$api->get("products/" . $id);
        $produto = $produto['body'];

        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];

        $prodBd = Product::where('nuvemshop_id', $produto->id)
            ->first();

        return view('nuvemshop/produtos_form')
            ->with('produto', $produto)
            ->with('prodBd', $prodBd)
            ->with('categorias', $categorias)
            ->with('contratoJs', true)
            ->with('title', 'Editar Produto');
    }
}
