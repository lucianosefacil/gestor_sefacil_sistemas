<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Serviço para integração direta com GPAPI.dll
 * Baseado na documentação GetCard para Delphi
 */
class GpApiService
{
    protected $dllPath;
    protected $ffi;

    public function __construct()
    {
        $this->dllPath = 'C:\Tef_Dial\GPAPI.dll';
        $this->initializeFfi();
    }

    /**
     * Inicializa FFI para usar a DLL GPAPI
     */
    private function initializeFfi()
    {
        try {
            // Definições das funções da DLL conforme documentação
            $this->ffi = \FFI::cdef("
                char* GPAPI_VendaDebito(char* json);
                char* GPAPI_VendaCredito(char* json);
                unsigned char GPAPI_FinalizaTransacoesPendentes(unsigned char conf);
                char* GPAPI_StatusTransacao();
                char* GPAPI_CancelaTransacao(char* json);
                char* GPAPI_OperacaoAdministrativa();
            ", $this->dllPath);
            
            Log::info('GPAPI DLL carregada com sucesso');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar GPAPI DLL: ' . $e->getMessage());
            $this->ffi = null;
        }
    }

    /**
     * Processa venda no débito
     */
    public function vendaDebito($valor, $tipo = 'V')
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $json = json_encode([
                'Valor' => $valor,
                'Tipo' => $tipo
            ]);

            Log::info('GPAPI VendaDebito - Input: ' . $json);

            $result = $this->ffi->GPAPI_VendaDebito($json);
            $response = \FFI::string($result);

            Log::info('GPAPI VendaDebito - Output: ' . $response);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro GPAPI VendaDebito: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processa venda no crédito
     */
    public function vendaCredito($valor, $parcelas = 1)
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $json = json_encode([
                'Valor' => $valor,
                'Tipo' => 'V',
                'Parcelas' => $parcelas
            ]);

            Log::info('GPAPI VendaCredito - Input: ' . $json);

            $result = $this->ffi->GPAPI_VendaCredito($json);
            $response = \FFI::string($result);

            Log::info('GPAPI VendaCredito - Output: ' . $response);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro GPAPI VendaCredito: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Finaliza transações pendentes
     */
    public function finalizaTransacoesPendentes($confirmar = true)
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $conf = $confirmar ? 1 : 0;
            
            Log::info('GPAPI FinalizaTransacoesPendentes - Confirmar: ' . ($confirmar ? 'SIM' : 'NÃO'));

            $result = $this->ffi->GPAPI_FinalizaTransacoesPendentes($conf);

            Log::info('GPAPI FinalizaTransacoesPendentes - Result: ' . $result);

            return $result === 1; // 1 = sucesso, 0 = falha

        } catch (\Exception $e) {
            Log::error('Erro GPAPI FinalizaTransacoesPendentes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica status da transação
     */
    public function statusTransacao()
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $result = $this->ffi->GPAPI_StatusTransacao();
            $response = \FFI::string($result);

            Log::info('GPAPI StatusTransacao - Output: ' . $response);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro GPAPI StatusTransacao: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancela transação
     */
    public function cancelaTransacao($dados = [])
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $json = json_encode($dados);

            Log::info('GPAPI CancelaTransacao - Input: ' . $json);

            $result = $this->ffi->GPAPI_CancelaTransacao($json);
            $response = \FFI::string($result);

            Log::info('GPAPI CancelaTransacao - Output: ' . $response);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro GPAPI CancelaTransacao: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Operação administrativa
     */
    public function operacaoAdministrativa()
    {
        if (!$this->ffi) {
            throw new \Exception('DLL GPAPI não está disponível');
        }

        try {
            $result = $this->ffi->GPAPI_OperacaoAdministrativa();
            $response = \FFI::string($result);

            Log::info('GPAPI OperacaoAdministrativa - Output: ' . $response);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro GPAPI OperacaoAdministrativa: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Faz o parse da resposta JSON retornada pela DLL
     */
    private function parseResponse($response)
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Se não for JSON válido, tenta extrair status da forma do exemplo Delphi
                $status = null;
                if (strpos($response, 'Status') !== false) {
                    $statusPos = strpos($response, 'Status') + 9;
                    $status = substr($response, $statusPos, 1);
                }
                
                return [
                    'raw_response' => $response,
                    'status' => $status,
                    'parsed' => false
                ];
            }

            return [
                'parsed' => true,
                'data' => $data,
                'status' => $data['Status'] ?? null,
                'raw_response' => $response
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao fazer parse da resposta GPAPI: ' . $e->getMessage());
            return [
                'parsed' => false,
                'raw_response' => $response,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verifica se a DLL está disponível
     */
    public function isDllAvailable()
    {
        return $this->ffi !== null && file_exists($this->dllPath);
    }

    /**
     * Processa pagamento completo seguindo o padrão da documentação
     */
    public function processarPagamento($valor, $tipo = 'debito', $parcelas = 1)
    {
        try {
            // 1. Inicia a transação
            if ($tipo === 'debito') {
                $response = $this->vendaDebito($valor);
            } else {
                $response = $this->vendaCredito($valor, $parcelas);
            }

            // 2. Verifica se precisa confirmar (Status = 'P' conforme documentação)
            if (isset($response['status']) && $response['status'] === 'P') {
                // Transação pendente, precisa confirmar
                $confirmado = true; // Por padrão confirma, mas pode ser configurável
                
                $tentativas = 0;
                $maxTentativas = 3;
                
                do {
                    $finalizou = $this->finalizaTransacoesPendentes($confirmado);
                    $tentativas++;
                    
                    if (!$finalizou && $tentativas < $maxTentativas) {
                        sleep(1); // Aguarda 1 segundo antes de tentar novamente
                    }
                    
                } while (!$finalizou && $tentativas < $maxTentativas);
                
                if (!$finalizou) {
                    throw new \Exception('Falha ao finalizar transação após ' . $maxTentativas . ' tentativas');
                }
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Erro ao processar pagamento GPAPI: ' . $e->getMessage());
            throw $e;
        }
    }
}