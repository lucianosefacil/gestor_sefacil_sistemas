<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class TefService
{
    protected $client;
    protected $config;
    protected $businessData;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = config('tef');
        $this->businessData = $this->getBusinessData();
    }

    /**
     * Obtém dados da empresa e localização do banco de dados
     */
    private function getBusinessData()
    {
        // Busca a empresa e localização do usuário logado
        $businessId = Auth::user()->business_id ?? 1;
        $locationId = session('user.business_location_id') ?? null;
        
        $business = DB::table('business')
            ->where('id', $businessId)
            ->select('name', 'razao_social', 'cnpj')
            ->first();

        if (!$business) {
            // Fallback para primeira empresa se não encontrar
            $business = DB::table('business')
                ->select('name', 'razao_social', 'cnpj')
                ->first();
        }

        // Busca código de certificação TEF da localização (business_location)
        $tef_certificacao = null;
        if ($locationId) {
            $location = DB::table('business_locations')
                ->where('id', $locationId)
                ->where('business_id', $businessId)
                ->select('tef_registro_certificacao')
                ->first();
            
            $tef_certificacao = $location->tef_registro_certificacao ?? null;
        }

        // Se não tiver certificação na localização, busca do .env
        // Em produção (APP_ENV != local/debug), é obrigatório ter no banco
        $isDebug = config('app.env') === 'local' || config('app.debug') === true;
        
        if (empty($tef_certificacao)) {
            if ($isDebug) {
                // Em debug/homologação, usa o .env como fallback
                $tef_certificacao = config('tef.registro_certificacao', 'SEU_CODIGO_CERTIFICACAO');
            } else {
                // Em produção, loga erro mas permite continuar (para não quebrar o sistema)
                \Log::error('TEF: Código de certificação não configurado para localização', [
                    'business_id' => $businessId,
                    'location_id' => $locationId
                ]);
                $tef_certificacao = config('tef.registro_certificacao', '');
            }
        }

        return [
            'empresa_automacao' => $business->razao_social ?? config('tef.empresa_automacao', 'SUA EMPRESA LTDA'),
            'nome_automacao' => config('tef.nome_automacao', 'Gestor SeFácil'),
            'registro_certificacao' => $tef_certificacao
        ];
    }

    /**
     * Processa pagamento TEF
     */
    public function processarPagamentoTef($transactionId, $valor, $tipoTransacao = 'debito', $parcelas = 1)
    {
        try {
            // 1. Iniciar transação TEF
            $controle = $this->generateControle();
            $tefResponse = $this->iniciarTransacao([
                'transaction_id' => $transactionId,
                'valor_total' => $valor,
                'tipo_transacao' => $tipoTransacao,
                'parcelas' => $parcelas,
                'controle' => $controle
            ]);

            if (!$tefResponse['success']) {
                throw new \Exception('Falha ao iniciar transação TEF: ' . $tefResponse['message']);
            }

            $tefData = $tefResponse['data'];

            // Log detalhado da resposta da API GetCard removido para produção

            // 2. Verificar status da transação
            // Primeiramente, vamos analisar todos os possíveis status
            $status = $tefData['status'] ?? '';
            
            // Análise detalhada do status removida para produção
            
            // Aceita tanto "aprovado" quanto "APROVADA" e variações
            $statusAprovado = in_array(strtoupper($status), ['APROVADO', 'APROVADA']);
            
            if ($statusAprovado) {
                // 3. Confirmar transação
                $confirmResponse = $this->confirmarTransacao([
                    'identificacao' => $transactionId,
                    'doc_fiscal' => $tefData['nsu'] ?? '',
                    'adquirente' => $tefData['adquirente'] ?? '',
                    'controle' => $controle
                ]);

                if ($confirmResponse['success']) {
                    // 4. Salvar dados do pagamento TEF
                    $this->salvarPagamentoTef($transactionId, $tefData, $valor);
                    
                    return [
                        'success' => true,
                        'data' => $tefData,
                        'message' => 'Pagamento TEF aprovado com sucesso'
                    ];
                }
            }

            // Se chegou aqui, houve erro ou transação negada
            $errorMessage = $tefData['erro'] ?? 'Transação TEF negada';
            $statusInfo = 'Status: ' . ($tefData['status'] ?? 'indefinido');
            
            Log::warning('TEF transação não aprovada:', [
                'status' => $tefData['status'] ?? 'sem status',
                'erro' => $tefData['erro'] ?? 'sem erro',
                'tefData_completo' => $tefData
            ]);
            
            return [
                'success' => false,
                'data' => $tefData,
                'message' => $errorMessage . ' (' . $statusInfo . ')'
            ];

        } catch (\Exception $e) {
            Log::error('Erro TefService processarPagamentoTef: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Inicia transação TEF - comando CRT (Crédito/Débito)
     */
    private function iniciarTransacao($params)
    {
        // Converte valor para centavos (integer) conforme esperado pela API GetCard
        $valorEmCentavos = (int) round($params['valor_total'] * 100);
        
        $data = [
            'comando' => 'CRT', // Comando conforme exemplo GetCard
            'identificacao' => (int) $params['transaction_id'], // Garante que é integer
            'docFiscal' => (int) $params['transaction_id'],
            'valorTotal' => $valorEmCentavos, // Em centavos
            'moeda' => $this->config['moeda'],
            'capAutomacao' => $this->config['cap_automacao'],
            'empresaAutomacao' => $this->businessData['empresa_automacao'],
            'dataHoraFiscal' => now()->format('c'), // ISO 8601 format como no exemplo
            'versaoInterface' => $this->config['versao_interface'],
            'nomeAutomacao' => $this->businessData['nome_automacao'],
            'versaoAutomacao' => $this->config['versao_automacao'],
            'registroCertificacao' => $this->businessData['registro_certificacao']
        ];

        // Log para debug removido para produção

        return $this->callTefApi($this->config['endpoints']['req'], $data);
    }

    /**
     * Confirma transação TEF - comando CNF
     */
    private function confirmarTransacao($params)
    {
        $data = [
            'comando' => 'CNF', // Comando conforme exemplo GetCard
            'identificacao' => $params['identificacao'],
            'docFiscal' => $params['doc_fiscal'],
            'adquirente' => $params['adquirente'],
            'moeda' => $this->config['moeda'],
            'parcelas' => 0,
            'capAutomacao' => $this->config['cap_automacao'],
            'empresaAutomacao' => $this->businessData['empresa_automacao'],
            'dataHoraFiscal' => now()->format('Y-m-d H:i:s'),
            'versaoInterface' => $this->config['versao_interface'],
            'nomeAutomacao' => $this->businessData['nome_automacao'],
            'versaoAutomacao' => $this->config['versao_automacao'],
            'registroCertificacao' => $this->businessData['registro_certificacao']
        ];

        return $this->callTefApi($this->config['endpoints']['conf'], $data);
    }

    /**
     * Desfaz transação TEF - comando NCN
     */
    public function cancelarTransacao($params)
    {
        $data = [
            'comando' => 'NCN', // Comando conforme exemplo GetCard
            'identificacao' => $params['identificacao'],
            'docFiscal' => $params['doc_fiscal'],
            'adquirente' => $params['adquirente'],
            'controle' => $params['controle'],
            'moeda' => $this->config['moeda'],
            'parcelas' => 0,
            'capAutomacao' => $this->config['cap_automacao'],
            'empresaAutomacao' => $this->businessData['empresa_automacao'],
            'dataHoraFiscal' => now()->format('Y-m-d H:i:s'),
            'versaoInterface' => $this->config['versao_interface'],
            'nomeAutomacao' => $this->businessData['nome_automacao'],
            'versaoAutomacao' => $this->config['versao_automacao'],
            'registroCertificacao' => $this->businessData['registro_certificacao']
        ];

        return $this->callTefApi($this->config['endpoints']['desfaz'], $data);
    }

    /**
     * Salva dados do pagamento TEF no banco
     */
    private function salvarPagamentoTef($transactionId, $tefData, $valor)
    {
        $transaction = Transaction::find($transactionId);
        
        if ($transaction) {
            // Criar registro de pagamento TEF
            $paymentData = [
                'transaction_id' => $transactionId,
                'business_id' => $transaction->business_id,
                'amount' => $valor,
                'method' => 'tef',
                'paid_on' => now(),
                'created_by' => auth()->user()->id ?? $transaction->created_by,
                'payment_ref_no' => $this->generatePaymentRef(),
                'note' => 'Pagamento TEF',
                'transaction_no' => $tefData['nsu'] ?? '',
                'card_transaction_number' => $tefData['codigoAutorizacao'] ?? '',
                'card_number' => $tefData['bin'] ?? '',
                'card_type' => $tefData['tipoCartao'] ?? '',
                'card_holder_name' => '',
                // Dados específicos do TEF
                'tef_nsu' => $tefData['nsu'] ?? '',
                'tef_codigo_autorizacao' => $tefData['codigoAutorizacao'] ?? '',
                'tef_adquirente' => $tefData['adquirente'] ?? '',
                'tef_bandeira' => $tefData['bandeira'] ?? '',
                'tef_tipo_transacao' => $tefData['tipoTransacao'] ?? '',
                'tef_controle' => $tefData['controle'] ?? '',
                'tef_data_hora' => $tefData['dataHora'] ?? now(),
            ];

            // Adicionar campos TEF específicos se não existirem na tabela
            $this->addTefFieldsToPaymentTable();

            $payment = TransactionPayment::create($paymentData);

            // Atualizar status do pagamento da transação
            $this->updateTransactionPaymentStatus($transaction);
        } else {
            Log::warning('TEF: Transação não encontrada', ['transaction_id' => $transactionId]);
        }
    }

    /**
     * Atualiza status de pagamento da transação
     */
    private function updateTransactionPaymentStatus($transaction)
    {
        // Buscar tipo de transação TEF na tabela tef_transactions
        $tefTransaction = DB::table('tef_transactions')
            ->where('transaction_id', $transaction->id)
            ->select('tef_tipo_transacao', 'tef_comando')
            ->orderBy('id', 'desc')
            ->first();

        if ($tefTransaction) {
            $tipoTransacao = $tefTransaction->tef_tipo_transacao ?? $tefTransaction->tef_comando;
            
            if (!empty($tipoTransacao)) {
                // Mapear tipo de transação TEF para forma de pagamento
                $paymentMethod = $this->mapTefTipoToPaymentMethod($tipoTransacao);
                
                // Atualizar forma de pagamento em transaction_payments
                DB::table('transaction_payments')
                    ->where('transaction_id', $transaction->id)
                    ->whereNull('method') // Só atualiza se ainda não tem método
                    ->orWhere('method', 'tef') // Ou se o método é 'tef' (genérico)
                    ->update(['method' => $paymentMethod]);
            } else {
                Log::warning('TEF: Tipo de transação vazio', [
                    'transaction_id' => $transaction->id
                ]);
            }
        } else {
            Log::warning('TEF: Nenhum registro encontrado na tef_transactions', [
                'transaction_id' => $transaction->id
            ]);
        }

        // Calcular total pago
        $totalPaid = $transaction->transaction_payments()->sum('amount');
        
        if ($totalPaid >= $transaction->final_total) {
            $transaction->payment_status = 'paid';
        } else {
            $transaction->payment_status = 'partial';
        }
        
        $transaction->save();
    }

    /**
     * Mapeia tipo de transação TEF para forma de pagamento do sistema
     * Baseado no manual TEF
     */
    private function mapTefTipoToPaymentMethod($tefTipoTransacao)
    {
        // Mapeamento conforme manual TEF
        // Referência: https://www.getnet.com.br/suporte/tef/
        $mapping = [
            '10' => 'debit',     // Débito - Cartão de Débito
            '20' => 'card',      // Crédito à vista
            '21' => 'card',      // Crédito parcelado emissor (loja)
            '22' => 'card',      // Crédito parcelado estabelecimento (ADM)
            '30' => 'card',      // Pré-datado
            '40' => 'card',      // Voucher (alimentação, refeição)
            '50' => 'card',      // Private Label (cartão da loja)
            '60' => 'card',      // Frota
            '70' => 'other',     // Saque
            '80' => 'other',     // Depósito
            '90' => 'pix',       // PIX (se aplicável)
        ];

        Log::info('TEF: Mapeando tipo de transação', [
            'tef_tipo_transacao' => $tefTipoTransacao,
            'payment_method' => $mapping[$tefTipoTransacao] ?? 'card'
        ]);

        return $mapping[$tefTipoTransacao] ?? 'card';
    }    /**
     * Adiciona campos TEF à tabela transaction_payments se não existirem
     */
    private function addTefFieldsToPaymentTable()
    {
        try {
            if (!\Schema::hasColumn('transaction_payments', 'tef_nsu')) {
                \Schema::table('transaction_payments', function ($table) {
                    $table->string('tef_nsu')->nullable();
                    $table->string('tef_codigo_autorizacao')->nullable();
                    $table->string('tef_adquirente')->nullable();
                    $table->string('tef_bandeira')->nullable();
                    $table->string('tef_tipo_transacao')->nullable();
                    $table->string('tef_controle')->nullable();
                    $table->timestamp('tef_data_hora')->nullable();
                });
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao adicionar campos TEF: ' . $e->getMessage());
        }
    }

    /**
     * Chama API do TEF
     */
    private function callTefApi($endpoint, $data)
    {
        try {
            // Log removido para produção

            $response = $this->client->post($this->config['api_url'] . $endpoint, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'timeout' => $this->config['timeout']
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Log removido para produção

            return [
                'success' => true,
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('TEF API Error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Gera código de controle único
     */
    private function generateControle()
    {
        return date('YmdHis') . rand(1000, 9999);
    }

    /**
     * Gera referência de pagamento
     */
    private function generatePaymentRef()
    {
        return 'TEF' . date('YmdHis') . rand(100, 999);
    }

    /**
     * Verifica status do TEF
     */
    public function verificarStatus()
    {
        return $this->callTefApi('/tefgp-status', []);
    }

    /**
     * Operações administrativas
     */
    public function operacoesAdministrativas()
    {
        return $this->callTefApi('/tefgp-adm', []);
    }
}