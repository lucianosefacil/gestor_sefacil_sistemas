// TEF Integration - Produção
class TefIntegration {
    constructor() {
        this.pendingTefConfirmations = []; // Armazena dados TEF para confirmação após venda
        if (typeof $ === 'undefined') {
            setTimeout(() => { this.init(); }, 100);
            return;
        }
        this.init();
    }

    init() {
        this.baseUrl = this.getProjectBaseUrl() + '/tef';
        this.isProcessing = false;
        this.currentTransaction = null;
        this.initializeEventListeners();
    }

    getProjectBaseUrl() {
        const metaBaseUrl = $('meta[name="base-url"]').attr('content');
        if (metaBaseUrl) return metaBaseUrl;
        
        const currentPath = window.location.pathname;
        if (currentPath.includes('gestor_sefacil_sistemas/public')) {
            const basePath = currentPath.split('gestor_sefacil_sistemas/public')[0];
            return basePath + 'gestor_sefacil_sistemas/public';
        }
        return '';
    }

    initializeEventListeners() {
        $(document).on('change', '.payment_types_dropdown', (e) => {
            const paymentMethod = $(e.target).val();
            const paymentRow = $(e.target).closest('.payment_row');
            
            this.toggleTefSection(paymentRow, paymentMethod);
            if (paymentMethod === 'tef') {
                this.showTefOptions(paymentRow);
            }
        });

        $(document).on('click', '.btn-processar-tef', (e) => {
            e.preventDefault();
            this.processarPagamentoTef($(e.target).closest('.payment_row'));
        });



        $(document).on('click', '.btn-tef-adm', (e) => {
            e.preventDefault();
            this.operacoesAdministrativas();
        });
    }

    showTefOptions(paymentRow) {
        const rowIndex = paymentRow.find('.payment_row_index').val();
        paymentRow.find('.tef-options').remove();
        
        // Interface simplificada: Processar TEF | Verificar Status | Operações ADM
        
        // Apenas cria a área de status, sem duplicar os botões que já existem no HTML
        const tefOptionsHtml = `
            <div class="tef-options" style="margin-top: 10px;">
                <div class="tef-status" style="display: none;"></div>
            </div>
        `;
        
        paymentRow.append(tefOptionsHtml);
    }

    async processarPagamentoTef(paymentRow) {
        if (this.isProcessing) {
            this.showAlert('Já existe uma transação TEF em andamento', 'warning');
            return;
        }

        try {
            this.isProcessing = true;
            const rowIndex = paymentRow.find('.payment_row_index').val();
            
            let amount = paymentRow.find('.payment-amount').val() || 
                       paymentRow.find(`#amount_${rowIndex}`).val() ||
                       paymentRow.find(`input[name="payment[${rowIndex}][amount]"]`).val();
            
            if (!amount) {
                amount = $(`#amount_${rowIndex}`).val() || $('.payment-amount').first().val();
            }
            
            // Valores padrão fixos (campos removidos da interface)
            const tipoTransacao = 'debito';
            const parcelas = 1;
            const valorFinal = amount ? parseFloat(amount.replace(',', '.')) : 0;
            
            if (!amount || valorFinal <= 0) {
                throw new Error('Valor do pagamento deve ser maior que zero');
            }

            this.showProcessingStatus(paymentRow, true);
            this.showAlert('Processando pagamento TEF...', 'info');
            
            const response = await this.callTefApi('/processar', {
                transaction_id: this.getCurrentTransactionId(),
                valor_total: valorFinal,
                tipo_transacao: tipoTransacao,
                parcelas: parseInt(parcelas)
            });

            if (response.success || (response.data && (response.data.status || '').toUpperCase().includes('APROVAD'))) {
                this.showTefSuccess(paymentRow, response.data);
                this.showAlert('Pagamento TEF aprovado com sucesso!', 'success');
                
                // Armazena dados TEF para confirmação após conclusão da venda
                this.storeTefConfirmationData(response.data);
            } else {
                throw new Error(response.message || 'Erro no processamento TEF');
            }

        } catch (error) {
            this.showAlert('Erro no TEF: ' + error.message, 'error');
        } finally {
            this.isProcessing = false;
            this.showProcessingStatus(paymentRow, false);
        }
    }

    async operacoesAdministrativas() {
        try {
            this.showAlert('Iniciando operações administrativas TEF...', 'info');
            const response = await this.callTefApi('/adm', {});

            if (response.success) {
                this.showAlert('Operações administrativas TEF concluídas', 'success');
            } else {
                throw new Error(response.message || 'Erro nas operações administrativas');
            }
        } catch (error) {
            this.showAlert('Erro nas operações TEF: ' + error.message, 'error');
        }
    }

    async verificarStatusTefHandler(paymentRow) {
        try {
            this.showProcessingStatus(paymentRow, true);
            this.showAlert('Verificando status TEF...', 'info');
            
            const response = await this.callTefApi('/test-status', {}, 'GET');
            
            if (response.success) {
                this.showAlert('Status TEF verificado com sucesso!', 'success');
                if (response.data) {
                    this.showTefSuccess(paymentRow, response.data);
                } else {
                    paymentRow.find('.tef-status').html('<div class="alert alert-info">TEF operacional - Nenhuma transação ativa</div>').show();
                }
            } else {
                throw new Error(response.message || 'Erro ao verificar status TEF');
            }
        } catch (error) {
            this.showAlert('Erro ao verificar status TEF: ' + error.message, 'error');
            paymentRow.find('.tef-status').html(`<div class="alert alert-danger">Erro: ${error.message}</div>`).show();
        } finally {
            this.showProcessingStatus(paymentRow, false);
        }
    }

    toggleTefSection(paymentRow, paymentMethod) {
        const tefSection = paymentRow.find('.tef-section');
        const tefMethods = ['card', 'credit_card', 'debit_card', 'cartao_credito', 'cartao_debito', 'tef'];
        
        if (tefMethods.includes(paymentMethod)) {
            tefSection.show();
        } else {
            tefSection.hide();
        }
    }

    showProcessingStatus(paymentRow, show) {
        const statusDiv = paymentRow.find('.tef-status');
        const processBtn = paymentRow.find('.btn-processar-tef');
        
        if (show) {
            statusDiv.html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Processando...</div>').show();
            processBtn.prop('disabled', true);
        } else {
            statusDiv.hide();
            processBtn.prop('disabled', false);
        }
    }

    async callTefApi(endpoint, data, method = 'POST') {
        const url = this.baseUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        };

        if (method === 'POST') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        return await response.json();
    }

    showAlert(message, type = 'info') {
        const alertTypes = {
            'success': 'alert-success',
            'error': 'alert-danger', 
            'warning': 'alert-warning',
            'info': 'alert-info'
        };

        const alertHtml = `
            <div class="alert ${alertTypes[type]} alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ${message}
            </div>
        `;

        $('body').append(alertHtml);
        setTimeout(() => $('.alert').last().fadeOut(), 5000);
    }

    getCurrentTransactionId() {
        return parseInt(Date.now().toString().slice(-6));
    }

    clearTefData() {
        // Não limpa se há uma transação em processamento
        if (this.isProcessing) {
            return;
        }
        
        try {
            // Limpa dados TEF salvos globalmente
            if (window.tefDataToSave) {
                delete window.tefDataToSave;
            }
            
            // Remove elementos visuais de TEF da tela
            $('.tef-options').remove();
            $('.tef-success').remove();
            $('.tef-error').remove();
            $('.tef-status').hide().empty();
            
            // Remove todos os elementos com classes relacionadas ao TEF
            $('.alert:contains("TEF Processado")').remove();
            $('.alert:contains("Erro no TEF")').remove();
            
            // Remove campos hidden de TEF do formulário
            $('input[name*="[tef_"]').remove();
            $('input[name*="tef_"]').remove();
            
            // Reset transaction state
            this.currentTransaction = null;
            this.isProcessing = false;
            
        } catch (error) {
            // Se houver erro na limpeza, pelo menos reseta o estado básico
            this.currentTransaction = null;
            this.isProcessing = false;
            if (window.tefDataToSave) {
                delete window.tefDataToSave;
            }
        }
    }

    // Armazena dados TEF para confirmação posterior
    storeTefConfirmationData(data) {
        if (data && data.identificacao) {
            this.pendingTefConfirmations.push({
                identificacao: data.identificacao,
                doc_fiscal: data.nsu || data.doc_fiscal || '',
                adquirente: data.adquirente || '',
                controle: data.controle || '',
                timestamp: Date.now()
            });
            console.log('Dados TEF armazenados para confirmação:', data);
        }
    }

    // Confirma transações TEF pendentes após venda concluída
    async confirmTefAfterSale() {
        if (this.pendingTefConfirmations.length === 0) {
            return;
        }

        console.log('Confirmando TEF após conclusão da venda...');
        
        const confirmationsToProcess = [...this.pendingTefConfirmations];
        this.pendingTefConfirmations = []; // Limpa lista para evitar duplicação

        for (const tefData of confirmationsToProcess) {
            try {
                const response = await $.ajax({
                    url: '/tef/confirmar',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        identificacao: tefData.identificacao,
                        doc_fiscal: tefData.doc_fiscal,
                        adquirente: tefData.adquirente,
                        controle: tefData.controle
                    }
                });

                if (response.success) {
                    console.log('TEF confirmado com sucesso após venda:', tefData.identificacao);
                    this.showAlert('TEF confirmado após conclusão da venda', 'success');
                } else {
                    console.error('Erro na confirmação TEF:', response.message);
                    this.showAlert('Erro na confirmação TEF: ' + response.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao confirmar TEF após venda:', error);
                this.showAlert('Erro ao confirmar TEF após venda', 'error');
            }
        }
    }

    onNewSaleStarted() {
        // Chamado quando uma nova venda é iniciada
        this.clearTefData();
    }

    forceClearTefData() {
        // Força a limpeza mesmo durante processamento (usar com cuidado)
        const wasProcessing = this.isProcessing;
        this.isProcessing = false;
        this.clearTefData();
        if (wasProcessing) {
            this.showAlert('Dados TEF limpos - transação anterior cancelada', 'warning');
        }
    }

    checkTefDataStatus() {
        // Método para debug - verifica status atual dos dados TEF
        const hasGlobalData = !!window.tefDataToSave;
        const hasVisualElements = $('.tef-success').length > 0 || $('.tef-options').length > 0;
        const hasHiddenFields = $('input[name*="[tef_"]').length > 0;
        
        return {
            hasGlobalData,
            hasVisualElements,
            hasHiddenFields,
            isProcessing: this.isProcessing,
            currentTransaction: !!this.currentTransaction
        };
    }

    showTefSuccess(paymentRow, data) {
        const statusHtml = `
            <div class="alert alert-success">
                <i class="fa fa-check"></i> <strong>TEF Processado</strong>
                <div class="mt-2">
                    <small><strong>Status:</strong> ${data.status || 'Aprovado'}</small><br>
                    <small><strong>NSU:</strong> ${data.nsu || 'N/A'}</small><br>
                    <small><strong>Autorização:</strong> ${data.codigoAutorizacao || 'N/A'}</small><br>
                    <small><strong>Adquirente:</strong> ${data.adquirente || 'N/A'}</small>
                </div>
            </div>
        `;
        paymentRow.find('.tef-status').html(statusHtml).show();
        this.storeTefDataInPaymentRow(paymentRow, data);
    }

    storeTefDataInPaymentRow(paymentRow, tefData) {
        const rowIndex = paymentRow.find('.payment_row_index').val();
        
        const tefFields = [
            { name: 'tef_status', value: String(tefData.status || '') },
            { name: 'tef_nsu', value: String(tefData.nsu || '') },
            { name: 'tef_codigo_autorizacao', value: String(tefData.codigoAutorizacao || '') },
            { name: 'tef_adquirente', value: String(tefData.adquirente || '') },
            { name: 'tef_comando', value: String(tefData.comando || '') },
            { name: 'tef_id_req', value: String(tefData.idReq || '') },
            { name: 'tef_valor', value: String(tefData.valor || '0') },
            { name: 'tef_parcelas', value: String(tefData.parcelas || '0') },
            { name: 'tef_tipo_transacao', value: String(tefData.tipoTransacao || '') },
            { name: 'tef_data_hora', value: String(tefData.dataHora || '') },
            { name: 'tef_processado', value: '1' }
        ];
        
        paymentRow.find('input[name*="[tef_"]').remove();
        
        tefFields.forEach(field => {
            const hiddenInput = $('<input type="hidden">');
            hiddenInput.attr('name', `payment[${rowIndex}][${field.name}]`);
            hiddenInput.attr('value', field.value);
            paymentRow.append(hiddenInput);
        });
        
        if (!window.tefDataToSave) window.tefDataToSave = {};
        window.tefDataToSave[rowIndex] = tefData;
    }
    
    ensureTefDataInForm() {
        if (!window.tefDataToSave) return;
        
        Object.keys(window.tefDataToSave).forEach(rowIndex => {
            const paymentRowInput = $(`.payment_row_index[value="${rowIndex}"]`);
            const paymentRow = paymentRowInput.closest('.row');
            
            if (paymentRow.length > 0 && paymentRow.find('input[name*="tef_"]').length === 0) {
                this.storeTefDataInPaymentRow(paymentRow, window.tefDataToSave[rowIndex]);
            }
        });
    }
}

$(document).ready(function() {
    window.tefIntegration = new TefIntegration();
    
    // Função global para debug/teste - pode ser chamada no console
    window.clearTefDebug = function() {
        if (window.tefIntegration) {
            window.tefIntegration.forceClearTefData();
            return window.tefIntegration.checkTefDataStatus();
        }
    };
    
    window.checkTefStatus = function() {
        if (window.tefIntegration) {
            return window.tefIntegration.checkTefDataStatus();
        }
    };
    
    // Intercepta o clique no botão de salvar para garantir que dados TEF sejam incluídos
    $(document).on('click', '#pos-save', function() {
        if (window.tefIntegration && window.tefDataToSave) {
            window.tefIntegration.ensureTefDataInForm();
        }
    });

    // Limpa dados TEF quando uma nova venda é iniciada
    $(document).on('click', '#add_new_product_pos', function() {
        if (window.tefIntegration) {
            window.tefIntegration.onNewSaleStarted();
        }
    });

    // Limpa dados TEF quando o modal de pagamento é aberto (nova transação)
    $(document).on('shown.bs.modal', '#payment_modal', function() {
        if (window.tefIntegration) {
            // Verifica se não há transação em andamento antes de limpar
            setTimeout(() => {
                if (!window.tefIntegration.isProcessing) {
                    window.tefIntegration.clearTefData();
                }
            }, 100);
        }
    });

    // Limpa dados TEF quando o modal de pagamento é fechado após conclusão da venda
    $(document).on('hidden.bs.modal', '#payment_modal', function() {
        if (window.tefIntegration) {
            // Pequeno delay para garantir que a venda foi finalizada
            setTimeout(() => {
                window.tefIntegration.clearTefData();
            }, 500);
        }
    });

    // Limpa dados TEF quando o formulário é resetado
    $(document).on('reset', '#add_pos_sell_form', function() {
        if (window.tefIntegration) {
            setTimeout(() => {
                window.tefIntegration.clearTefData();
            }, 100);
        }
    });

    // Intercepta a função reset_pos_form do sistema para limpar dados TEF
    if (typeof window.reset_pos_form_original === 'undefined') {
        window.reset_pos_form_original = window.reset_pos_form;
        window.reset_pos_form = function() {
            // Chama a função original primeiro
            if (window.reset_pos_form_original) {
                window.reset_pos_form_original();
            }
            
            // Limpa dados TEF após o reset
            if (window.tefIntegration) {
                setTimeout(() => {
                    window.tefIntegration.clearTefData();
                }, 100);
            }
        };
    }

    // Intercepta requisições AJAX para detectar quando venda foi salva com sucesso
    $(document).ajaxSuccess(function(event, xhr, settings, data) {
        // Verifica se é uma requisição de salvamento de venda bem-sucedida
        if (settings.url && settings.url.includes('/pos') && 
            settings.method === 'POST' && 
            data && data.success == 1) {
            
            if (window.tefIntegration) {
                console.log('Venda concluída com sucesso. Verificando TEF pendente...');
                // Primeiro confirma TEF pendente, depois limpa dados
                setTimeout(async () => {
                    await window.tefIntegration.confirmTefAfterSale();
                    window.tefIntegration.clearTefData();
                }, 300);
            }
        }
    });
});
