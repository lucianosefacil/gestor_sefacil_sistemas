<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('VeiculoOsController@store'), 'method' => 'post', 'id' => '']) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'Adicionar Veículo' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('placa', __( 'Placa' ) . ':*') !!}
                        {!! Form::text('placa', null, ['class' => 'form-control placa', 'required', 'placeholder' => 'Placa' ]); !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('uf', 'UF' . ':') !!}
                        {!! Form::select('uf', $ufs, '', ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
                      </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('modelo', __( 'Modelo' ) . ':*') !!}
                        {!! Form::text('modelo', null, ['class' => 'form-control', 'required', 'placeholder' => 'Modelo']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('marca', __( 'Marca' ) . ':') !!}
                        {!! Form::text('marca', null, ['class' => 'form-control', 'placeholder' => __( 'Marca' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('cor', 'Cor' . ':') !!}
                        {!! Form::text('cor', null, ['class' => 'form-control', 'placeholder' =>'Cor']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('ano_fabricacao', __( 'Ano Fabricação' ) . ':') !!}
                        {!! Form::text('ano_fabricacao', null, ['class' => 'form-control ano', 'placeholder' => 'Ano Fabricação']); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('ano_modelo', 'Ano Modelo' . ':') !!}
                        {!! Form::text('ano_modelo', null, ['class' => 'form-control ano', 'placeholder' =>'Ano Modelo']); !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('cliente_id', 'Cliente' . ':') !!}
                        <select name="cliente_id" id="customer_id" class="form-control">
                            @foreach ($clientes as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('observacao', 'Observação' . ':') !!}
                        {!! Form::text('observacao', null, ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">


$(document).on("focus", ".moeda", function () {
    $(this).mask("00000000,00", { reverse: true })
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

$(document).on("focus", ".ano", function () {
    $(this).mask("0000", { reverse: true })
});

$(document).on("focus", ".placa", function () {
    $(this).mask("AAA-AAAA", { reverse: true })
});

</script>