<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientesExport;

class ExportarController extends Controller
{
    public function index()
    {
        return view('report.exportar');
    }

    public function produtos(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $query = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->join('units', 'products.unit_id', '=', 'units.id')
            ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
            ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
            ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
            ->join('variations as v', 'v.product_id', '=', 'products.id')
            ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->where('products.business_id', $business_id)
            ->where('products.type', '!=', 'modifier');

        $products = $query->select(
            'products.id',
            'products.name as product',
            'products.type',
            'c1.name as category',
            'c2.name as sub_category',
            'units.actual_name as unit',
            'brands.name as brand',
            'tax_rates.name as tax',
            'products.sku',
            'products.ncm',
            'products.cfop_interno',
            'products.cfop_externo',
            'products.cest',
            'products.image',
            'products.ecommerce',
            'products.perc_icms',
            'products.perc_pis',
            'products.perc_cofins',
            'products.perc_ipi',
            'products.pCredSN',
            'products.cfop_interno',
            'products.cfop_externo',
            'products.cst_csosn',
            'products.cst_pis',
            'products.cst_cofins',
            'products.cst_ipi',
            'products.ncm',
            'products.cest',
            'products.perc_glp',
            'products.perc_gnn',
            'products.valor_partida',
            'products.cenq_ipi',
            'products.cBenef',
            'products.valor_ecommerce',
            'products.altura',
            'products.largura',
            'products.comprimento',
            'products.perc_icms_interestadual',
            'products.perc_icms_interno',
            'products.perc_fcp_interestadual',
            'products.pICMSST',
            'products.modBC',
            'products.modBCST',
            'products.enable_stock',
            'products.is_inactive',
            'products.not_for_selling',
            'products.product_custom_field3',
            'products.product_custom_field4',
            'products.codigo_barras',
            'v.cod_barras',
            DB::raw('SUM(vld.qty_available) as estoque'),
            DB::raw('MIN(v.sell_price_inc_tax) as valor_venda'),
            DB::raw('MIN(v.dpp_inc_tax) as valor_compra')
        )->groupBy('products.id');

        $prod = $products->get();

        $nomeArquivo = tempnam(sys_get_temp_dir(), 'csv_' );
        $arquivo = fopen($nomeArquivo, 'w');
        $cabecalho = ['id', 'product', 'type', 'category', 'sub_category', 'brand', 'tax', 'sku', 
            'ncm', 'cfop_interno', 'cfop_externo', 'cest', 'image', 'ecommerce', 
            'perc_icms', 'perc_pis', 'perc_cofins', 'perc_ipi', 'pCredSN', 
            'cst_csosn', 'cst_pis', 'cst_cofins', 'cst_ipi', 'perc_glp', 
            'perc_gnn', 'valor_partida', 'cenq_ipi', 'cBenef', 'valor_ecommerce', 
            'altura', 'largura', 'comprimento', 'perc_icms_interestadual', 
            'perc_icms_interno', 'perc_fcp_interestadual', 'pICMSST', 
            'modBC', 'modBCST', 'enable_stock', 'is_inactive', 
            'not_for_selling', 'product_custom_field3', 'product_custom_field4', 
            'cod_barras', 'codigo_barras', 'estoque', 'valor_venda', 'valor_compra'];
        fputcsv($arquivo, $cabecalho, ';');

        foreach ($prod as $item) {
            $temp = [
                $item->id,
                $item->product,
                $item->type,
                $item->category,
                $item->sub_category,
                $item->brand,
                $item->tax,
                $item->sku,
                $item->ncm,
                $item->cfop_interno,
                $item->cfop_externo,
                $item->cest,
                $item->image,
                $item->ecommerce,
                $item->perc_icms,
                $item->perc_pis,
                $item->perc_cofins,
                $item->perc_ipi,
                $item->pCredSN,
                $item->cst_csosn,
                $item->cst_pis,
                $item->cst_cofins,
                $item->cst_ipi,
                $item->perc_glp,
                $item->perc_gnn,
                $item->valor_partida,
                $item->cenq_ipi,
                $item->cBenef,
                $item->valor_ecommerce,
                $item->altura,
                $item->largura,
                $item->comprimento,
                $item->perc_icms_interestadual,
                $item->perc_icms_interno,
                $item->perc_fcp_interestadual,
                $item->pICMSST,
                $item->modBC,
                $item->modBCST,
                $item->enable_stock,
                $item->is_inactive,
                $item->not_for_selling,
                $item->product_custom_field3,
                $item->product_custom_field4,
                $item->cod_barras,
                $item->codigo_barras,
                $item->estoque,
                $item->valor_venda,
                $item->valor_compra,
            ];
            fputcsv($arquivo, $temp, ';');
        }

        fclose($arquivo);

        return response()->download($nomeArquivo, 'Produtos' . date('d-m-Y H:i:s') . '.csv'); 

    }

    public function clientes()
    {
        $business_id = request()->user()->business_id;
        $clientes = Contact::where('business_id', $business_id)->where('type', 'customer')->get();

        $nomeArquivo = tempnam(sys_get_temp_dir(), 'csv_' );
        $arquivo = fopen($nomeArquivo, 'w');
        $cabecalho = ['Nome', 'CPF_CNPJ', 'RG_IE', 'Rua', 'Numero', 'Bairro', 'Cep', 'Email', 'Celular', 'Telefone', 'Cidade', 'Cidade_id', 'Codigo'];
        fputcsv($arquivo, $cabecalho, ';');

        foreach($clientes as $contact){
            $oArray = [
                'nome' => $contact->name,
                'cpf_cnpj' => $contact->cpf_cnpj,
                'ie_rg' => $contact->ie_rg,
                'rua' => $contact->rua,
                'numero' => $contact->numero,
                'bairro' => $contact->bairro,
                'cep' => $contact->cep,
                'email' => $contact->email,
                'celular' => $contact->mobile,
                'telefone' => $contact->landline,
                'cidade' => $contact->cidade ? $contact->cidade->nome : null,
                'cidade_id' => $contact->city_id,
                'contact_id' => $contact->contact_id
            ];
            fputcsv($arquivo, $oArray, ';');
        }
        fclose($arquivo);
        return response()->download($nomeArquivo, 'Clientes' . date('d-m-Y H:i:s') . '.csv'); 
    }
}
