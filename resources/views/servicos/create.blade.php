<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('ServicosController@store'), 'method' => 'post', 'id' => '']) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'Adicionar Serviço' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('codigo', __( 'Código(SKU)' ) . ':') !!}
                        {!! Form::text('codigo', null, ['class' => 'form-control', 'placeholder' => __( 'Código (opcional)' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('valor', 'Valor' . ':*') !!}
                        {!! Form::text('valor', null, ['class' => 'form-control required moeda', 'placeholder' =>'Valor']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('nome', __( 'Descrição' ) . ':*') !!}
                        {!! Form::text('nome', null, ['class' => 'form-control required', 'placeholder' => 'Descrição do serviço']); !!}
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

</script>