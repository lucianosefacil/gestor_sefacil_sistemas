<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    {{-- <link rel="stylesheet" href="/css/style_pdf.css"> --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css?family=Pinyon+Script" rel="stylesheet">

    <style>
        table,
        th,
        td {
            border-collapse: collapse;
            padding: 2px 3px;
        }

        @media print {
            body {
                visibility: hidden;
            }

            #via_profissional {
                visibility: visible;
            }
        }

    </style>
</head>
<body onload="gerarArquivo()">
    <div class="row" id="pdf">
        <div class="" style="margin-top:">
            <button class="btn btn-info" id="btn_via_profissional">IMPRIMIR VIA PROFISSIONAL</button>
            <div class="row" id="via_profissional">
                <h5>Via Profissional</h5>
                <div class="row identificacao-paciente">
                    <div class="col s12">
                        <label style="color: black">Data de criação: <strong id="data-exame">{{\Carbon\Carbon::parse($ordem->created_at)->format('d/m/Y H:m:s ')}}</strong>
                            ORDEM DE SERVIÇO Nº: {{$ordem->id}}</label><br>
                        <label style="color: black">Cliente: <strong>{{$ordem->cliente->name}}</strong> -- {{$ordem->cliente->mobile}}</label>
                    </div>
                </div>
                <div style="margin-left: -20px; margin-top:0px">
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
                <div class="col s11" style="margin-top:-5px">
                    <label style="color: black"><strong>OBSERVAÇÕES:</strong></label>
                    <label style="color: black">{{$ordem->observacao}}</label>
                </div>
                <div style="margin-left: -20px">
                    ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                </div>
                <div class="col s12" style="margin-top:0px">
                    <table class="" style="margin-top:0px">
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
                                <th style="width: 95px;">
                                    <label style="color: black" for="">{{$i->produto->id}} </label>
                                </th>
                                <th style="width: 465px;">
                                    <label style="color: black" for="">{{$i->produto->name}}</label>
                                </th>
                                <th style="width: 80px;">
                                    <label style="color: black" for="">{{number_format($i->quantidade, 2, ',', '.')}}</label>
                                </th>
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
                                <th style="width: 95px;">
                                    <label style="color: black" for="">{{$s->servico_id }}</label>
                                </th>
                                <th style="width: 465px;">
                                    <label style="color: black" for="">{{$s->servico->nome}}</label>
                                </th>
                                <th style="width: 80px;">
                                    <label style="color: black" for="">{{number_format($s->quantidade, 2, ',', '.')}}</label>
                                </th>
                            </tr>
                            @php
                            $somaServicos += $s->sub_total;
                            @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
                <div style="margin-top: 20px" class="col s6 center-align">
                    <span><strong>_________________________________________________________</strong></span><br>
                    <span><strong>Assinatura</strong></span>
                    <br>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/js/html2canvas.min.js"></script>
    <script type="text/javascript" src="/js/jspdf.min.js"></script>
    {{-- <script type="text/javascript" src="/js/gerarPdf.js"></script> --}}

    <script>
        var cache_width = $('#pdf').width();
        // var teste = $('#via_cliente').width();

        var a4 = [595.28, 841.89];

        function gerarArquivo() {
            // Setar o width da div no formato a4
            var teste = $("#pdf").width((a4[0] * 1.33333) - 80).css('max-width', 'none');
            // $("body").css("display", "none");
            var nomeExame = $('#nome-exame').html();
            var dataExame = $('#data-exame').html();

            dataExame.replace("/", "_");
            dataExame.replace("/", "_");
            dataExame.replace("/", "_");

            // Aqui ele cria a imagem e cria o pdf
            html2canvas($('#pdf'), {
                onrendered: function(canvas) {
                    var img = canvas.toDataURL("image/png", 1.0);
                    var doc = new jsPDF({
                        unit: 'px'
                        , format: 'a4'
                    });
                    doc.addImage(img, 'JPEG', 20, 20);
                    // doc.save(nomeExame + "_" + dataExame + '.pdf');
                    //Retorna ao CSS normal
                    $('#renderPDF').width(cache_width);

                    var url = window.location.href;
                    var redir = url.split("/");
                    // location.href = "/agendamento/exams/" + redir[6];
                }
            });
        }

        $('#btn_via_profissional').click(() => {
            ViaCliente()
        })

        function ViaCliente() {
            window.print();
        }

    </script>
</body>
</html>
