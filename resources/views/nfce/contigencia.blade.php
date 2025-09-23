@extends('layouts.app')
@section('title', 'NFCe Contigência')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>NFCe
        <small>Lista de Contigência</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])

    <form action="" method="get">
        <div class="row">
            <div class="col-sm-2 col-lg-2">
                <div class="form-group">
                    <label for="product_custom_field2">Data inicial:</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input class="form-control start-date-picker" placeholder="Data inicial" value="{{{ isset($data_inicio) ? $data_inicio : ''}}}" data-mask="00/00/0000" name="data_inicio" type="text" id="">
                    </div>

                </div>
            </div>
            <div class="col-sm-2 col-lg-2">
                <div class="form-group">
                    <label for="product_custom_field2">Data final:</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input class="form-control start-date-picker" placeholder="Data final" data-mask="00/00/0000" name="data_final" type="text" value="{{{ isset($data_final) ? $data_final : ''}}}">
                    </div>

                </div>
            </div>

            <div class="col-sm-2 col-lg-2">
                <div class="form-group"><br>
                    <button style="margin-top: 5px;" class="btn btn-block btn-primary">Filtrar</button>
                </div>
            </div>

        </div>
    </form>

    <div class="table-responsive">
        @if($contigencia)
        <h4 class="text-danger">Contigência esta ativa</h4>
        @endif
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th></th>
                    <th>Data</th>
                    <th>Número</th>
                    <th>Valor</th>
                    <th>Chave</th>
                    <th>Estado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $n)
                <tr>
                    <td><input type="checkbox" name="check" value="{{ $n->id }}" class="check-nfce"></td>
                    <td>{{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i:s') }}</td>
                    <td>{{$n->numero_nfce}}</td>
                    <td>{{number_format($n->final_total, 2, ',', '.')}}</td>
                    <td>{{$n->chave}}</td>
                    <td>{{$n->estado}} <span class="text-danger">em contigência</span></td>
                    <td>

                        <a class="btn btn-primary btn-sm" title="Imprimir" target="_blank" href="{{route('nfce.imprimir', $n->id)}}">
                            <i class="fa fa-print" aria-hidden="true"></i>
                        </a>

                        <a class="btn btn-info btn-sm" title="Xml" target="_blank" href="{{route('contigencia.xml', $n->id)}}">
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </a>
                        @if(!$contigencia)
                        <button class="btn btn-success btn-sm btn-send" onclick="send('{{$n->id}}')">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        </button>
                        @endif
                        
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Soma total: <strong>R$ {{ number_format($data->sum('final_total'), 2, ',', '.') }}</strong></h4>
        <button class="btn btn-success btn-send-all" disabled>
            <i class="fa fa-paper-plane" aria-hidden="true"></i>
            Enviar Selecionados
        </button>
    </div>
    @endcomponent
</section>
<input type="hidden" id="token" value="{{csrf_token()}}" name="">

@stop

@section('javascript')
<script type="text/javascript">
    var selecionados = []
    $(function(){
        percorreChecekbox()
    })

    $('.check-nfce').click(() => {
        percorreChecekbox()
    })

    function percorreChecekbox(){
        selecionados = []
        $('.check-nfce').each(function(e, i){
            if(i.checked){
                selecionados.push(i.value)
            }
        })
        console.log(selecionados.length)
        if(selecionados.length > 0){
            $('.btn-send-all').removeAttr('disabled')
        }else{
            $('.btn-send-all').attr('disabled', 1)
        }
    }

    function send(id){
        $('.btn-send').attr('disabled', 1)
        var path = window.location.protocol + '//' + window.location.host

        $.ajax
        ({
            type: 'POST',
            data: {
                id: id,
                _token: $('#token').val()
            },
            url: '{{ route("nfce.transmitir-contigencia") }}',
            dataType: 'json',
            success: function(e){
                $('.btn-send').removeAttr('disabled')
                console.log(e)
                swal("Sucesso", "NFCe emitida, recibo: " + e, "success")
                .then(() => {
                    window.open(path + '/nfce/imprimir/'+id)
                    location.reload()
                })

            }, error: function(e){
                $('.btn-send').removeAttr('disabled')
                console.log(e)

                if(e.status == 401){
                    console.log(e.responseJSON.protocolo)
                    let jsError = JSON.parse(e.responseJSON.protocolo)

                    swal("Erro ao transmitir", "[" + jsError.protNFe.infProt.cStat +  "]" + jsError.protNFe.infProt.xMotivo, "error");

                }else if(e.status == 402){
                    swal("Erro ao transmitir", e.responseJSON, "error")
                }
                else{
                    try{
                        let jsError = JSON.parse(e.responseJSON)

                        swal("Erro ao transmitir", jsError.protNFe.infProt.xMotivo, "error")
                    }catch{
                        swal("Erro ao transmitir", e.responseJSON, "error");
                    }

                }

            }
        })
    }

    $('.btn-send-all').click(() => {
        console.clear()
        $('.btn-send-all').attr('disabled', 1)

        $.ajax
        ({
            type: 'POST',
            data: {
                selecionados: selecionados,
                _token: $('#token').val()
            },
            url: '{{ route("nfce.transmitir-contigencia-lote") }}',
            dataType: 'json',
            success: function(e){
                $('.btn-send-all').removeAttr('disabled')
                console.log(e)
                swal("Sucesso", "Lote processado com sucesso!", "success")

            }, error: function(e){
                $('.btn-send-all').removeAttr('disabled')
                console.log(e)

                if(e.status == 401){
                    console.log(e.responseJSON.protocolo)
                    let jsError = JSON.parse(e.responseJSON.protocolo)

                    swal("Erro ao transmitir", "[" + jsError.protNFe.infProt.cStat +  "]" + jsError.protNFe.infProt.xMotivo, "error");

                }else if(e.status == 402){
                    swal("Erro ao transmitir", e.responseJSON, "error")
                }
                else{
                    try{
                        let jsError = JSON.parse(e.responseJSON)

                        swal("Erro ao transmitir", jsError.protNFe.infProt.xMotivo, "error")
                    }catch{
                        swal("Erro ao transmitir", e.responseJSON, "error");
                    }

                }
            }
        })
    })
</script>
@endsection