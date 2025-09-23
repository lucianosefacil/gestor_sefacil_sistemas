<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'location_id', 'cliente_id', 'service_type', 'pick_up_on_site_addr', 'descricao', 'valor', 'status_id',
        'data_entrega', 'nfe_id', 'observacao', 'funcionario_id', 'veiculo_id'
    ];

    public function servicos()
    {
        return $this->hasMany(ServicoOs::class, 'ordem_servico_id', 'id');
    }

    public function itens()
    {
        return $this->hasMany(ProdutoOs::class, 'ordem_servico_id', 'id');
    }

    public function relatorios()
    {
        return $this->hasMany(RelatorioOs::class, 'ordem_servico_id', 'id');
    }

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class, 'ordem_servico_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Contact::class, 'cliente_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function status()
    {
        return $this->belongsTo('Modules\Repair\Entities\RepairStatus', 'status_id');
    }

    public function businessLocation()
    {
        return $this->belongsTo('App\Models\BusinessLocation', 'location_id');
    }

    public function product_locations()
    {
        return $this->belongsToMany(\App\Models\BusinessLocation::class, 'product_locations', 'product_id', 'location_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    // public function getPartsUsed()
    // {
    //     $parts = [];
    //     if (!empty($this->parts)) {
    //         $variation_ids = [];
    //         $job_sheet_parts = $this->parts;
    //         foreach($job_sheet_parts as $key => $value) {
    //             $variation_ids[] = $key;
    //         } 

    //         $variations = Variation::whereIn('id', $variation_ids)
    //                             ->with(['product_variation', 'product', 'product.unit'])  
    //                             ->get();

    //         foreach ($variations as $variation) {
    //             $parts[$variation->id]['variation_id'] = $variation->id;
    //             $parts[$variation->id]['variation_name'] = $variation->full_name;
    //             $parts[$variation->id]['unit'] = $variation->product->unit->short_name;
    //             $parts[$variation->id]['unit_id'] = $variation->product->unit->id;
    //             $parts[$variation->id]['quantity'] = $job_sheet_parts[$variation->id]['quantity'];
    //         }
    //     }

    //     return $parts;
    // }
}
