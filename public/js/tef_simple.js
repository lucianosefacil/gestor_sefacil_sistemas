// Interceptação simples para TEF
$(document).ready(function() {
    // Intercepta AJAX calls
    const originalAjax = $.ajax;
    $.ajax = function(options) {
        // Verifica se é requisição de venda (mais específico)
        const isVendaRequest = (options.method === 'POST' && options.url && 
                               (options.url.endsWith('/pos') || options.url.includes('/pos/store')));
        
        if (isVendaRequest) {
            // Adiciona dados TEF se existirem
            if (window.tefDataToSave) {
                Object.keys(window.tefDataToSave).forEach(rowIndex => {
                    const tefData = window.tefDataToSave[rowIndex];
                    
                    // Adiciona diretamente à string
                    const tefParams = [
                        `payment[${rowIndex}][tef_status]=${encodeURIComponent(tefData.status || '')}`,
                        `payment[${rowIndex}][tef_nsu]=${encodeURIComponent(tefData.nsu || '')}`,
                        `payment[${rowIndex}][tef_codigo_autorizacao]=${encodeURIComponent(tefData.codigoAutorizacao || '')}`,
                        `payment[${rowIndex}][tef_adquirente]=${encodeURIComponent(tefData.adquirente || '')}`,
                        `payment[${rowIndex}][tef_comando]=${encodeURIComponent(tefData.comando || '')}`,
                        `payment[${rowIndex}][tef_id_req]=${encodeURIComponent(tefData.idReq || '')}`,
                        `payment[${rowIndex}][tef_valor]=${encodeURIComponent(tefData.valor || '0')}`,
                        `payment[${rowIndex}][tef_parcelas]=${encodeURIComponent(tefData.parcelas || '0')}`,
                        `payment[${rowIndex}][tef_tipo_transacao]=${encodeURIComponent(tefData.tipoTransacao || '')}`,
                        `payment[${rowIndex}][tef_data_hora]=${encodeURIComponent(tefData.dataHora || '')}`,
                        `payment[${rowIndex}][tef_processado]=1`
                    ];
                    
                    const tefString = '&' + tefParams.join('&');
                    options.data += tefString;
                });
            }
        }
        
        return originalAjax.call(this, options);
    };
});