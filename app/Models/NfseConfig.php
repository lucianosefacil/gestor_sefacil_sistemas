<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class NfseConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'razao_social', 'documento', 'regime', 'ie', 'im', 'cnae', 'login_prefeitura',
        'senha_prefeitura', 'telefone', 'email', 'rua', 'numero', 'bairro', 'complemento', 'cep',
        'logo', 'cidade_id', 'empresa_id', 'token', 'codigo_tributacao_municipio', 'item_lc'
    ];

    public function cidade(){
        return $this->belongsTo(City::class, 'cidade_id');
    }
}
