@extends('layouts.app')
@section('title', 'Editar estado fiscal')

@section('css')
<style type="text/css">
	.table-responsive{
		height: 400px !important;
	}

	.sticky-col {
		position: -webkit-sticky;
		position: sticky;
	}

	.first-col {
		width: 400px;
		min-width: 400px;
		max-width: 400px;
		left: 0px;
	}

	.second-col {
		width: 150px;
		min-width: 150px;
		max-width: 150px;
		left: 100px;
	}
</style>
@endsection

@section('content')

<section class="content">

	{!! Form::open(['url' => route('devolucao.update-fiscal', [$item->id]), 'method' => 'put', 'id' => 'add_purchase_form', 'files' => true ]) !!}
	@component('components.widget', ['class' => 'box-primary'])

	<div class="row">


		<div class="col-sm-12">
			<div class="form-group">
				<h3 class="box-title">Dados do Documento</h3>

				<div class="row">
					<div class="col-sm-12">

						<span>Chave: <strong>{{ $item->chave_nf_entrada }}</strong></span><br>
						<span>Valor: <strong>{{number_format($item->valor_integral, 2, ',', '.')}}</strong></span><br>
						<span>Número: <strong>{{ $item->nNf }}</strong></span><br>
						<span>Estado Atual: <strong>{{ $item->estado() }}</strong></span><br>
						
					</div>

				</div>
			</div>
		</div>

		
		<div class="row">
			<div class="col-sm-12">

				<div class="form-group">

					
					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('estado', 'Estado'. ':*') !!}
							{!! Form::select('estado', ['0' => 'Novo', '1' => 'Aprovado', '2' => 'Rejeitado', '3' => 'Cancelado'], null, ['id' => 'tipo', 'class' => 'form-control select2', 'required']); !!}
						</div>
					</div>

					<div class="col-sm-2">
						<div class="form-group">
							{!! Form::label('file', 'Arquivo XML'. ':*') !!}
							{!! Form::file('file', null, ['id' => 'tipo', 'class' => 'form-control', 'required', 'accept' => 'xml']); !!}
						</div>
					</div>
					
					
				</div>
			</div>
		</div>



		<div class="row">
			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary pull-right btn-flat">Salvar Devolução</button>
			</div>
		</div>


	</div>

	@endcomponent
	{!! Form::close() !!}


</section>

@section('javascript')



@endsection


<!-- /.content -->

@endsection
