@extends('layouts.app')

@section('title', 'Teste TEF Integration')

@push('head')
<meta name="base-url" content="{{ url('/') }}">
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Teste de Integração TEF
            <small>Transferência Eletrônica de Fundos</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-credit-card"></i> Simulação de Pagamento TEF</h3>
                    </div>
                    <div class="box-body">
                        <form id="tef-test-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="valor">Valor da Transação</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">R$</span>
                                            <input type="text" class="form-control" id="valor" name="valor" value="10.00" placeholder="0,00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_method">Método de Pagamento</label>
                                        <select class="form-control payment_types_dropdown" id="payment_method" name="payment_method">
                                            <option value="cash">Dinheiro</option>
                                            <option value="card">Cartão</option>
                                            <option value="credit_card">Cartão de Crédito</option>
                                            <option value="debit_card">Cartão de Débito</option>
                                            <option value="tef">TEF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- TEF Section -->
                            <div class="tef-section" style="display: none;">
                                <div class="box box-info">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><i class="fa fa-credit-card"></i> TEF - Transferência Eletrônica de Fundos</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-primary btn-processar-tef">
                                                    <i class="fa fa-credit-card"></i> Processar TEF
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-warning btn-verificar-status-tef">
                                                    <i class="fa fa-refresh"></i> Verificar Status
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-danger btn-cancelar-tef" style="display: none;">
                                                    <i class="fa fa-times"></i> Cancelar TEF
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tef-status mt-3" style="display: none;">
                                            <div class="alert alert-info">
                                                <i class="fa fa-spinner fa-spin"></i> Processando TEF...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Status da API TEF</h3>
                    </div>
                    <div class="box-body">
                        <div id="api-status">
                            <p><strong>Status:</strong> <span class="label label-warning">Verificando...</span></p>
                            <p><strong>Última Verificação:</strong> <span id="last-check">-</span></p>
                        </div>
                        
                        <button type="button" class="btn btn-info btn-block" id="check-api-status">
                            <i class="fa fa-refresh"></i> Verificar Status da API
                        </button>
                        
                        <hr>
                        
                        <h4>Dados da Última Resposta:</h4>
                        <div id="last-response" style="max-height: 300px; overflow-y: auto;">
                            <pre id="response-data">Nenhuma resposta ainda...</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<!-- Garantir que jQuery está carregado primeiro -->
@if(!isset($jquery_loaded))
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endif

<script src="{{ asset('js/tef.js?v=' . time()) }}"></script>
<script>
$(document).ready(function() {
    // Instância específica para teste
    if (typeof window.tefIntegration === 'undefined') {
        window.tefIntegration = new TefIntegration();
    }
    
    // Eventos específicos desta página
    $('#check-api-status').click(async function() {
        $('#api-status .label').removeClass('label-success label-danger').addClass('label-warning').text('Verificando...');
        
        try {
            const response = await window.tefIntegration.verificarStatusTef();
            
            if (response.success) {
                $('#api-status .label').removeClass('label-warning label-danger').addClass('label-success').text('Online');
                $('#response-data').text(JSON.stringify(response.data, null, 2));
            } else {
                $('#api-status .label').removeClass('label-warning label-success').addClass('label-danger').text('Erro');
                $('#response-data').text('Erro: ' + response.message);
            }
        } catch (error) {
            $('#api-status .label').removeClass('label-warning label-success').addClass('label-danger').text('Offline');
            $('#response-data').text('Erro: ' + error.message);
        }
        
        $('#last-check').text(new Date().toLocaleString('pt-BR'));
    });
    
    // Trigger inicial
    $('#check-api-status').click();
    
    // Auto-refresh a cada 30 segundos
    setInterval(function() {
        $('#check-api-status').click();
    }, 30000);
    
    // Mock payment row para teste
    window.mockPaymentRow = {
        find: function(selector) {
            if (selector === '.tef-status') {
                return $('#tef-test-form .tef-status');
            }
            return $();
        }
    };
    
    // Override dos métodos para usar o mock
    $('.btn-processar-tef').click(function() {
        window.tefIntegration.processarPagamentoTef(window.mockPaymentRow);
    });
    
    $('.btn-verificar-status-tef').click(function() {
        window.tefIntegration.verificarStatusTefHandler(window.mockPaymentRow);
    });
});
</script>
@endsection