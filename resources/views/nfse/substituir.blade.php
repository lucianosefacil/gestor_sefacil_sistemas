@extends('layouts.app')
@section('title', 'Substituir NFSe')

@section('content')
<section class="content-header">
  <h1>NFSe
    <small>Substituição</small>
  </h1>
</section>

<section class="content">
  @component('components.widget', ['class' => 'box-primary', 'title' => 'Substituir NFSe'])
  
    <form class="form" method="post" action="{{ route('nfse.substituir.salvar', $item->id) }}">
      @csrf

      <input type="hidden" name="id_antiga" value="{{ $item->id }}">

      <div class="row">
        <div class="col-sm-12">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul style="margin-bottom: 0;">
                @foreach($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>

      <div class="alert alert-warning">
        <strong>Substituição:</strong> Esta NFSe aprovada será reemitida com os dados abaixo.
        Ajuste o que for necessário, informe o motivo da substituição e clique em <strong>Salvar e substituir NFSe</strong>.
      </div>

      {{-- Linha 1: Cliente / Documento / Email / Telefone --}}
      <div class="row">
        <div class="col-sm-6 col-lg-6">
          <div class="form-group">
            <label>Cliente <span class="text-danger">*</span></label>
            <div class="input-group">
              <select name="cliente" id="cliente_select" class="form-control" required style="width: 100%;">
                <option value="">Selecione um cliente</option>
                @if(isset($item) && $item->cliente_id)
                  <option value="{{ $item->cliente_id }}" selected>
                    {{ $item->cliente_id }} - {{ $item->razao_social }}
                  </option>
                @endif
              </select>
              <span class="input-group-btn">
                <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name="">
                  <i class="fa fa-plus-circle text-primary fa-lg"></i>
                </button>
              </span>
            </div>
          </div>
        </div>

        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>CPF/CNPJ <span class="text-danger">*</span></label>
            <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror"
                   value="{{ old('documento', $item->documento) }}" required>
          </div>
        </div>

        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $item->email) }}">
          </div>
        </div>
      </div>

      {{-- Linha 2: Razão / Nome Fantasia / CEP --}}
      <div class="row">
        <div class="col-sm-6 col-lg-6">
          <div class="form-group">
            <label>Razão Social <span class="text-danger">*</span></label>
            <input type="text" name="razao_social" class="form-control @error('razao_social') is-invalid @enderror"
                   value="{{ old('razao_social', $item->razao_social) }}" required>
          </div>
        </div>

        <div class="col-sm-4 col-lg-4">
          <div class="form-group">
            <label>Nome Fantasia</label>
            <input type="text" name="nome_fantasia" class="form-control @error('nome_fantasia') is-invalid @enderror"
                   value="{{ old('nome_fantasia', $item->nome_fantasia) }}">
          </div>
        </div>

        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>CEP <span class="text-danger">*</span></label>
            <input type="text" name="cep" class="form-control @error('cep') is-invalid @enderror"
                   value="{{ old('cep', $item->cep) }}" required>
          </div>
        </div>
      </div>

      {{-- Linha 3: Endereço --}}
      <div class="row">
        <div class="col-sm-6 col-lg-5">
          <div class="form-group">
            <label>Rua <span class="text-danger">*</span></label>
            <input type="text" name="rua" class="form-control @error('rua') is-invalid @enderror"
                   value="{{ old('rua', $item->rua) }}" required>
          </div>
        </div>

        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>Número <span class="text-danger">*</span></label>
            <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror"
                   value="{{ old('numero', $item->numero) }}" required>
          </div>
        </div>

        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>Bairro <span class="text-danger">*</span></label>
            <input type="text" name="bairro" class="form-control @error('bairro') is-invalid @enderror"
                   value="{{ old('bairro', $item->bairro) }}" required>
          </div>
        </div>

        <div class="col-sm-4 col-lg-3">
          <div class="form-group">
            <label>Cidade <span class="text-danger">*</span></label>
            <select name="cidade_id" class="form-control @error('cidade_id') is-invalid @enderror" required>
              @foreach(App\Models\City::all() as $c)
                <option value="{{ $c->id }}"
                  @if(old('cidade_id', $item->cidade_id) == $c->id) selected @endif>
                  {{ $c->nome }} ({{ $c->uf }})
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Linha 4: Serviço / Códigos / Valores --}}
      <hr>
      <div class="row">
        <div class="col-sm-6 col-lg-6">
          <div class="form-group">
            <label>Descrição do Serviço <span class="text-danger">*</span></label>
            <textarea name="discriminacao" 
                      id="discriminacao" 
                      class="form-control @error('discriminacao') is-invalid @enderror" 
                      rows="3" 
                      maxlength="2000"
                      required>{{ old('discriminacao', optional($item->servico)->discriminacao) }}</textarea>
            <small class="text-muted">
              <span id="char-count">0</span> / 2000 caracteres
            </small>
          </div>
        </div>

        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Natureza de Operação <span class="text-danger">*</span></label>
            <input type="text" name="natureza_operacao" class="form-control @error('natureza_operacao') is-invalid @enderror"
                   value="{{ old('natureza_operacao', $item->natureza_operacao) }}" required>
          </div>
        </div>

        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Valor do Serviço <span class="text-danger">*</span></label>
            <input type="text" name="valor_servico" class="form-control @error('valor_servico') is-invalid @enderror money"
                   value="{{ old('valor_servico', optional($item->servico)->valor_servico ? number_format($item->servico->valor_servico, 2, ',', '.') : '') }}" required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3 col-lg-2">
          <div class="form-group">
            <label>Item LC 116/2003<span class="text-danger">*</span></label>
            <input type="text" name="codigo_servico" class="form-control @error('codigo_servico') is-invalid @enderror"
                   value="{{ old('codigo_servico', optional($item->servico)->codigo_servico ?? optional($nfseConfig)->item_lc) }}" required>
          </div>
        </div>

        <div class="col-sm-3 col-lg-2">
          <div class="form-group">
            <label>CNAE</label>
            <input type="text" name="codigo_cnae" class="form-control"
                   value="{{ old('codigo_cnae', optional($item->servico)->codigo_cnae ?? optional($nfseConfig)->cnae) }}">
          </div>
        </div>

        <div class="col-sm-3 col-lg-2">
          <div class="form-group">
            <label>Ativid. Município</label>
            <input type="text" name="codigo_tributacao_municipio" class="form-control"
                   value="{{ old('codigo_tributacao_municipio', optional($item->servico)->codigo_tributacao_municipio ?? optional($nfseConfig)->codigo_tributacao_municipio) }}">
          </div>
        </div>

        <div class="col-sm-3 col-lg-2">
          <div class="form-group">
            <label>Aliquota ISS</label>
            <input type="tel" name="aliquota_iss" class="form-control money" id="aliquota_iss" value="{{ old('aliquota_iss', optional($item->servico)->aliquota_iss) }}"/>
          </div>
        </div>

        <div class="col-sm-3 col-lg-2">
          <div class="form-group">
            <label>ISS Retido <span class="text-danger">*</span></label>
            <select name="iss_retido" class="form-control" required>
              <option value="2" @if(old('iss_retido', optional($item->servico)->iss_retido ?? 2)==2) selected @endif>Não</option>
              <option value="1" @if(old('iss_retido', optional($item->servico)->iss_retido ?? 2)==1) selected @endif>Sim</option>
            </select>
          </div>
        </div>
      </div>

      <hr>
      <div class="row">
        <div class="col-sm-12"><h4>Deduções e Descontos</h4></div>
      </div>
      <div class="row">
        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Valor de Deduções (R$)</label>
            <input type="text" name="valor_deducoes" class="form-control money"
                   value="{{ old('valor_deducoes', optional($item->servico)->valor_deducoes ? number_format($item->servico->valor_deducoes, 2, ',', '.') : '') }}">
          </div>
        </div>
        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Desconto Incondicional (R$)</label>
            <input type="text" name="desconto_incondicional" class="form-control money"
                   value="{{ old('desconto_incondicional', optional($item->servico)->desconto_incondicional ? number_format($item->servico->desconto_incondicional, 2, ',', '.') : '') }}">
          </div>
        </div>
        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Desconto Condicional (R$)</label>
            <input type="text" name="desconto_condicional" class="form-control money"
                   value="{{ old('desconto_condicional', optional($item->servico)->desconto_condicional ? number_format($item->servico->desconto_condicional, 2, ',', '.') : '') }}">
          </div>
        </div>
        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Outras Retenções (R$)</label>
            <input type="text" name="outras_retencoes" class="form-control money"
                   value="{{ old('outras_retencoes', optional($item->servico)->outras_retencoes ? number_format($item->servico->outras_retencoes, 2, ',', '.') : '') }}">
          </div>
        </div>
      </div>

      <hr>
      <div class="row">
        <div class="col-sm-12"><h4>Retenções de Impostos (%)</h4></div>
      </div>
      <div class="row">
        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>PIS (%)</label>
            <input type="text" name="aliquota_pis" class="form-control money"
                   value="{{ old('aliquota_pis', optional($item->servico)->aliquota_pis) }}">
          </div>
        </div>
        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>COFINS (%)</label>
            <input type="text" name="aliquota_cofins" class="form-control money"
                   value="{{ old('aliquota_cofins', optional($item->servico)->aliquota_cofins) }}">
          </div>
        </div>
        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>INSS (%)</label>
            <input type="text" name="aliquota_inss" class="form-control money"
                   value="{{ old('aliquota_inss', optional($item->servico)->aliquota_inss) }}">
          </div>
        </div>
        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>IR (%)</label>
            <input type="text" name="aliquota_ir" class="form-control money"
                   value="{{ old('aliquota_ir', optional($item->servico)->aliquota_ir) }}">
          </div>
        </div>
        <div class="col-sm-2 col-lg-2">
          <div class="form-group">
            <label>CSLL (%)</label>
            <input type="text" name="aliquota_csll" class="form-control money"
                   value="{{ old('aliquota_csll', optional($item->servico)->aliquota_csll) }}">
          </div>
        </div>
      </div>

      {{-- Linha 5: Exigibilidade / Competência --}}
      <div class="row">
        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Exigibilidade ISS <span class="text-danger">*</span></label>
            <select name="exigibilidade_iss" class="form-control" required>
              @foreach(\App\Models\Nfse::exigibilidades() as $k => $v)
                <option value="{{ $k }}"
                  @if(old('exigibilidade_iss', optional($item->servico)->exigibilidade_iss) == $k) selected @endif>
                  {{ $v }}
                </option>
              @endforeach
            </select＞
          </div>
        </div>

        <div class="col-sm-3 col-lg-3">
          <div class="form-group">
            <label>Data da Competência</label>
            <input type="date" name="data_competencia" class="form-control"
                   value="{{ old('data_competencia', optional($item->servico)->data_competencia) }}">
          </div>
        </div>
      </div>

      {{-- Resumo Fiscal --}}
      <hr>
      <div class="row">
        <div class="col-sm-12 col-lg-6">
          <h4>Resumo Fiscal</h4>
          <div class="table-responsive">
            <table class="table table-bordered">
              <tbody>
                <tr><th style="width:60%">Valor do Serviço</th><td id="res_valor_servico">R$ 0,00</td></tr>
                <tr><th>Base de Cálculo</th><td id="res_base">R$ 0,00</td></tr>
                <tr><th>PIS</th><td id="res_pis">R$ 0,00</td></tr>
                <tr><th>COFINS</th><td id="res_cofins">R$ 0,00</td></tr>
                <tr><th>INSS</th><td id="res_inss">R$ 0,00</td></tr>
                <tr><th>IR</th><td id="res_ir">R$ 0,00</td></tr>
                <tr><th>CSLL</th><td id="res_csll">R$ 0,00</td></tr>
                <tr><th>ISS Retido</th><td id="res_iss_retido">R$ 0,00</td></tr>
                <tr><th>ISSQN</th><td id="res_issqn">R$ 0,00</td></tr>
                <tr><th>Deduções</th><td id="res_deducoes">R$ 0,00</td></tr>
                <tr><th>Desc. Incond.</th><td id="res_desc_incond">R$ 0,00</td></tr>
                <tr><th>Desc. Cond.</th><td id="res_desc_cond">R$ 0,00</td></tr>
                <tr><th>Outras Retenções</th><td id="res_outras">R$ 0,00</td></tr>
                <tr class="bg-info"><th>Valor Líquido</th><td id="res_liquido"><strong>R$ 0,00</strong></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Campo extra de justificativa --}}
      <hr>
      <div class="row">
        <div class="form-group validated col-sm-12 col-lg-12">
            <label class="col-form-label" id="justificativa_substituicao">Justificativa da substituição <span class="text-danger">*</span></label>
            <select class="form-control custom-select" id="motivo" name="justificativa_substituicao" required>
              <option value="">Selecione um motivo...</option>
              <option value="Erro na emissão">Erro na emissão</option>
              <option value="Serviço não prestado">Serviço não prestado</option>
              <option value="Duplicidade de nota">Duplicidade de nota</option>
            </select>
          </div>
      </div>

      {{-- Ações --}}
      <hr>
      <div class="row">
        <div class="col-sm-12">
          <a href="{{ url('/nfse') }}" class="btn btn-secondary">
            Voltar
          </a>
          <button type="submit" class="btn btn-primary">
            Salvar e substituir NFSe
          </button>
        </div>
      </div>

    </form>
  @endcomponent
</section>

{{-- Modal para Adicionar Novo Cliente (Quick Add) --}}
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
  @include('contact.create', ['quick_add' => true, 'tipo' => 'customer'])
</div>

@endsection

@section('javascript')
<script>
(function() {
  function toNumber(v, element = null) {
    if (v === null || v === undefined) return 0;
    if (typeof v !== 'string') v = String(v);
    v = v.replace(/[^\d,.]/g, '');
    if (!v.includes(',') && !v.includes('.')) {
      const n = parseFloat(v) || 0;
      if (element && element.classList.contains('money') && n > 0 && n % 1 === 0 && n > 99) {
        return n / 100;
      }
      return n;
    }
    const parts = v.split('.');
    if (parts.length > 1) {
      v = parts.slice(0, -1).join('') + '.' + parts[parts.length - 1];
    }
    v = v.replace(',', '.');
    const n = parseFloat(v);
    return isNaN(n) ? 0 : n;
  }

  function money(n) {
    return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  }

  const q = s => document.querySelector(s);

  function recalc() {
    const valorServicoEl = q('input[name="valor_servico"]');
    const valorDeducoesEl = q('input[name="valor_deducoes"]');
    const descIncondEl = q('input[name="desconto_incondicional"]');
    const descCondEl = q('input[name="desconto_condicional"]');
    const outrasRetEl = q('input[name="outras_retencoes"]');
    const aliqPISEl = q('input[name="aliquota_pis"]');
    const aliqCOFINSEl = q('input[name="aliquota_cofins"]');
    const aliqINSSEl = q('input[name="aliquota_inss"]');
    const aliqIREl = q('input[name="aliquota_ir"]');
    const aliqCSLLEl = q('input[name="aliquota_csll"]');
    const aliqISSEl = q('input[name="aliquota_iss"]');
    const aliqISSQNEl = q('input[name="aliquota_issqn"]');

    const valorServico = toNumber(valorServicoEl?.value, valorServicoEl);
    const valorDeducoes = toNumber(valorDeducoesEl?.value, valorDeducoesEl);
    const descIncond = toNumber(descIncondEl?.value, descIncondEl);
    const descCond = toNumber(descCondEl?.value, descCondEl);
    const outrasRet = toNumber(outrasRetEl?.value, outrasRetEl);

    const aliqPIS = toNumber(aliqPISEl?.value, aliqPISEl);
    const aliqCOFINS = toNumber(aliqCOFINSEl?.value, aliqCOFINSEl);
    const aliqINSS = toNumber(aliqINSSEl?.value, aliqINSSEl);
    const aliqIR = toNumber(aliqIREl?.value, aliqIREl);
    const aliqCSLL = toNumber(aliqCSLLEl?.value, aliqCSLLEl);
    const aliqISS = toNumber(aliqISSEl?.value, aliqISSEl);
    const aliqISSQN = toNumber(aliqISSQNEl?.value, aliqISSQNEl);

    const issRetidoSel = q('select[name="iss_retido"]');
    const issRetidoFlag = issRetidoSel ? (issRetidoSel.value === '1') : false;

    const base = Math.max(valorServico - valorDeducoes, 0);

    const pis = base * (aliqPIS / 100);
    const cofins = base * (aliqCOFINS / 100);
    const inss = base * (aliqINSS / 100);
    const ir = base * (aliqIR / 100);
    const csll = base * (aliqCSLL / 100);
    const issRetido = issRetidoFlag ? base * (aliqISS / 100) : 0;
    const issqn = base * (aliqISSQN / 100);

    const liquido = base - (pis + cofins + inss + ir + csll + issRetido + issqn) - outrasRet - descIncond - descCond;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = money(val); };
    set('res_valor_servico', valorServico);
    set('res_base', base);
    set('res_pis', pis);
    set('res_cofins', cofins);
    set('res_inss', inss);
    set('res_ir', ir);
    set('res_csll', csll);
    set('res_iss_retido', issRetido);
    set('res_deducoes', valorDeducoes);
    set('res_desc_incond', descIncond);
    set('res_desc_cond', descCond);
    set('res_outras', outrasRet);
    set('res_issqn', issqn);
    set('res_liquido', liquido);
  }

  const selectors = [
    'input[name="valor_servico"]',
    'input[name="valor_deducoes"]',
    'input[name="desconto_incondicional"]',
    'input[name="desconto_condicional"]',
    'input[name="outras_retencoes"]',
    'input[name="aliquota_pis"]',
    'input[name="aliquota_cofins"]',
    'input[name="aliquota_inss"]',
    'input[name="aliquota_ir"]',
    'input[name="aliquota_csll"]',
    'input[name="aliquota_iss"]',
    'select[name="iss_retido"]',
    'input[name="aliquota_issqn"]'
  ];

  selectors.forEach(s => {
    const el = document.querySelector(s);
    if (el) {
      let timeoutId;
      el.addEventListener('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(recalc, 100);
      });
      el.addEventListener('change', recalc);
      el.addEventListener('blur', recalc);
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(recalc, 200);
  });

  recalc();
})();

document.addEventListener('DOMContentLoaded', function() {
  const textarea = document.getElementById('discriminacao');
  const counter = document.getElementById('char-count');

  function updateCounter() {
    const length = textarea.value.length;
    counter.textContent = length;

    if (length > 1800) {
      counter.style.color = '#dc3545';
    } else if (length > 1500) {
      counter.style.color = '#ffc107';
    } else {
      counter.style.color = '#6c757d';
    }
  }

  textarea.addEventListener('input', updateCounter);
  updateCounter();
});

$(document).ready(function() {
  var lastSearchTerm = '';
  
  $('#cliente_select').select2({
    ajax: {
      url: '/contacts/customers',
      dataType: 'json',
      delay: 250,
      data: function(params) {
        lastSearchTerm = params.term || '';
        return {
          q: params.term,
          page: params.page
        };
      },
      processResults: function(data) {
        return {
          results: data
        };
      },
      cache: true
    },
    templateResult: function(data) {
      if (!data.id) return data.text;
      var template = '<div>' + data.text;
      if (data.cpf_cnpj) {
        template += '<br><small class="text-muted">CPF/CNPJ: ' + data.cpf_cnpj + '</small>';
      }
      template += '</div>';
      return $(template);
    },
    templateSelection: function(data) {
      return data.text || data.id;
    },
    minimumInputLength: 1,
    language: {
      noResults: function() {
        var searchTerm = $('.select2-search__field').val();
        return '<button type="button" data-name="' + searchTerm + 
               '" class="btn btn-link add_new_customer_inline">' +
               '<i class="fa fa-plus-circle fa-lg"></i>&nbsp; Adicionar "' + searchTerm + '" como novo cliente</button>';
      },
      searching: function() {
        return 'Buscando...';
      },
      inputTooShort: function() {
        return 'Digite pelo menos 1 caractere para buscar';
      }
    },
    placeholder: 'Digite para buscar cliente',
    allowClear: true,
    escapeMarkup: function(markup) {
      return markup;
    }
  });

  $('#cliente_select').on('select2:select', function(e) {
    var clienteId = e.params.data.id;
    $.ajax({
      url: '/contacts/customer/' + clienteId,
      method: 'GET',
      dataType: 'json',
      success: function(cliente) {
        $('input[name="documento"]').val(cliente.documento || cliente.tax_number);
        $('input[name="razao_social"]').val(cliente.razao_social || cliente.name);
        $('input[name="nome_fantasia"]').val(cliente.nome_fantasia || '');
        $('input[name="email"]').val(cliente.email || '');
        $('input[name="cep"]').val(cliente.cep || cliente.zip_code);
        $('input[name="rua"]').val(cliente.rua || cliente.address_line_1);
        $('input[name="numero"]').val(cliente.numero || '');
        $('input[name="bairro"]').val(cliente.bairro || cliente.city);
        if (cliente.cidade_id) {
          $('select[name="cidade_id"]').val(cliente.cidade_id).trigger('change');
        }
        toastr.success('Dados do cliente carregados com sucesso!');
      },
      error: function() {
        toastr.error('Erro ao carregar dados do cliente');
      }
    });
  });

  $('#cliente_select').on('select2:clear', function() {
    $('input[name="documento"]').val('');
    $('input[name="razao_social"]').val('');
    $('input[name="nome_fantasia"]').val('');
    $('input[name="email"]').val('');
    $('input[name="cep"]').val('');
    $('input[name="rua"]').val('');
    $('input[name="numero"]').val('');
    $('input[name="bairro"]').val('');
    $('select[name="cidade_id"]').val('').trigger('change');
  });

  $('.add_new_customer').on('click', function(e) {
    e.preventDefault();
    $('#cliente_select').select2('close');
    var nomeCliente = $(this).attr('data-name') || '';
    $('.contact_modal').find('input#name').val(nomeCliente);
    $('.contact_modal').find('select#contact_type').val('customer')
        .closest('div.contact_type_div').addClass('hide');
    $('.contact_modal').modal('show');
    $(this).attr('data-name', '');
  });

  $(document).on('click', '.add_new_customer_inline', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var searchTerm = $(this).data('name');
    $('#cliente_select').select2('close');
    $('.add_new_customer').attr('data-name', searchTerm);
    $('.add_new_customer').trigger('click');
  });

  $('form#quick_add_contact').validate({
    rules: {
      contact_id: {
        remote: {
          url: '/contacts/check-contact-id',
          type: 'post',
          data: {
            contact_id: function () {
              return $('#contact_id').val();
            },
            hidden_id: function () {
              if ($('#hidden_id').length) {
                return $('#hidden_id').val();
              } else {
                return '';
              }
            },
          },
        },
      },
    },
    messages: {
      contact_id: {
        remote: 'ID do contato já existe',
      },
    },
    submitHandler: function (form) {
      $(form).find('button[type="submit"]').attr('disabled', true);
      var data = $(form).serialize();
      $.ajax({
        method: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: data,
        success: function (result) {
          if (result.success == true) {
            var newOption = new Option(
              result.data.id + ' - ' + result.data.name,
              result.data.id,
              true,
              true
            );
            $('#cliente_select').append(newOption);
            $('#cliente_select').val(result.data.id).trigger('change');
            $('div.contact_modal').modal('hide');
            toastr.success(result.msg);
          } else {
            toastr.error(result.msg);
          }
        },
        error: function() {
          toastr.error('Erro ao cadastrar cliente');
        }
      });
    }
  });

  $('.contact_modal').on('hidden.bs.modal', function () {
    $('form#quick_add_contact')
      .find('button[type="submit"]')
      .removeAttr('disabled');
    $('form#quick_add_contact')[0].reset();
  });
});
</script>
@endsection