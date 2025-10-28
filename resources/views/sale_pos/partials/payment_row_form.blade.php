<div class="row">
	<input type="hidden" class="payment_row_index" value="{{$row_index}}">
	@php
	$col_class = 'col-md-6';
	if(!empty($accounts)){
		$col_class = 'col-md-4';
	}
	@endphp
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("amount_$row_index" , 'Valor' . ':*') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				@if ($payment_line['method'] == 'aguardando_pagamento')
				{!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount']), [
                    'class' => 'form-control payment-amount input_number',
                    'required',
                    'id' => "total_produto_add",
                    'placeholder' => __('sale.amount'),
                ]) !!}
                @else
				{!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount']), [
                    'class' => 'form-control payment-amount input_number',
                    'required',
                    'id' => "amount_$row_index",
                    'placeholder' => __('sale.amount'),
                ]) !!}
                @endif
			</div>
		</div>
	</div>
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("method_$row_index" , 'Forma de pagamento' . ':*') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-list"></i>
				</span>
				{!! Form::select("payment[$row_index][method]", $payment_types, $payment_line['method'], ['class' => 'form-control col-md-12 payment_types_dropdown', 'required', 'id' => "method_$row_index", 'style' => 'width:100%;']); !!}
			</div>
		</div>
	</div>
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("vencimento_$row_index" , 'Vencimento:*') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-calendar"></i>
				</span>
				{!! Form::text("payment[$row_index][vencimento]", $payment_line['vencimento'], ['class' => 'form-control payment-vencimento', '', 'id' => "payment_$row_index", 'required', 'placeholder' => 'Vencimento', 'data-mask="00/00/0000"', 'data-mask-reverse="true"']); !!}

			</div>
		</div>
	</div>
	
	@if(!empty($accounts))
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("account_$row_index" , __('lang_v1.payment_account') . ':') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("payment[$row_index][account_id]", $accounts, !empty($payment_line['account_id']) ? $payment_line['account_id'] : '' , ['class' => 'form-control select2', 'id' => "account_$row_index", 'style' => 'width:100%;']); !!}
			</div>
		</div>
	</div>
	@endif
	<div class="clearfix"></div>
	@include('sale_pos.partials.payment_type_details')
	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("note_$row_index", 'Observação de pagamento:') !!}
			{!! Form::textarea("payment[$row_index][note]", $payment_line['note'], ['class' => 'form-control', 'rows' => 3, 'id' => "note_$row_index"]); !!}
		</div>
	</div>
	
	<!-- TEF Integration Section -->
	@if(!empty($business) && $business->enable_tef_pdv == 1)
	<div class="col-md-12 tef-section" style="display: none;">
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title"><i class="fa fa-credit-card"></i> TEF - Transferência Eletrônica de Fundos</h3>
				<small>A forma de pagamento será selecionada no terminal GETPAY</small>
			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-md-12">
						<button type="button" class="btn btn-primary btn-processar-tef" data-row="{{$row_index}}">
							<i class="fa fa-credit-card"></i> Processar TEF
						</button>
						<button type="button" class="btn btn-info btn-tef-adm" data-row="{{$row_index}}">
							<i class="fa fa-cog"></i> Operações ADM
						</button>
					</div>
				</div>
				<div class="tef-status mt-3" style="display: none;">
					<div class="alert alert-info">
						<i class="fa fa-spinner fa-spin"></i> Processando TEF...
					</div>
				</div>
			</div>
		</div>
	</div>
	@endif
</div>