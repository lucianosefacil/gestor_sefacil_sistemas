@extends('layouts.relatorio_default')
@section('content')

<table class="table" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th class="text-left">Data</th>
			<th class="text-left">Número</th>
			<th class="text-left">Chave</th>
			<th class="text-left">Recibo</th>
			<th class="text-left">Estado</th>
			<th class="text-left">Valor</th>
		</tr>
	</thead>
	<tbody>
		@foreach($notasAprovadas as $n)
		<tr>
			<td>{{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i:s')}}</td>
			<td>{{$n->numero_nfce}}</td>
			<td>{{$n->chave}}</td>
			<td>{{$n->recibo}}</td>
			<td>{{$n->estado}}</td>
			<td>{{ number_format($n->final_total, 2, ',', '.') }}</td>

		</tr>
		@endforeach
	</tbody>
</table>
<h4>Soma total: <strong>R$ {{ number_format($notasAprovadas->sum('final_total'), 2, ',', '.') }}</strong></h4>

@endsection