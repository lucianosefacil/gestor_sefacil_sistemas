<?php $__env->startSection('title', 'Manter Devolução'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Manter Devolução</h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
    <div class="col-md-12">
      <?php $__env->startComponent('components.widget'); ?>
      
      <input type="hidden" id="id" value="<?php echo e($devolucao->id, false); ?>" name="">
      <div class="col-md-12">
        <h4>Número NFe Entrada: <strong><?php echo e($devolucao->nNf, false); ?></strong></h4>
        <h4>Chave NFe Entrada: <strong><?php echo e($devolucao->chave_nf_entrada, false); ?></strong></h4>
        <h4>Chave NFe Gerada: <strong><?php echo e($devolucao->chave_gerada, false); ?></strong></h4>
        <h4>Número NFe Gerada: <strong><?php echo e($devolucao->numero_gerado, false); ?></strong></h4>
        <h4>Estado: <strong><?php echo e($devolucao->estado(), false); ?></strong></h4>
        <h4>Fornecedor: <strong><?php echo e($devolucao->contact->name, false); ?></strong></h4>
        <h4>CPF/CNPJ: <strong><?php echo e($devolucao->contact->cpf_cnpj, false); ?></strong></h4>
        <h4>Cidade: <strong><?php echo e($devolucao->contact->cidade->nome, false); ?> (<?php echo e($devolucao->contact->cidade->uf, false); ?>)</strong></h4>

        <h4>Valor Devolvido: <strong>R$<?php echo e(number_format($devolucao->valor_devolvido, 2, ',', '.'), false); ?></strong></h4>
        <h4>Valor Integral: <strong>R$<?php echo e(number_format($devolucao->valor_integral, 2, ',', '.'), false); ?></strong></h4>

      </div>

      <input type="hidden" id="devolucao_id" value="<?php echo e($devolucao->id, false); ?>" name="">
      
      <div class="clearfix"></div>


      <?php if($devolucao->estado == 0 || $devolucao->estado == 2): ?>

      <div class="col-md-12">
        <a class="btn btn-lg btn-primary" target="_blank" href="<?php echo e(route('devolucao.renderizar', [$devolucao->id]), false); ?>" id="submit_user_button">Visualizar</a>
        <a class="btn btn-lg btn-danger" target="_blank" href="<?php echo e(route('devolucao.gerar-xml', [$devolucao->id]), false); ?>" id="submit_user_button">Gerar XML</a>
        <a class="btn btn-lg btn-success" id="send-sefaz">Transmitir para Sefaz</a>
      </div>
      <?php elseif($devolucao->estado == 1): ?>

      <div class="col-md-12">
        <a class="btn btn-lg btn-primary" target="_blank" href="<?php echo e(route('devolucao.imprimir', [$devolucao->id]), false); ?>" id="submit_user_button">Imprimir</a>
        <a class="btn btn-lg btn-info" target="_blank" href="<?php echo e(route('devolucao.baixar-xml', [$devolucao->id]), false); ?>" id="submit_user_button">Baixar XML</a>
        <a class="btn btn-lg btn-danger" id="cancelar">Cancelar NFe</a>
        <a class="btn btn-lg btn-warning" id="corrigir">Corrigir NFe</a>

        <?php if($devolucao->sequencia_cce > 0): ?>

        <a class="btn btn-lg btn-primary" target="_blank" href="<?php echo e(route('devolucao.imprimir-correcao', [$devolucao->id]), false); ?>" id="submit_user_button">Imprimir CCe</a>
        <?php endif; ?>

      </div>
      <?php elseif($devolucao->estado == 3): ?>

      <div class="col-md-12">
        <a class="btn btn-lg btn-primary" target="_blank" href="<?php echo e(route('devolucao.imprimir-cancelamento', [$devolucao->id]), false); ?>" id="submit_user_button">Imprimir Cancelamento</a>
        <a class="btn btn-lg btn-info" target="_blank" href="<?php echo e(route('devolucao.xml-cancelado', [$devolucao->id]), false); ?>" id="submit_user_button">Baixar XML de Cancelamento</a>

      </div>

      <?php endif; ?>
      
      <?php echo $__env->renderComponent(); ?>
    </div>

  </div>

  

  <input type="hidden" id="token" value="<?php echo e(csrf_token(), false); ?>" name="">
  <input type="hidden" id="numero_nfe" value="<?php echo e($devolucao->numero_gerado, false); ?>" name="">


  <br>
  <div class="row" id="action" style="display: none">
    <div class="col-md-12">
      <?php $__env->startComponent('components.widget'); ?>
      <div class="info-box-content">
        <div class="col-md-4 col-md-offset-4">

          <span class="info-box-number total_purchase">
            <strong id="acao"></strong>
            <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i></span>
          </div>
        </div>
        <?php echo $__env->renderComponent(); ?>

      </div>
    </div>

    <?php $__env->stopSection(); ?>


    <?php $__env->startSection('javascript'); ?>
    <script type="text/javascript">

      // swal("Good job!", "You clicked the button!", "success");
      var path = window.location.protocol + '//' + window.location.host

      $('#cancelar').click(() => {
        let numero_nfe = $('#numero_nfe').val();
        swal({
          text: 'Cancelamento de Devolução '+numero_nfe+'.',
          content: "input",
          button: {
            text: "Cancelar!",
            closeModal: false,
            type: 'error'
          },
          confirmButtonColor: "#DD6B55",
        })
        .then(v => {
          if (!v) swal("Erro!", "Informe um motivo para Cancelamento!", "error");
          else{
            let token = $('#token').val();
            let id = $('#id').val();
            $.ajax
            ({
              type: 'POST',
              data: {
                id: id,
                _token: token,
                justificativa: v
              },
              url: '<?php echo e(route("devolucao.cancelar"), false); ?>',
              dataType: 'json',
              success: function(e){
                console.log(e)

                swal("sucesso", e.retEvento.infEvento.xMotivo, "success")
                .then(() => {
                  location.reload()
                });

              }, error: function(e){
                console.log(e)

                console.log(e.responseJSON.data.retEvento.infEvento.xMotivo)

                swal("Erro ao cancelar", e.responseJSON.data.retEvento.infEvento.xMotivo, "error");

              }

            })
          }         


        })
        
        .catch(err => {
          if (err) {
            swal("Erro", "Algo não ocorreu bem!", "error");
          } else {
            swal.stopLoading();
            swal.close();
          }
        });
      })

      
      $('#send-sefaz').click(() => {
        let token = $('#token').val();
        let devolucao_id = $('#devolucao_id').val();
        
        $('#action').css('display', 'block')

        setTimeout(() => {
          $('#acao').html('Gerando XML');
        }, 50);

        setTimeout(() => {
          $('#acao').html('Assinando o arquivo');
        }, 800);

        setTimeout(() => {
          $('#acao').html('Transmitindo para sefaz');
        }, 1500);
        var path = window.location.protocol + '//' + window.location.host

        $.ajax
        ({
          type: 'POST',
          data: {
            _token: token,
            devolucao_id: devolucao_id
          },
          url: '<?php echo e(route("devolucao.transmitir"), false); ?>',
          dataType: 'json',
          success: function(e){
            console.log(e)

            swal("sucesso", "Devolução emitida, recibo: " + e, "success")
            .then(() => {
              window.open(path + '/devolucao/imprimir/'+devolucao_id)
              location.reload()
            });
            $('#action').css('display', 'none')


          }, error: function(e){
            $('#action').css('display', 'none')
            
            console.log(e)
            if(e.status == 402){
              swal("Erro ao transmitir", e.responseJSON, "error");
              $('#action').css('display', 'none')

            }else if(e.status == 500){
              swal("Erro ao transmitir", e.responseJSON.message, "error");
              
            }else{
              $('#action').css('display', 'none')
              try{

                let jsError = JSON.parse(e.responseJSON)
                console.log(jsError)
                swal("Erro ao transmitir", jsError.protNFe.infProt.xMotivo, "error");
              }catch{
                swal("Erro ao transmitir", e.responseJSON, "error");

              }

            }
          }

        })

      })

      $('#corrigir').click(() => {
        let numero_nfe = $('#devolucao_id').val();
        swal({
          text: 'Carta de correção devolução para NFe '+numero_nfe+'.',
          content: "input",
          button: {
            text: "Corrigir!",
            closeModal: false,
            type: 'error'
          },
          confirmButtonColor: "#DD6B55",
        })
        .then(v => {
          if (v.length < 15) swal("Erro!", "Informe 15 caracteres no minimo!", "error");
          else{
            let token = $('#token').val();
            let id = $('#id').val();
            $.ajax
            ({
              type: 'POST',
              data: {
                id: id,
                _token: token,
                justificativa: v
              },
              url: '<?php echo e(route("devolucao.corrigir"), false); ?>',
              dataType: 'json',
              success: function(e){
                console.log(e)

                swal("sucesso", e.retEvento.infEvento.xMotivo, "success")
                .then(() => {
                  window.open(path + '/devolucao/imprimirCorrecao/'+id)
                  location.reload()
                });
                

              }, error: function(e){
                console.log(e)
                console.log(e.responseJSON.data.retEvento.infEvento.xMotivo)

                swal("Erro ao corrigir", e.responseJSON.data.retEvento.infEvento.xMotivo, "error");

              }

            })
          }         


        })
        
        .catch(err => {
          if (err) {
            swal("Erro", "Algo não ocorreu bem!", "error");
          } else {
            swal.stopLoading();
            swal.close();
          }
        });
      })


    </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/devolucao/ver.blade.php ENDPATH**/ ?>