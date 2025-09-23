<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('ServicosController@update', [$servico->id]), 'method' => 'put', 'id' => 'servicos_form']) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'Editar Serviços' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('codigo', __( 'Código(SKU)' ) . ':') !!}
                        {!! Form::text('codigo', $servico->codigo, ['class' => 'form-control', 'placeholder' => __( 'Código (opcional)' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('valor', 'Valor' . ':*') !!}
                        {!! Form::text('valor', $servico->valor, ['class' => 'form-control required moeda', 'placeholder' =>'Valor']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('nome', 'Descrição' . ':*') !!}
                        {!! Form::text('nome', $servico->nome, ['class' => 'form-control required', 'placeholder' => 'Descrição do serviço']); !!}
                    </div>
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

</script>
