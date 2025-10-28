<?php

namespace App\Observers;

use App\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionPaymentObserver
{
    /**
     * Handle the TransactionPayment "created" event.
     */
    public function created(TransactionPayment $transactionPayment)
    {
        Log::info('🔔 TEF Observer: TransactionPayment CREATED EVENT FIRED', [
            'payment_id' => $transactionPayment->id,
            'transaction_id' => $transactionPayment->transaction_id,
            'method' => $transactionPayment->method,
            'amount' => $transactionPayment->amount
        ]);
        
        // Só processa se o método for 'tef' ou nulo/vazio
        if (in_array($transactionPayment->method, ['tef', null, ''])) {
            Log::info('✅ TEF Observer: Método é TEF/NULL - processando...');
            $this->updatePaymentMethodFromTef($transactionPayment);
        } else {
            Log::info('❌ TEF Observer: Método não é TEF/NULL - ignorando', [
                'method' => $transactionPayment->method
            ]);
        }
    }

    /**
     * Handle the TransactionPayment "updated" event.
     */
    public function updated(TransactionPayment $transactionPayment)
    {
        // Só processa se o método mudou para 'tef' ou está vazio
        if (in_array($transactionPayment->method, ['tef', null, '']) && $transactionPayment->isDirty('method')) {
            $this->updatePaymentMethodFromTef($transactionPayment);
        }
    }

    /**
     * Atualiza a forma de pagamento baseado nos dados TEF
     */
    private function updatePaymentMethodFromTef(TransactionPayment $transactionPayment)
    {
        Log::info('TEF Observer: Processando pagamento', [
            'payment_id' => $transactionPayment->id,
            'transaction_id' => $transactionPayment->transaction_id,
            'method' => $transactionPayment->method
        ]);

        // Buscar tipo de transação TEF na tabela tef_transactions
        $tefTransaction = DB::table('tef_transactions')
            ->where('transaction_id', $transactionPayment->transaction_id)
            ->select('tef_tipo_transacao', 'tef_comando')
            ->orderBy('id', 'desc')
            ->first();

        Log::info('TEF Observer: Resultado da busca em tef_transactions', [
            'transaction_id' => $transactionPayment->transaction_id,
            'tefTransaction_found' => $tefTransaction ? 'SIM' : 'NÃO',
            'tefTransaction' => $tefTransaction
        ]);

        if ($tefTransaction) {
            $tipoTransacao = $tefTransaction->tef_tipo_transacao ?? $tefTransaction->tef_comando;
            
            Log::info('TEF Observer: Tipo de transação extraído', [
                'tef_tipo_transacao_raw' => $tefTransaction->tef_tipo_transacao,
                'tef_comando_raw' => $tefTransaction->tef_comando,
                'tipoTransacao_final' => $tipoTransacao
            ]);
            
            if (!empty($tipoTransacao)) {
                // Mapear tipo de transação TEF para forma de pagamento
                $paymentMethod = $this->mapTefTipoToPaymentMethod($tipoTransacao);
                
                Log::info('TEF Observer: Atualizando método de pagamento', [
                    'payment_id' => $transactionPayment->id,
                    'transaction_id' => $transactionPayment->transaction_id,
                    'tef_tipo_transacao' => $tipoTransacao,
                    'payment_method' => $paymentMethod
                ]);
                
                // Atualizar sem disparar eventos (para evitar loop infinito)
                DB::table('transaction_payments')
                    ->where('id', $transactionPayment->id)
                    ->update(['method' => $paymentMethod]);
                    
                Log::info('TEF Observer: Método de pagamento atualizado com sucesso', [
                    'payment_id' => $transactionPayment->id,
                    'new_method' => $paymentMethod
                ]);
            } else {
                Log::warning('TEF Observer: Tipo de transação vazio', [
                    'transaction_id' => $transactionPayment->transaction_id
                ]);
            }
        } else {
            Log::info('TEF Observer: Nenhum registro TEF encontrado para esta transação', [
                'transaction_id' => $transactionPayment->transaction_id
            ]);
        }
    }

    /**
     * Mapeia tipo de transação TEF para forma de pagamento do sistema
     * Baseado no manual TEF
     */
    private function mapTefTipoToPaymentMethod($tefTipoTransacao)
    {
        // Mapeamento conforme manual TEF
        $mapping = [
            '10' => 'card',      // Débito
            '20' => 'card',      // Crédito à vista
            '21' => 'card',      // Crédito parcelado emissor
            '22' => 'card',      // Crédito parcelado estabelecimento
            '30' => 'card',      // Pré-datado
            '40' => 'card',      // Voucher
            '50' => 'card',      // Private Label
            '60' => 'card',      // Frota
            '70' => 'other',     // Saque
            '80' => 'other',     // Depósito
            '90' => 'pix',       // PIX (se aplicável)
        ];

        return $mapping[$tefTipoTransacao] ?? 'card';
    }
}
