<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;
use App\Models\City;
use App\Models\Business;

class Nfse extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'filial_id',
        'valor_total',
        'estado',
        'serie',
        'codigo_verificacao',
        'numero_nfse',
        'url_xml',
        'url_pdf_nfse',
        'url_pdf_rps',
        'cliente_id',
        'documento',
        'razao_social',
        'im',
        'ie',
        'cep',
        'rua',
        'numero',
        'bairro',
        'complemento',
        'cidade_id',
        'email',
        'telefone',
        'natureza_operacao',
        'uuid',
        'nome_fantasia',

        'cancelado_em',
        'cancelamento_codigo',
        'cancelamento_mensagem',
        'cancelamento_data_evento',
        'cancelamento_xml_path',
        'cancelamento_pdf_path',
        'cancelamento_log_path',
    ];

    protected $casts = [
        'valor_total'             => 'decimal:7',
        'cancelado_em'            => 'datetime',
        'cancelamento_data_evento'=> 'datetime',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    public static function lastNfse()
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];

        $nfse = Nfse::where('numero_nfse', '!=', 0)
            ->where('empresa_id', $empresa_id)
            ->orderBy('numero_nfse', 'desc')
            ->first();
        return $nfse != null ? $nfse->numero_nfse : 1;
    }

    public function servico()
    {
        return $this->hasOne(NfseServico::class, 'nfse_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Contact::class, 'cliente_id');
    }

    public function cidade()
    {
        return $this->belongsTo(City::class, 'cidade_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Business::class, 'empresa_id');
    }

    public static function exigibilidades()
    {
        return [
            1 => 'Exígivel',
            2 => 'Não incidência',
            3 => 'Isenção',
            4 => 'Exportação',
            5 => 'Imunidade',
            6 => 'Exigibilidade Suspensa por Decisão Judicial',
            7 => 'Exigibilidade Suspensa por Processo Administrativo',
        ];
    }
}
