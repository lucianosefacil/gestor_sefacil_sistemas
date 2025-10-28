<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TefTransaction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tef_data_hora' => 'datetime',
        'tef_processado' => 'boolean',
        'tef_valor' => 'decimal:4',
    ];

    /**
     * Relacionamento com a transação principal
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Models\Transaction::class);
    }

    /**
     * Relacionamento com a transação do cash register
     */
    public function cashRegisterTransaction()
    {
        return $this->belongsTo(\App\Models\CashRegisterTransaction::class);
    }
}