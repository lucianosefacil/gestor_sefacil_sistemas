<div class="pos-tab-content">
    <h4>Configurações da NFSe</h4>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                {!! Form::label('token_nfse', 'Token NFSe:') !!}
                {!! Form::text('token_nfse', $business->token_nfse, ['class' => 'form-control', 'placeholder' => 'Token NFSe']) !!}
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('ultimo_numero_nfse', 'Ultimo Número NFSe:') !!}
                {!! Form::text('ultimo_numero_nfse', $business->ultimo_numero_nfse, [
                    'class' => 'form-control',
                    'placeholder' => 'Ultimo Número NFSe',
                ]) !!}
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('numero_serie_nfse', 'Número de Série NFSe:') !!}
                {!! Form::text('numero_serie_nfse', $business->numero_serie_nfse, [
                    'class' => 'form-control',
                    'placeholder' => 'Número de Série NFSe',
                ]) !!}
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                {!! Form::label('numero_rps', 'Número de RPS:') !!}
                {!! Form::text('numero_rps', $business->numero_rps, [
                    'class' => 'form-control',
                    'placeholder' => 'Número de RPS',
                ]) !!}
            </div>
        </div>

    </div>
</div>
