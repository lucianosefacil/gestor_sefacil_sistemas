<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locacao extends Model
{
    use HasFactory;

    protected $table = 'locacaos';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'transaction_id',
        'data_abertura',
        'valor',
        'status',
        'dias_em_locacao',
        'valor_total',
        'dias_excedentes',
        'dias_total',
        'produto_id'
    ];

    /**
     * Relacionamento: A locação pertence a uma transação.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    /**
     * Define se o status da locação está "aberto".
     */
    public function isAberto()
    {
        return $this->status === 'aberto';
    }
}
