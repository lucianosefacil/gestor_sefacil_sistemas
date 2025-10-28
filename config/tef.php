<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TEF Configuration - GetCard API Local
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com TEF-GP (GetCard)
    | A API GetCard roda localmente no computador
    |
    */

    // URL da API GetCard local (padrão: 127.0.0.1:8000)
    'api_url' => env('TEF_API_URL', 'http://127.0.0.1:8000'),
    
    // Endpoints específicos da API GetCard
    'endpoints' => [
        'req' => '/api/tefgp-req',      // Requisição de transação (CRT)
        'conf' => '/api/tefgp-conf',    // Confirmação (CNF)  
        'desfaz' => '/api/tefgp-desfaz', // Desfazer (NCN)
        'adm' => '/api/tefgp-adm',      // Administrativo/Re-imprimir
    ],
    
    'timeout' => env('TEF_TIMEOUT', 30),
    
    // Dados da empresa (OBRIGATÓRIOS - conforme documentação GetCard)
    'empresa_automacao' => env('TEF_EMPRESA_AUTOMACAO', 'NOME DA SUA EMPRESA'),
    'nome_automacao' => env('TEF_NOME_AUTOMACAO', 'NOME DO SEU SISTEMA'),
    'versao_automacao' => env('TEF_VERSAO_AUTOMACAO', 'v1, 40, 0, 0'),
    'registro_certificacao' => env('TEF_REGISTRO_CERTIFICACAO', 'CODIGO'),
    
    // Parâmetros técnicos GetCard
    'cap_automacao' => 3, // Capacidade da automação (conforme exemplo)
    'versao_interface' => 40, // Versão da interface TEF-GP
    'moeda' => 0, // Real brasileiro (0 conforme exemplo)
    
    // Debug mode
    'debug' => env('TEF_DEBUG', false),
    
    // Log all TEF transactions
    'log_transactions' => env('TEF_LOG_TRANSACTIONS', true),
];