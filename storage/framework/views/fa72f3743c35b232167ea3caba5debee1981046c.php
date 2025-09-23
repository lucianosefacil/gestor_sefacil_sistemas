<?php $__env->startSection('title', 'Emitir NFe'); ?>

<?php $__env->startSection('content'); ?>

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Emitir NFe</h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <?php $__env->startComponent('components.widget'); ?>
                    <input type="hidden" id="id" value="<?php echo e($transaction->id, false); ?>" name="">
                    <div class="col-md-5">
                        <h4>Local: <strong><?php echo e($transaction->location->name, false); ?> -
                                <?php echo e($transaction->location->location_id, false); ?></strong></h4>
                        <h4>Estado: <strong><?php echo e($transaction->estado, false); ?></strong></h4>
                        <h4>Ultimo numero NFe: <strong><?php echo e($transaction->lastNFe($transaction), false); ?></strong></h4>
                        <h4>Natureza de Operação: <strong><?php echo e($transaction->natureza->natureza, false); ?></strong></h4>
                        <h4>Cliente: <strong><?php echo e($transaction->contact->name, false); ?></strong></h4>
                        <h4>CNPJ: <strong><?php echo e($transaction->contact->cpf_cnpj, false); ?></strong></h4>
                        <h4>Valor: <strong><?php echo e(number_format($transaction->final_total, 2, ',', ''), false); ?></strong></h4>
                        <h4>Forma de pagamento: <strong><?php echo e($payment_method, false); ?></strong></h4>

                    </div>

                    <div class="clearfix"></div>


                    <div class="col-md-12">
                        <a class="btn btn-lg btn-primary" target="_blank"
                            href="<?php echo e(route('nfe.renderizar', [$transaction->id]), false); ?>" id="submit_user_button">Visualizar</a>
                        <a class="btn btn-lg btn-danger" target="_blank" href="<?php echo e(route('nfe.gerarXml', [$transaction->id]), false); ?>"
                            id="submit_user_button">Gerar XML</a>
                        <a class="btn btn-lg btn-success" id="send-sefaz">Transmitir para Sefaz</a>
                    </div>

                    <div class="col-md-3 m-1">
                        <a class="btn btn-lg btn-info" href="<?php echo e(route('nfe.alterarDataEmissao', [$transaction->id]), false); ?>">Alterar
                            Data Emissão</a>
                    </div>
                <?php echo $__env->renderComponent(); ?>
            </div>

        </div>

        <input type="hidden" id="token" value="<?php echo e(csrf_token(), false); ?>" name="">

        <br>
        <div class="row" id="action" style="display: none">
            <div class="col-md-12">
                <?php $__env->startComponent('components.widget'); ?>
                    <div class="info-box-content">
                        <div class="col-md-4 col-md-offset-4">

                            <span class="info-box-number total_purchase">
                                <strong id="acao"></strong>
                                <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i>
                            </span>
                        </div>
                    </div>
                <?php echo $__env->renderComponent(); ?>

            </div>
        </div>

    <?php $__env->stopSection(); ?>



    <?php $__env->startSection('javascript'); ?>
        <script type="text/javascript">
            // swal("Good job!", "You clicked the button!", "success");
            var notClick = false;

            // $('#send-sefaz').click(() => {
            //     console.clear()
            //     if (!notClick) {
            //         notClick = true;
            //         $('#send-sefaz').attr('disabled', 'disabled')
            //         $('#action').css('display', 'block')
            //         let token = $('#token').val();
            //         let id = $('#id').val();

            //         setTimeout(() => {
            //             $('#acao').html('Gerando XML');
            //         }, 100);

            //         setTimeout(() => {
            //             $('#acao').html('Assinando o arquivo');
            //         }, 800);

            //         setTimeout(() => {
            //             $('#acao').html('Transmitindo para sefaz');
            //         }, 1500);

            //         $.ajax({
            //             type: 'POST',
            //             data: {
            //                 id: id,
            //                 _token: token
            //             },
            //             url: '<?php echo e(route('nfe.transmitir'), false); ?>',
            //             dataType: 'json',
            //             success: function(e) {
            //                 console.log(e)
            //                 $('#action').css('display', 'none')

            //                 if (e.success) {
            //                     swal("Sucesso", e.mensagem, "success").then(() => {
            //                         window.open('<?php echo e(route('nfe.imprimir', [$transaction->id]), false); ?>')
            //                         location.reload()
            //                     });
            //                 } else {
            //                     swal("Erro", e.mensagem + (e.erro ? "\n\nDetalhe: " + e.erro : ""),
            //                         "error");
            //                 }
            //             },
            //             error: function(e) {
            //                 $('#send-sefaz').removeAttr('disabled')
            //                 notClick = false;
            //                 console.log(e)
            //                 try {
            //                     let jsError = JSON.parse(e.responseText);
            //                     msg = jsError.mensagem || msg;
            //                 } catch (err) {}

            //                 swal("Erro", msg, "error");
            //                 $('#action').hide();
            //             }
            //         })
            //     }
            // })


            $('#send-sefaz').click(() => {
                if (notClick) return;
                notClick = true;
                $('#send-sefaz').prop('disabled', true);
                $('#action').show();

                let token = $('#token').val();
                let id = $('#id').val();

                $.ajax({
                        type: 'POST',
                        url: '<?php echo e(route('nfe.transmitir'), false); ?>',
                        data: {
                            id,
                            _token: token
                        },
                        dataType: 'json'
                    })
                    .done((res) => {
                        $('#action').hide();
                        $('#send-sefaz').prop('disabled', false);
                        notClick = false;

                        if (res && res.success) {
                            swal("Sucesso", res.mensagem || "NF-e autorizada.", "success").then(() => {
                                window.open('<?php echo e(route('nfe.imprimir', [$transaction->id]), false); ?>');
                                location.reload();
                            });
                        } else {
                            const msg = res?.mensagem || res?.error || res?.message || "Falha na transmissão.";
                            swal("Erro", msg, "error");
                        }
                    })
                    .fail((xhr) => {
                        $('#action').hide();
                        $('#send-sefaz').prop('disabled', false);
                        notClick = false;

                        const r = xhr.responseJSON;
                        const msg = (r && (r.mensagem || r.error || r.message || (typeof r === 'string' ? r : JSON
                                .stringify(r)))) ||
                            xhr.responseText ||
                            "Falha inesperada.";
                        swal("Erro", msg, "error");
                    });
            });
        </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/nfe/novo.blade.php ENDPATH**/ ?>