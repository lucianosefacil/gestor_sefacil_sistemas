@extends('ordem_servico.imprimir_default')
@section('content')

<div class="row">
    <div style="margin-left: -20px">
        -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-5px">
        <label style="color: black">PLACA DO VEÍCULO: {{$ordem->veiculo ? $ordem->veiculo->placa : '--'}}</strong> -- </label>
        <label style="color: black">MARCA: <strong>{{$ordem->veiculo ? $ordem->veiculo->marca : '--'}}</strong> -- </label>
        <label style="color: black">MODELO: <strong>{{$ordem->veiculo ? $ordem->veiculo->modelo : '--'}}</strong> -- </label>
    </div>
    <div style="margin-left:-20px;">
        -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-10px">
        <label style="color: black"><strong id="nome-exame">OBSERVAÇÕES:</strong></label>
        <label style="color: black">{{$ordem->observacao}}</label>
    </div>
    <div style="margin-left: -20px">
        -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-10px">
        <table class="" style="margin-top:-15px">
            <thead>
                <tr>
                    <th class="" style="width: 95px;">
                        <label style="color: black" for="">Cod/Ref</label>
                    </th>
                    <th class="" style="width: 350px;">
                        <label style="color: black" for="">Descrição</label>
                    </th>
                    <th class="" style="width: 80px;">
                        <label style="color: black" for="">Qtd.</label>
                    </th>
                    <th class="" style="width: 80px;">
                        <label style="color: black" for="">Vl Uni</label>
                    </th>
                    <th class="" style="width: 80px;">
                        <label style="color: black" for="">Vl Liq.</label>
                    </th>
                </tr>
            </thead>
            @php
            $somaItens = 0;
            @endphp
            <tbody>
                @foreach($ordem->itens as $i)
                <tr>
                    <td style="margin-top: -15px">
                        <label style="color: black" for="">{{$i->produto->id}} </label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{$i->produto->name}}</label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{number_format($i->quantidade, 2, ',', '.')}}</label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{number_format($i->valor_unitario, 2, ',', '.')}}</label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{number_format($i->sub_total, 2, ',', '.')}}</label>
                    </td>
                </tr>
                @php
                $somaItens += $i->sub_total;
                @endphp
                @endforeach
            </tbody>
        </table>
        <label style="color: black; magin-top:-15px" for="">TOTAL PEÇAS: {{number_format($somaItens, 2, ',', '.')}}</label>
    </div>
    ---------------------------------------------------------------------------------------------------------------------------------------------------------

    <div class="col s12" style="margin-top:-10px">
        <table>
            @php
            $somaServicos = 0;
            @endphp
            <tbody>
                @foreach($ordem->servicos as $s)
                <tr>
                    <td style="width: 95px;">
                        <label style="color: black" for="">{{$s->servico_id }}</label>
                    </td>
                    <td style="width: 320px;">
                        <label style="color: black" for="">{{$s->servico->nome}}</label>
                    </td>
                    <td class="">
                        <label style="color: black" for="">{{number_format($s->quantidade, 2, ',', '.')}}</label>
                    </td>
                    <td class="b-top">
                        <label style="color: black" for="">{{number_format($s->valor_unitario, 2, ',', '.')}}</label>
                    </td>
                    <td class="b-top">
                        <label style="color: black" for="">{{number_format($s->sub_total, 2, ',', '.')}}</label>
                    </td>
                </tr>
                @php
                $somaServicos += $s->sub_total;
                @endphp
                @endforeach
            </tbody>
        </table>
        <label style="color: black; magin-top:-15px" for="">TOTAL SERVIÇOS: {{number_format($somaServicos, 2, ',', '.')}}</label>
    </div>
    ---------------------------------------------------------------------------------------------------------------------------------------------------------
    <div class="col s12 right-align">
        <label style="color: black" class=""><strong>Valor Total da OS:</strong>
        {{number_format($ordem->valor,2 ,',', '.')}}
        </label>
    </div>
    <div class="col s6 center-align">
        <span><strong>___________________________________________________</strong></span><br>
        <span><strong>Assinatura</strong></span>
        <br>
    </div>
</div>



<label for="">Recorte aqui</label>
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -             
{{-- parte do funcionario  --}}

<div class="row">
    <h5>Via Profissional</h5>
    <div style="margin-left: -20px">
        -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-5px">
        <label style="color: black">PLACA DO VEÍCULO: {{$ordem->veiculo ? $ordem->veiculo->placa : '--'}}</strong> -- </label>
        <label style="color: black">MARCA: <strong>{{$ordem->veiculo ? $ordem->veiculo->marca : '--'}}</strong> -- </label>
        <label style="color: black">MODELO: <strong>{{$ordem->veiculo ? $ordem->veiculo->modelo : '--'}}</strong> -- </label>
    </div>
    <div style="margin-left:-20px;">
        -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-10px">
        <label style="color: black"><strong id="nome-exame">OBSERVAÇÕES:</strong></label>
        <label style="color: black">{{$ordem->observacao}}</label>
    </div>
    <div style="margin-left: -20px">
        ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    </div>
    <div class="col s12" style="margin-top:-10px">
        <table class="" style="margin-top:-15px">
            <thead>
                <tr>
                    <th class="" style="width: 95px;">
                        <label style="color: black" for="">Cod/Ref</label>
                    </th>
                    <th class="" style="width: 350px;">
                        <label style="color: black" for="">Descrição</label>
                    </th>
                    <th class="" style="width: 80px;">
                        <label style="color: black" for="">Qtd.</label>
                    </th>
                   
                </tr>
            </thead>
            @php
            $somaItens = 0;
            @endphp
            <tbody>
                @foreach($ordem->itens as $i)
                <tr>
                    <td style="margin-top: -15px">
                        <label style="color: black" for="">{{$i->produto->id}} </label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{$i->produto->name}}</label>
                    </td>
                    <td>
                        <label style="color: black" for="">{{number_format($i->quantidade, 2, ',', '.')}}</label>
                    </td>
                </tr>
                @php
                $somaItens += $i->sub_total;
                @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    ---------------------------------------------------------------------------------------------------------------------------------------------------------
    <div class="col s12" style="margin-top:-10px">
        <table>
            @php
            $somaServicos = 0;
            @endphp
            <tbody>
                @foreach($ordem->servicos as $s)
                <tr>
                    <td style="width: 95px;">
                        <label style="color: black" for="">{{$s->servico_id }}</label>
                    </td>
                    <td style="width: 465px;">
                        <label style="color: black" for="">{{$s->servico->nome}}</label>
                    </td>
                    <td class="">
                        <label style="color: black" for="">{{number_format($s->quantidade, 2, ',', '.')}}</label>
                    </td>
                </tr>
                @php
                $somaServicos += $s->sub_total;
                @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
    <div style="margin-top: 15px" class="col s6 center-align">
        <span><strong>___________________________________________________</strong></span><br>
        <span><strong>Assinatura</strong></span>
        <br>
    </div>
</div>
@endsection
