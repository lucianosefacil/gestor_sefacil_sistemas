<div class="modal-dialog" role="document">
	<div class="modal-content">

		{!! Form::open(['url' => action('CashRegisterController@storeSangriaSuprimento'), 'method' => 'post', 
		'id' => 'add_cash_register_form' ]) !!}
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">Sangria ou suprimento de caixa</h4>
		</div>
		<div class="modal-body">

			<div class="row">

				<div class="col-sm-6">
					<div class="form-group">
						{!! Form::label('type','Tipo*') !!}
						{!! Form::select('type', ['sangria' => 'Sangria', 'suprimento' => 'Suprimento'], null, ['class' => 'form-control',
						'placeholder' => 'Tipo', 'required']); !!}
					</div>
				</div>

				<div class="col-sm-6">
					<div class="form-group">
						{!! Form::label('value','Valor*') !!}
						{!! Form::tel('value', null, ['class' => 'form-control money',
						'placeholder' => 'Valor', 'required', 'data-mask' => '00000,00']); !!}
					</div>
				</div>

				<div class="col-sm-12">
					<div class="form-group">
						{!! Form::label('note','Observação') !!}
						{!! Form::tel('note', null, ['class' => 'form-control',
						'placeholder' => 'Observação']); !!}
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
			<button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
		</div>
		{!! Form::close() !!}
	</div>
</div>

<script type="text/javascript">
	$('#value').mask('000000000,00', {reverse: true})
</script>