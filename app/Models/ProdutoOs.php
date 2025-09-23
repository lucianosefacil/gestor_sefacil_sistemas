<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoOs extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id', 'ordem_servico_id', 'quantidade', 'valor_unitario', 'sub_total', 'variation_id'
    ];

    public function warranties()
    {
        return $this->belongsToMany('App\Models\Warranty', 'sell_line_warranties', 'sell_line_id', 'warranty_id');
    }

    public function produto(){
        return $this->belongsTo(Product::class, 'produto_id');
    }
}
