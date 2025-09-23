<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoSku extends Model
{
    use HasFactory;

    protected $fillable = [ 'product_id', 'produto_referenciado', 'sub_sku', 'cod_barras', 'variation_id'];


    public function produto(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}