<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TefService;

class TefController extends Controller
{
    protected $tefService;

    public function __construct(TefService $tefService)
    {
        $this->tefService = $tefService;
    }
    /**
     * Processar pagamento TEF completo
     */
    public function processarPagamento(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer',
            'valor_total' => 'required|numeric|min:0.01',
            'tipo_transacao' => 'in:debito,credito',
            'parcelas' => 'integer|min:1|max:12'
        ]);

        $result = $this->tefService->processarPagamentoTef(
            $request->input('transaction_id'),
            $request->input('valor_total'),
            $request->input('tipo_transacao', 'debito'),
            $request->input('parcelas', 1)
        );

        return response()->json($result);
    }

    /**
     * Iniciar transação TEF
     */
    public function iniciarTransacao(Request $request)
    {
        try {
            $data = [
                'comando' => 'CRT',
                'identificacao' => $request->input('transaction_id'),
                'docFiscal' => $request->input('nfce_numero', 0),
                'valorTotal' => $request->input('valor_total'),
                'moeda' => 0, // Real brasileiro (conforme exemplos da documentação)
                'capAutomacao' => 3, // Conforme exemplos da documentação
                'empresaAutomacao' => config('tef.empresa_automacao', 'NOME DA SUA EMPRESA'),
                'dataHoraFiscal' => now()->format('Y-m-d\TH:i:s.v\Z'), // Formato ISO
                'versaoInterface' => 40, // Conforme exemplos da documentação
                'nomeAutomacao' => config('tef.nome_automacao', 'NOME DO SEU SISTEMA'),
                'versaoAutomacao' => config('tef.versao_automacao', 'v1, 40, 0, 0'),
                'registroCertificacao' => config('tef.registro_certificacao', 'CODIGO')
            ];

            $response = $this->callTefApi('/tefgp-req', $data);

            return response()->json([
                'success' => true,
                'data' => $response,
                'sent_data' => $data // Para debug
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF iniciarTransacao: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar transação TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar transação TEF
     */
    public function confirmarTransacao(Request $request)
    {
        try {
            $data = [
                'comando' => 'tefgp-conf',
                'identificacao' => $request->input('identificacao'),
                'docFiscal' => $request->input('doc_fiscal'),
                'adquirente' => $request->input('adquirente'),
                'controle' => $request->input('controle'),
                'versaoInterface' => 1,
                'nomeAutomacao' => config('app.name', 'Gestor SeFácil'),
                'versaoAutomacao' => '1.0.0',
                'registroCertificacao' => 'CERT123456'
            ];

            $response = $this->callTefApi('/tefgp-conf', $data);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF confirmarTransacao: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar transação TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar transação TEF
     */
    public function cancelarTransacao(Request $request)
    {
        try {
            $data = [
                'comando' => 'tefgp-cancel',
                'identificacao' => $request->input('identificacao'),
                'docFiscal' => $request->input('doc_fiscal'),
                'valorTotal' => $request->input('valor_total'),
                'moeda' => 1,
                'adquirente' => $request->input('adquirente'),
                'nsu' => $request->input('nsu'),
                'dataHoraComprovante' => $request->input('data_hora_comprovante'),
                'controle' => $request->input('controle'),
                'capAutomacao' => 1,
                'empresaAutomacao' => config('app.name', 'Gestor SeFácil'),
                'versaoInterface' => 1,
                'nomeAutomacao' => config('app.name', 'Gestor SeFácil'),
                'versaoAutomacao' => '1.0.0',
                'registroCertificacao' => 'CERT123456'
            ];

            $response = $this->callTefApi('/tefgp-cancel', $data);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF cancelarTransacao: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar transação TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desfazer transação TEF
     */
    public function desfazerTransacao(Request $request)
    {
        try {
            $data = [
                'comando' => 'tefgp-desfaz',
                'identificacao' => $request->input('identificacao'),
                'docFiscal' => $request->input('doc_fiscal'),
                'adquirente' => $request->input('adquirente'),
                'controle' => $request->input('controle'),
                'versaoInterface' => 1,
                'nomeAutomacao' => config('app.name', 'Gestor SeFácil'),
                'versaoAutomacao' => '1.0.0',
                'registroCertificacao' => 'CERT123456'
            ];

            $response = $this->callTefApi('/tefgp-desfaz', $data);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF desfazerTransacao: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desfazer transação TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status do TEF
     */
    public function verificarStatus()
    {
        try {
            $response = $this->callTefApi('/tefgp-status', []);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF verificarStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Imprimir comprovante TEF
     */
    public function imprimirComprovante(Request $request)
    {
        try {
            $data = [
                'nfce' => $request->input('nfce'),
                'customerTEF' => $request->input('customer_tef', []),
                'storeTEF' => $request->input('store_tef', [])
            ];

            $response = $this->callTefApi('/tefgp-print', $data);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF imprimirComprovante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao imprimir comprovante TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Operações administrativas
     */
    public function operacoesAdm()
    {
        try {
            $response = $this->callTefApi('/tefgp-adm', []);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro TEF operacoesAdm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro nas operações administrativas TEF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Chama a API do TEF
     */
    private function callTefApi($endpoint, $data)
    {
        $client = new \GuzzleHttp\Client();
        
        // URL base da API TEF-GP (deve ser configurada no .env)
        $baseUrl = config('tef.api_url', 'http://127.0.0.1:8000');
        $timeout = config('tef.timeout', 60);
        
        try {
            // Log removido para produção
            
            // Tenta primeiro com form_params (formato que a GetCard API pode esperar)
            $response = $client->post($baseUrl . $endpoint, [
                'form_params' => $data,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'timeout' => $timeout,
                'connect_timeout' => 10,
                'read_timeout' => $timeout
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            // Log removido para produção
            
            return $result;
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error("TEF API Connection Error: " . $e->getMessage());
            throw new \Exception("Failed to connect to server: " . $e->getMessage());
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error("TEF API Request Error: " . $e->getMessage());
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody()->getContents();
                Log::error("TEF API Error Response: " . $body);
                throw new \Exception("Request failed: " . $body);
            }
            throw new \Exception("Request failed: " . $e->getMessage());
        }
    }

    /**
     * Teste do status da API TEF
     */
    public function testStatus()
    {
        try {
            // Verifica se a API está online primeiro
            $client = new \GuzzleHttp\Client();
            $baseUrl = config('tef.api_url', 'http://host.docker.internal:8000');
            
            // Teste de conectividade simples
            $response = $client->get($baseUrl, [
                'timeout' => 5,
                'connect_timeout' => 3,
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 500) {
                // API está online
                return response()->json([
                    'success' => true,
                    'status' => $response->getStatusCode(),
                    'message' => 'API TEF está respondendo'
                ]);
            } else {
                throw new \Exception('API TEF não está respondendo adequadamente');
            }

        } catch (\Exception $e) {
            Log::error('Erro TEF testStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 200); // Retorna 200 para não quebrar frontend
        }
    }

    /**
     * Gera um código de controle único
     */
    private function generateControle()
    {
        return date('YmdHis') . rand(1000, 9999);
    }
}