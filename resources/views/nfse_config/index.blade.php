@extends('layouts.app')
@section('title', 'Configuração da NFSe')

@section('content')

<section class="content-header">
  <h1>NFSe
    <small>Configuração</small>
  </h1>
</section>

<section class="content">
  @component('components.widget', ['class' => 'box-primary', 'title' => ($item ? 'Editar' : 'Cadastrar') . ' Configuração NFSe'])

    <form method="post" action="{{ $item ? route('nfse-config.update', [$item->id]) : route('nfse-config.store') }}" enctype="multipart/form-data">
      @csrf
      @if($item) @method('put') @endif

      {{-- Erros --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <ul style="margin-bottom: 0;">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Identificação --}}
      <div class="row">
        <div class="col-sm-2">
          <div class="form-group">
            <label>Documento</label>
            <input id="documento" type="text" name="documento" class="form-control cpf_cnpj @error('documento') is-invalid @enderror"
                   value="{{ old('documento', $item->documento ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <label>Nome</label>
            <input id="nome" type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                   value="{{ old('nome', $item->nome ?? '') }}" required>
          </div>
        </div>

        <input type="hidden" name="empresa_id" value="{{ $business_id }}">

        <div class="col-sm-4">
          <div class="form-group">
            <label>Razão social</label>
            <input id="razao_social" type="text" name="razao_social" class="form-control @error('razao_social') is-invalid @enderror"
                   value="{{ old('razao_social', $item->razao_social ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>I.E</label>
            <input id="ie" type="text" name="ie" class="form-control @error('ie') is-invalid @enderror"
                   value="{{ old('ie', $item->ie ?? '') }}">
          </div>
        </div>
      </div>

      {{-- Dados fiscais e contato --}}
      <div class="row">
        <div class="col-sm-2">
          <div class="form-group">
            <label>I.M</label>
            <input type="text" name="im" class="form-control @error('im') is-invalid @enderror"
                   value="{{ old('im', $item->im ?? '') }}">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>CNAE</label>
            <input type="text" name="cnae" class="form-control @error('cnae') is-invalid @enderror"
                   value="{{ old('cnae', $item->cnae ?? '') }}">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>Cód. de trib do município</label>
            <input type="text" name="codigo_tributacao_municipio" class="form-control @error('codigo_tributacao_municipio') is-invalid @enderror"
                   value="{{ old('codigo_tributacao_municipio', $item->codigo_tributacao_municipio ?? '') }}">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>Item LC</label>
            <input type="text" name="item_lc" class="form-control @error('item_lc') is-invalid @enderror"
                   value="{{ old('item_lc', $item->item_lc ?? '') }}">
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>Telefone</label>
            <input type="tel" id="telefone" name="telefone" class="form-control telefone @error('telefone') is-invalid @enderror"
                   value="{{ old('telefone', $item->telefone ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <label>Email</label>
            <input id="email" type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $item->email ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <label>Regime</label>
            <select class="form-control" name="regime">
              @php $reg = old('regime', $item->regime ?? 'simples'); @endphp
              <option value="simples" {{ $reg==='simples' ? 'selected' : '' }}>Simples</option>
              <option value="normal"  {{ $reg==='normal'  ? 'selected' : '' }}>Normal</option>
            </select>
          </div>
        </div>

      {{-- Endereço --}}
        <div class="col-sm-2">
          <div class="form-group">
            <label>CEP</label>
            <input id="cep" type="tel" name="cep" class="form-control cep @error('cep') is-invalid @enderror"
                   value="{{ old('cep', $item->cep ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <label>Rua</label>
            <input id="rua" type="text" name="rua" class="form-control @error('rua') is-invalid @enderror"
                   value="{{ old('rua', $item->rua ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>Número</label>
            <input id="numero" type="tel" name="numero" class="form-control @error('numero') is-invalid @enderror"
                   value="{{ old('numero', $item->numero ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            <label>Bairro</label>
            <input id="bairro" type="text" name="bairro" class="form-control @error('bairro') is-invalid @enderror"
                   value="{{ old('bairro', $item->bairro ?? '') }}" required>
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group">
            <label>Complemento</label>
            <input type="text" name="complemento" class="form-control @error('complemento') is-invalid @enderror"
                   value="{{ old('complemento', $item->complemento ?? '') }}">
          </div>
        </div>

      {{-- Cidade --}}
        <div class="col-sm-5">
          <div class="form-group">
            <label>Cidade</label>
            <select class="form-control select2 @error('cidade_id') is-invalid @enderror" id="kt_select2_1" name="cidade_id" required>
              <option value="">Selecione a cidade</option>
              @foreach($cidades as $c)
                <option value="{{ $c->id }}"
                  @if(old('cidade_id', $item->cidade_id ?? null) == $c->id) selected @endif>
                  {{ $c->nome }} ({{ $c->uf }})
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <label>Login prefeitura</label>
            <input id="login_prefeitura" type="text" name="login_prefeitura"
                   class="form-control @error('login_prefeitura') is-invalid @enderror"
                   value="{{ old('login_prefeitura', $item->login_prefeitura ?? '') }}">
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            <label>Senha prefeitura</label>
            <input id="senha_prefeitura" type="text" name="senha_prefeitura"
                   class="form-control @error('senha_prefeitura') is-invalid @enderror"
                   value="{{ old('senha_prefeitura', $item->senha_prefeitura ?? '') }}">
          </div>
        </div>
      </div>

      {{-- Logo / Token --}}
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label>Logo (.jpg)</label>
            <div>
              <div style="width:120px;height:120px;background-size:cover;background-position:center;
                          background-image:url('{{ isset($item) && $item->logo ? url('/logos/'.$item->logo) : url('/imgs/logo.png') }}');border:1px solid #ddd;border-radius:6px">
              </div>
              <br>
              <input type="file" name="file" accept=".jpg,.png">
            </div>
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <label>Token do emitente</label>
            @if(isset($item) && $item->token)
            <input class="form-control" type="password" name="token" value="{{ $item->token ?? '' }}" @if(!$item) @endif disabled>
            @else
            <input class="form-control" type="text" name="token" value="{{ $item->token ?? '' }}">
            @endif
          </div>

          @if(!empty($item) && !empty($item->token))
            <a href="{{ route('nfse-config.certificado') }}" class="btn btn-danger btn-sm">
              <i class="la la-file"></i> Upload de certificado
            </a>
          @endif
        </div>
      </div>

      {{-- Rodapé --}}
      <div class="row">
        <div class="col-sm-12">
          <a href="{{ route('nfse-config.index') }}" class="btn btn-secondary">Voltar</a>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </div>

    </form>
  @endcomponent
</section>

@endsection

@section('javascript')
<script>
  // Auto-preenche a partir do CNPJ
  $('#documento').on('blur', function() {
    let doc = ($(this).val() || '').replace(/[^0-9]/g,'');
    if (doc.length !== 14) return;

    $.get('https://publica.cnpj.ws/cnpj/' + doc)
      .done(function(data) {
        if (!data || !data.estabelecimento) return;

        const est = data.estabelecimento;

        let ie = '';
        if (Array.isArray(est.inscricoes_estaduais) && est.inscricoes_estaduais.length) {
          ie = est.inscricoes_estaduais[0].inscricao_estadual || '';
        }

        $('#ie').val(ie);
        $('#razao_social').val(data.razao_social || '');
        $('#nome').val(est.nome_fantasia || '');
        $('#rua').val(((est.tipo_logradouro || '') + ' ' + (est.logradouro || '')).trim());
        $('#numero').val(est.numero || '');
        $('#bairro').val(est.bairro || '');

        const rawCep = (est.cep || '').replace(/[^\d]+/g, '');
        if (rawCep.length === 8) {
          $('#cep').val(rawCep.substring(0,5) + '-' + rawCep.substring(5));
        }

        $('#email').val(est.email || '');
        $('#telefone').val(est.telefone1 || '');

        if (est.cidade && est.cidade.ibge_id) {
          findCidadeCodigo(est.cidade.ibge_id);
        }
      });
  });

  function findCidadeCodigo(codigo_ibge){
    $.get('{{ url('cidades/cidadePorCodigoIbge') }}/' + codigo_ibge)
      .done(function(res) {
        if (!res || !res.id) return;
        $('#kt_select2_1').val(res.id).trigger('change');
      });
  }
</script>
@endsection