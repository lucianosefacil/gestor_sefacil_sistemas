@if (session('alert'))
    <input type="text" id="alertId">
    <script>
        window.addEventListener('load', function() {
            checkCertificado();
        });

        function passwordCertificate() {
            location.href = "/business/settings#senha_certificado";
        }

        function checkCertificado() {
            return axios.get('/check-certificado')
                .then((response) => {
                    if (response.data.resultado == "tem registro") {
                    } else {
                        swal({
                            title: "{{ session('alert')['message'] }}",
                            text: "",
                            icon: "{{ session('alert')['icon'] ?? '' }}",
                            buttons: {
                                cancel: "Fechar",
                                confirm: {
                                    text: "Vamos lá!",
                                    closeModal: false,
                                    type: 'confirm'
                                }
                            },
                            dangerMode: true,
                        }).then((button) => {
                            if (button) {
                                @if (session('alert')['module'] == 'certificado')
                                    setTimeout(viewRequestCertificate, 3000);
                                @elseif (session('alert')['module'] == 'password_certificate')
                                    passwordCertificate();
                                @endif
                            } else {
                                swal.stopLoading();
                                swal.close();
                            }

                        });
                    }
                    return response.data
                    swal.close();
                }).catch((error) => {
                    toastr.error('Houve um problema ao consultar no banco de dados');
                    console.log(error);
                });
        }

        function viewRequestCertificate() {
            return axios.get('/certificado')
                .then((response) => {
                    if (response.data.id != null || response.data.id != undefined || response.data.id != "" || response
                        .data.id != 0) {
                        axios.delete('/certificado/' + response.data.id).catch((error) => {
                            toastr.error('Houve um problema ao recriar credenciais');
                            console.log(error);
                        });
                    }
                    createRequestCertificate();
                    swal.stopLoading();
                    swal.close();
                }).catch((error) => {
                    toastr.error('Houve um problema ao consultar no banco de dados');
                    console.log(error);
                });
        }

        function storeRequestCertificate(response, payload) {
            let payloadAxios = {
                _token: "{!! @csrf_token() !!}",
                payload: JSON.stringify(payload),
                response: JSON.stringify(response.data),
                business_id: {!! request()->user()->business_id !!}
            };
            axiosCSRF.post('/certificado', payloadAxios).then(function(response) {
                if (!response.data.success) {
                    toastr.error('Houve um problema ao inserir no banco de dados');
                }
            });
        }

        function createRequestCertificate() {
            let url = "/request-certificate";
            let payload = {
                payload: {
                    sku: "21",
                    user: {
                        document: "{{ 57191570008 }}",
                        name: "{{ request()->user()->business->razao_social }}",
                        email: "{{ request()->user()->email }}",
                        cellphone: "{{ \App\Utils\Util::retirarMascaraTelefone(request()->user()->business->telefone) }}",
                        zipCode: "{{ \App\Utils\Util::retirarMascaraCEP(request()->user()->business->cep) }}",
                        address: "{{ request()->user()->business->rua ?? 'SEM RUA CADASTRADA' }}",
                        number: "{{ request()->user()->business->numero }}",
                        neighborhood: "{{ request()->user()->business->bairro ?? 'SEM BAIRRO CADASTRADO' }}",
                        addressDetail: "NÃO HÁ",
                        city: "{{ request()->user()->business->cidade->nome ?? '' }}",
                        state: "{{ request()->user()->business->cidade->uf ?? '' }}"
                    },
                    utm: {
                        "createId": true
                    },
                }
            };
            let header = {
                "content-type": "application/json"
            };
            Object.keys(payload).forEach(function(key) {
                console.log(key)
                if (payload[key] === null || payload[key] === "") {
                    toastr.error("O campo " + key + " está nulo.");
                }
            });
            axiosCSRF = axios;


            axios.post(url, payload, header)
                .then(function(response) {
                    console.log('linha 108', response);
                    if (response.data !== null || response.data !== '') {
                        if (response.data.status === 'success') {
                            storeRequestCertificate(response, payload);
                            // createIframe(response.data.redirectTo);
                            window.open(response.data.redirectTo, "_blank");
                        } else {
                            toastr.success('houve um problema na comunicação entre em contato com o suporte');
                            console.log(response);
                        }
                        swal.stopLoading();
                        swal.close();
                    }
                    setTimeout(location.reload(), 10000);

                })
                .catch(function(error) {
                    toastr.success('houve um problema na comunicação entre em contato com o suporte');
                    console.log(error);
                    swal.stopLoading();

                    swal.close();
                });

        }

        function createIframe(src) {
            // Abre uma nova aba
            var newTab = window.open();
        }

        // HTML para o conteúdo do iframe
        var iframeHTML = `
    <!DOCTYPE html>
    <html>
      <head>
        <title>Iframe</title>
      </head>
      <body>
        <button class="close-button" onclick="window.close()">x</button>
          <iframe src="{src}" style="width: 100%; height: 100%; border: none;"></iframe>
      </body>
<style>
body{
position: absolute;
height: 100%;
width: 100%;
overflow: hidden;
}
iframe{
overflow: hidden;
}
button{
float: right;
margin-right: 40px;
}

.close-button {
  width: 30px;
  height: 30px;
  border: none;
  background-color: transparent;
  color: #333;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  transition: color 0.3s;
}

.close-button:hover {
  color: #ff0000;
}

</style>
    </html>
  `;

        // Escreve o HTML no documento da nova aba
        //         if (newTab.document) {
        //     newTab.document.write(iframeHTML);
        //     newTab.document.close();
        // } else {
        //     console.error("Não foi possível acessar o document da nova aba.");
        // }
        // newTab.document.write(iframeHTML.replace(':src', src));
    </script>


    @php
        session()->forget('alert');
    @endphp
@endif
