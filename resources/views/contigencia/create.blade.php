@extends('layouts.app')
@section('title', 'Ativar Contigência')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Ativar Contigência</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('ContigenciaController@store'), 'method' => 'post', 'id' => 'natureza_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])
      
      

      <div class="col-md-2 customer_fields">
        <div class="form-group">

          {!! Form::label('tipo', 'Tipo' . ':') !!}
          {!! Form::select('tipo', \App\Models\Contigencia::tiposContigencia(), '', ['id' => 'tipo', 'class' => 'form-control', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2 customer_fields">
        <div class="form-group">

          {!! Form::label('documento', 'Documento' . ':') !!}
          {!! Form::select('documento', ['NFe' => 'NFe', 'NFCe' => 'NFCe'], '', ['id' => 'documento', 'class' => 'form-control', 'required']); !!}
        </div>
      </div>


      <div class="col-md-8">
        <div class="form-group">
          {!! Form::label('motivo', 'Motivo' . '*:') !!}
          {!! Form::text('motivo', null, ['class' => 'form-control', 'required', 'placeholder' => 'Motivo']); !!}
        </div>
      </div>


      <div class="col-md-12">
        <button type="submit" class="btn btn-primary pull-right" id="submit_button">@lang( 'messages.save' )</button>

      </div>
      @endcomponent
    </div>
  </div>

  {!! Form::close() !!}
  @stop
  @section('javascript')
  <script type="text/javascript">
    $(document).on("change", "#tipo", function() {
      let tipo = $(this).val()
      $("#documento option").removeAttr('disabled');
      if(tipo == 'OFFLINE'){
        $("#documento").val('NFCe').change();
        $("#documento option[value='NFe']").attr('disabled', 1);
      }else{
        $("#documento").val('NFe').change();
        $("#documento option[value='NFCe']").attr('disabled', 1);
      }
    })

  </script>
  @endsection

