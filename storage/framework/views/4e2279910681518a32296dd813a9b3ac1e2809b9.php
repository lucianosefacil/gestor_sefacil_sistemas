<?php $__env->startSection('title', 'Manter NFe'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Manter NFe</h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
    <div class="col-md-12">
      <?php $__env->startComponent('components.widget'); ?>
      
      <input type="hidden" id="id" value="<?php echo e($transaction->id, false); ?>" name="">
      <div class="col-md-12">
        <h4>Local: <strong><?php echo e($transaction->location->name, false); ?> - <?php echo e($transaction->location->location_id, false); ?></strong></h4>
        <h4>Número NFe: <strong><?php echo e($transaction->numero_nfe, false); ?></strong></h4>
        <h4>Natureza de Operação: <strong><?php echo e($transaction->natureza->natureza, false); ?></strong></h4>
        <h4>Cliente: <strong><?php echo e($transaction->contact->name, false); ?></strong></h4>
        <h4>Email: <strong><?php echo e($transaction->contact->email, false); ?></strong></h4>
        <h4>CNPJ: <strong><?php echo e($transaction->contact->cpf_cnpj, false); ?></strong></h4>
        <h4>Estado: <strong><?php echo e($transaction->estado, false); ?></strong></h4>
        <h4>Chave: <strong><?php echo e($transaction->chave, false); ?></strong></h4>
      </div>

      <input type="hidden" id="numero_nfe" value="<?php echo e($transaction->numero_nfe, false); ?>" name="">
      
      <div class="clearfix"></div>


      <div class="col-md-12">
        <a class="btn btn-lg btn-primary" target="_blank" href="/nfe/imprimir/<?php echo e($transaction->id, false); ?>" id="submit_user_button">Imprimir</a>
        <a class="btn btn-lg btn-info" target="_blank" href="/nfe/baixarXml/<?php echo e($transaction->id, false); ?>" id="submit_user_button">Baixar XML</a>
        
        <?php if($transaction->estado != 'CANCELADO'): ?>
        <a class="btn btn-lg btn-danger" id="cancelar">Cancelar NFe</a>
        <a class="btn btn-lg btn-warning" id="corrigir">Corrigir NFe</a>
        <?php endif; ?>


        <?php if($transaction->sequencia_cce > 0): ?>
        <a class="btn btn-lg btn-question" style="background: #90caf9; color: #fff" target="_blank" href="/nfe/imprimirCorrecao/<?php echo e($transaction->id, false); ?>" id="submit_user_button">Imprimir Correção</a>
        <?php endif; ?>

        <?php if($transaction->estado == 'CANCELADO'): ?>
        <a class="btn btn-lg btn-question" style="background: #d84315; color: #fff" target="_blank" href="/nfe/imprimirCancelamento/<?php echo e($transaction->id, false); ?>" id="submit_user_button">Imprimir Cancelamento</a>
        <?php endif; ?>

        <a class="btn btn-lg btn-question" style="background: #673ab7; color: #fff" id="consultar">
        Consultar</a>

        <?php if($transaction->contact->email): ?>
        <a class="btn btn-lg btn-question" style="background: #f57c00; color: #fff" id="enviarEmail">
        Enviar Email</a>
        <?php endif; ?>
      </div>
      
      <?php echo $__env->renderComponent(); ?>
    </div>

  </div>

  <input type="hidden" id="token" value="<?php echo e(csrf_token(), false); ?>" name="">
  <input type="hidden" id="id" value="<?php echo e($transaction->id, false); ?>" name="">

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
      var path = window.location.protocol + '//' + window.location.host

      $('#cancelar').click(() => {
        let numero_nfe = $('#numero_nfe').val();
        swal({
          title: 'Cancelamento de NFe '+numero_nfe+'.',
          text: 'Mínimo de 15 caracteres!',
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
          else if(v.length < 15){
            swal("Erro!", "Informe no minimo 15 caracteres", "error");
          }
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
              url: path + '/nfe/cancelar',
              dataType: 'json',
              success: function(e){
                console.log(e)

                swal("sucesso", e.retEvento.infEvento.xMotivo, "success")
                .then(() => {
                  window.open(path + '/nfe/imprimirCancelamento/'+id)
                  location.reload()
                });

              }, error: function(e){
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

      $('#corrigir').click(() => {
        let numero_nfe = $('#numero_nfe').val();
        swal({
          text: 'Carta de correção para NFe '+numero_nfe+'.',
          content: "input",
          button: {
            text: "Corrigir!",
            closeModal: false,
            type: 'error'
          },
          confirmButtonColor: "#DD6B55",
        })
        .then(v => {
          if (!v) swal("Erro!", "Informe a correção!", "error");
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
              url: path + '/nfe/corrigir',
              dataType: 'json',
              success: function(e){
                console.log(e)

                swal("sucesso", e.retEvento.infEvento.xMotivo, "success")
                .then(() => {
                  window.open(path + '/nfe/imprimirCorrecao/'+id)
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

      $('#consultar').click(() => {
        let numero_nfe = $('#numero_nfe').val();
        
        let token = $('#token').val();
        let id = $('#id').val();

        $.ajax
        ({
          type: 'POST',
          data: {
            id: id,
            _token: token
          },
          url: path + '/nfe/consultar',
          dataType: 'json',
          success: function(e){
            console.log(e)
            swal("sucesso", "Resultado: " + e.xMotivo + " - Chave: " + e.chNFe, "success")
            .then(() => {
            });
          }, error: function(e){
            console.log(e)
            try{
              swal("Erro ao consultar", e.responseJSON, "error");
            }catch{
              swal("Erro ao consultar", e.responseText, "error");

            }
          }

        })
      })

      $('#enviarEmail').click(() => {

        let id = $('#id').val();

        $.ajax
        ({
          type: 'GET',
          url: path + '/nfe/enviarEmail/' +id,
          dataType: 'json',
          success: function(e){
            console.log(e)

            swal("Sucesso", e, "success");


          }, error: function(e){
            console.log(e)
            try{
              swal("Erro ao consultar", e.responseJSON, "error");
            }catch{
              swal("Erro ao consultar", e.responseText, "error");

            }
          }

        })
      })

    </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/nfe/ver.blade.php ENDPATH**/ ?>