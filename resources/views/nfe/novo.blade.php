@extends('layouts.app')

@section('title', 'Emitir NFe')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Emitir NFe</h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @component('components.widget')
                    <input type="hidden" id="id" value="{{ $transaction->id }}" name="">
                    <div class="col-md-5">
                        <h4>Local: <strong>{{ $transaction->location->name }} -
                                {{ $transaction->location->location_id }}</strong></h4>
                        <h4>Estado: <strong>{{ $transaction->estado }}</strong></h4>
                        <h4>Ultimo numero NFe: <strong>{{ $transaction->lastNFe($transaction) }}</strong></h4>
                        <h4>Natureza de Operação: <strong>{{ $transaction->natureza->natureza }}</strong></h4>
                        <h4>Cliente: <strong>{{ $transaction->contact->name }}</strong></h4>
                        <h4>CNPJ: <strong>{{ $transaction->contact->cpf_cnpj }}</strong></h4>
                        <h4>Valor: <strong>{{ number_format($transaction->final_total, 2, ',', '') }}</strong></h4>
                        <h4>Forma de pagamento: <strong>{{ $payment_method }}</strong></h4>

                    </div>

                    <div class="clearfix"></div>


                    <div class="col-md-12">
                        <a class="btn btn-lg btn-primary" target="_blank"
                            href="{{ route('nfe.renderizar', [$transaction->id]) }}" id="submit_user_button">Visualizar</a>
                        <a class="btn btn-lg btn-danger" target="_blank" href="{{ route('nfe.gerarXml', [$transaction->id]) }}"
                            id="submit_user_button">Gerar XML</a>
                        <a class="btn btn-lg btn-success" id="send-sefaz">Transmitir para Sefaz</a>
                    </div>

                    <div class="col-md-3 m-1">
                        <a class="btn btn-lg btn-info" href="{{ route('nfe.alterarDataEmissao', [$transaction->id]) }}">Alterar
                            Data Emissão</a>
                    </div>
                @endcomponent
            </div>

        </div>

        <input type="hidden" id="token" value="{{ csrf_token() }}" name="">

        <br>
        <div class="row" id="action" style="display: none">
            <div class="col-md-12">
                @component('components.widget')
                    <div class="info-box-content">
                        <div class="col-md-4 col-md-offset-4">

                            <span class="info-box-number total_purchase">
                                <strong id="acao"></strong>
                                <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i>
                            </span>
                        </div>
                    </div>
                @endcomponent

            </div>
        </div>

    @stop



    @section('javascript')
        <script type="text/javascript">
            // swal("Good job!", "You clicked the button!", "success");
            var notClick = false;

            // $('#send-sefaz').click(() => {
            //     console.clear()
            //     if (!notClick) {
            //         notClick = true;
            //         $('#send-sefaz').attr('disabled', 'disabled')
            //         $('#action').css('display', 'block')
            //         let token = $('#token').val();
            //         let id = $('#id').val();

            //         setTimeout(() => {
            //             $('#acao').html('Gerando XML');
            //         }, 100);

            //         setTimeout(() => {
            //             $('#acao').html('Assinando o arquivo');
            //         }, 800);

            //         setTimeout(() => {
            //             $('#acao').html('Transmitindo para sefaz');
            //         }, 1500);

            //         $.ajax({
            //             type: 'POST',
            //             data: {
            //                 id: id,
            //                 _token: token
            //             },
            //             url: '{{ route('nfe.transmitir') }}',
            //             dataType: 'json',
            //             success: function(e) {
            //                 console.log(e)
            //                 $('#action').css('display', 'none')

            //                 if (e.success) {
            //                     swal("Sucesso", e.mensagem, "success").then(() => {
            //                         window.open('{{ route('nfe.imprimir', [$transaction->id]) }}')
            //                         location.reload()
            //                     });
            //                 } else {
            //                     swal("Erro", e.mensagem + (e.erro ? "\n\nDetalhe: " + e.erro : ""),
            //                         "error");
            //                 }
            //             },
            //             error: function(e) {
            //                 $('#send-sefaz').removeAttr('disabled')
            //                 notClick = false;
            //                 console.log(e)
            //                 try {
            //                     let jsError = JSON.parse(e.responseText);
            //                     msg = jsError.mensagem || msg;
            //                 } catch (err) {}

            //                 swal("Erro", msg, "error");
            //                 $('#action').hide();
            //             }
            //         })
            //     }
            // })


            $('#send-sefaz').click(() => {
                if (notClick) return;
                notClick = true;
                $('#send-sefaz').prop('disabled', true);
                $('#action').show();

                let token = $('#token').val();
                let id = $('#id').val();

                $.ajax({
                        type: 'POST',
                        url: '{{ route('nfe.transmitir') }}',
                        data: {
                            id,
                            _token: token
                        },
                        dataType: 'json'
                    })
                    .done((res) => {
                        $('#action').hide();
                        $('#send-sefaz').prop('disabled', false);
                        notClick = false;

                        if (res && res.success) {
                            swal("Sucesso", res.mensagem || "NF-e autorizada.", "success").then(() => {
                                window.open('{{ route('nfe.imprimir', [$transaction->id]) }}');
                                location.reload();
                            });
                        } else {
                            const msg = res?.mensagem || res?.error || res?.message || "Falha na transmissão.";
                            swal("Erro", msg, "error");
                        }
                    })
                    .fail((xhr) => {
                        $('#action').hide();
                        $('#send-sefaz').prop('disabled', false);
                        notClick = false;

                        const r = xhr.responseJSON;
                        const msg = (r && (r.mensagem || r.error || r.message || (typeof r === 'string' ? r : JSON
                                .stringify(r)))) ||
                            xhr.responseText ||
                            "Falha inesperada.";
                        swal("Erro", msg, "error");
                    });
            });
        </script>
    @endsection
