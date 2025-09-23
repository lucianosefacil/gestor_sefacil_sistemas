<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'codigo', 'nome', 'cpf', 'celular', 'comissao', 'status'
    ];

    public static function forDropdown($business_id)
    {
        $query = Funcionario::where('business_id', $business_id)
            ->orderBy('nome', 'asc')
            ->get();

        $func = $query->pluck('nome', 'id');

        return $func;
    }
}
