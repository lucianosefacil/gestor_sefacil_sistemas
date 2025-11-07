@extends('layouts.app')
@section('title', 'NFSe - Lista')

@section('content')

<section class="content-header">
  <h1>NFSe
    <small>Lista</small>
  </h1>
</section>

<section class="content">
  @component('components.widget', ['class' => 'box-primary', 'title' => 'NFSe Lista'])

    <form action="/nfse/filtro" method="get">
      <div class="row">
        <div class="col-sm-2 col-lg-3">
          <div class="form-group">
            <label>Data inicial:</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input class="form-control start-date-picker" placeholder="Data inicial"
                     name="data_inicial" type="text" value="{{{ isset($data_inicio) ? $data_inicio : ''}}}">
            </div>
          </div>
        </div>

        <div class="col-sm-2 col-lg-3">
          <div class="form-group">
            <label>Data final:</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input class="form-control start-date-picker" placeholder="Data final"
                     name="data_final" type="text" value="{{{ isset($data_final) ? $data_final : ''}}}">
            </div>
          </div>
        </div>

        <div class="col-sm-2 col-lg-3">
          <div class="form-group"><br>
            <button style="margin-top: 5px;" class="btn btn-block btn-primary">Filtrar</button>
          </div>
        </div>

        <div class="col-sm-2 col-lg-3">
          <div class="form-group"><br>
            <a href="/nfse/create" style="margin-top: 5px;" class="btn btn-block btn-success">
              <i class="fa fa-plus"></i> Nova NFSe
            </a>
          </div>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Data</th>
            <th>Tomador</th>
            <th>Número</th>
            <th>Estado</th>
            <th>Valor Serviço</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($nfses as $n)
            <tr>
              <td>{{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i:s') }}</td>
              <td>{{ $n->razao_social }}</td>
              <td>{{ $n->numero_nfse > 0 ? $n->numero_nfse : '-' }}</td>
              <td>{{ ucfirst($n->estado) }}</td>
              <td>{{ number_format($n->valor_total, 2, ',', '.') }}</td>
              <td>
                @if($n->estado == 'novo' || $n->estado == 'rejeitado')
                <a title="Remover" class="btn btn-danger btn-sm" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/nfse/delete/{{ $n->id }}" }else{return false} })' href="#!">
								  <i class="fa fa-trash"></i>				
								</a>
                <a title="Editar" class="btn btn-warning btn-sm" onclick='swal("Atenção!", "Deseja editar este registro?", "warning").then((sim) => {if(sim){ location.href="/nfse/edit/{{ $n->id }}" }else{return false} })' href="#!">
								  <i class="fa fa-edit"></i>	
								</a>
								<button type="button" onclick="transmitir('{{ $n->id }}')" title="Transmitir NFSe" class="btn btn-success btn-sm" >
								  <i class="fa fa-paper-plane"></i>
								</button>
								@endif
								@if($n->estado == 'aprovado')
								<a title="Baixar XML" target="_blank" href="/nfse/baixarXml/{{$n->id}}" class="btn btn-light btn-sm">
								  <i class="fa fa-download"></i>
								</a>
								<a title="Imprimir NFSe" target="_blank" href="/nfse/imprimir/{{$n->id}}" class="btn btn-light btn-sm">
								  <i class="fa fa-print"></i>
								</a>

								{{-- <a title="Cancelar NFSe" class="btn btn-danger btn-sm" href="/nfse/cancelar/{{$n->id}}">
								  <i class="fa fa-times"></i>
								</a> --}}
                <button title="Cancelar NFSe" class="btn btn-danger btn-sm" onclick="openCancelarModal({{ $n->id }}, '{{ $n->numero_nfse }}')">
                  <i class="fa fa-times"></i>
                </button>

								@else
								<a target="_blank" title="Visualizar temporário" class="btn btn-info btn-sm" href="/nfse/preview-xml/{{$n->id}}">
								  <i class="fa fa-file-excel"></i>
								</a>
								@endif
								{{-- <a title="Clonar" class="btn btn-primary btn-sm" href="/nfse/clone/{{$n->id}}">
								<i class="fa fa-copy"></i>
								</a> --}}
              </td>
            </tr>
          @empty
            <tr><td colspan="6">Nenhuma NFSe encontrada.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($nfses, 'links'))
      <div class="clearfix"></div>
      <br>
      {{ $nfses->links() }}
    @endif

    <h4 class="mt-3">Soma dos serviços:
      <strong>R$ {{ number_format($nfses->sum('valor_total'), 2, ',', '.') }}</strong>
    </h4>

    <div class="modal fade" id="modal1" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">CANCELAR NFSe <strong class="text-danger" id="numero_cancelamento"></strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              x
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
  
              <div class="form-group validated col-sm-12 col-lg-12">
                <label class="col-form-label" id="">Motivo</label>
                <select class="form-control custom-select" id="motivo">
                  <option value="1">Erro na emissão</option>
                  <option value="2">Serviço não prestado</option>
                  <option value="4">Duplicidade de nota</option>
                </select>
              </div>
            </div>
  
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">Fechar</button>
            <button type="button" id="btn-cancelar-2" onclick="cancelar()" class="btn btn-light-success font-weight-bold spinner-white spinner-right">Cancelar</button>
          </div>
        </div>
      </div>
    </div>
  @endcomponent
</section>

@section('javascript')
<script>


function transmitir(id) {
  swal({
    title: "Confirmar Transmissão",
    text: "Deseja transmitir esta NFSe?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sim, transmitir!",
    cancelButtonText: "Cancelar"
  }).then((result) => {
    if (!result) return;

    swal({ title: "Transmitindo...", text: "Aguarde...", type: "info", showConfirmButton: false, allowOutsideClick: false });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/nfse/enviar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ id })
    })
    .then(async (r) => ({ ok: r.ok, status: r.status, data: await r.json() }))
    .then(({ ok, status, data }) => {
      // Integra Notas: sucesso(bool), codigo(int), mensagem(string)
      if (ok && (data.sucesso || status === 200)) {
        swal({ title: "Sucesso!", text: "NFSe transmitida.", type: "success" }).then(() => location.reload());
        return;
      }
      if (status === 202 || data.codigo === 5023) {
        swal({ title: "Em processamento", text: "A NFSe está processando. Tente consultar em instantes.", type: "info" });
        return;
      }
      const msg = data.mensagem || data.erros || JSON.stringify(data);
      swal({ title: "Erro na Transmissão", text: String(msg), type: "error" });
    })
    .catch((e) => swal({ title: "Erro", text: e.message, type: "error" }));
  });
}

let nfseIdParaCancelar = null;

function openCancelarModal(id, numero) {
  nfseIdParaCancelar = id;
  document.getElementById('numero_cancelamento').textContent = '#' + (numero || '');
  $('#modal1').modal('show');
}

function cancelar() {
  if (!nfseIdParaCancelar) return;

  const motivoSel = document.getElementById('motivo').value; // "1", "2" ou "4"
  const motivos = { '1': 'Erro na emissão', '2': 'Serviço não prestado', '4': 'Duplicidade de nota' };
  const codigo_cancelamento = parseInt(motivoSel, 10) || 2;           // código numérico
  const justificativa = motivos[motivoSel] || 'Cancelamento requerido'; // texto legível

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // feedback
  if (window.swal) {
    swal({ title: 'Cancelando...', text: 'Aguarde...', type: 'info', showConfirmButton: false, allowOutsideClick: false });
  }

  fetch('/nfse/cancelar', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrf
    },
    body: JSON.stringify({ id: nfseIdParaCancelar, codigo_cancelamento, justificativa})
  })
  .then(async (r) => ({ ok: r.ok, status: r.status, data: await r.json().catch(() => ({})) }))
  .then(({ ok, status, data }) => {
    if (ok || status === 200) {
      $('#modal1').modal('hide');
      if (window.swal) {
        swal({ title: 'Cancelada!', text: 'NFSe cancelada com sucesso.', type: 'success' })
          .then(() => location.reload());
      } else {
        location.reload();
      }
      return;
    }
    const msg = data?.mensagem || data?.erro || data?.message || 'Falha ao cancelar';
    if (window.swal) swal({ title: 'Erro', text: String(msg), type: 'error' });
  })
  .catch((e) => {
    if (window.swal) swal({ title: 'Erro', text: e.message, type: 'error' });
  });
}

</script>
@endsection

@endsection