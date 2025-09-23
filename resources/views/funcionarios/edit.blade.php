<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('FuncionarioController@update', [$funcionario->id]), 'method' => 'put', 'id' => '']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'Editar Funcionário' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('codigo', __( 'Código' ) . ':') !!}
                        {!! Form::text('codigo', $funcionario->codigo, ['class' => 'form-control', 'placeholder' => __( 'Código (opcional)' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('celular', 'Celular' . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </span>
                            {!! Form::text('celular', $funcionario->celular, ['class' => 'form-control fone', 'placeholder' => 'Telefone Fixo']); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('nome', __( 'Nome' ) . ':*') !!}
                        {!! Form::text('nome', $funcionario->nome, ['class' => 'form-control required', 'placeholder' => 'Nome']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cpf', __( 'CPF' ) . ':') !!}
                        {!! Form::text('cpf', $funcionario->cpf, ['class' => 'form-control cpf', 'placeholder' => __( 'CPF' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('comissao', 'Comissão' . ':') !!}
                        {!! Form::text('comissao', $funcionario->percentual_comissao, ['class' => 'form-control required moeda', 'placeholder' =>'Comissão']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    {!! Form::label('status', __('Status').':*', ['style' => 'margin-left:20px;'])!!}
                    <br>
                    <label class="radio-inline">
                        {!! Form::radio('status', 'ativo', ($funcionario->status == 'ativo') ? true : false, [ 'class' => 'input-icheck']); !!}
                        @lang('Ativo')
                    </label>
                    <label class="radio-inline radio_btns">
                        {!! Form::radio('status', 'inativo',  ($funcionario->status == 'inativo') ? true : false, [ 'class' => 'input-icheck']); !!}
                        @lang('Inativo')
                    </label>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
        {!! Form::close() !!}
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
    $(document).on("focus", ".moeda", function() {
        $(this).mask("00000000,00", {
            reverse: true
        })
    });

    var SPMaskBehavior = function (val) {
    return val.replace(/\D/g, "").length === 11
    ? "(00) 00000-0000"
    : "(00) 0000-00009";
},
spOptions = {
    onKeyPress: function (val, e, field, options) {
        field.mask(SPMaskBehavior.apply({}, arguments), options);
    },
};

$(".fone").mask(SPMaskBehavior, spOptions);

$(document).on("focus", ".cpf", function () {
    $(this).mask("000.000.000-00", { reverse: true })
});

</script>
