<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSellService extends Model
{
  use HasFactory;

  protected $fillable = [
    'transaction_id', 'servico_id', 'quantity', 'valor_unitario', 'sub_total', 'ordem_servico_id'
  ];

  public function servicos()
  {
    return $this->hasMany(ServicoOs::class, 'ordem_servico_id', 'id');
  }

  public function servico()
  {
    return $this->belongsTo(Servico::class, 'servico_id');
  }
}
