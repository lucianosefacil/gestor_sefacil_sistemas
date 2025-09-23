<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\RequestCertificate;
use App\Utils\ResponseErrorUtil;
use App\Utils\ResponseSuccessUtil;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificadoController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function certificateRequest()
    {
        try {
            $url = getenv('URL_PAGE_CERTIFICATE') . '/request-certificate';
            $payload = request()->payload;
            $request = Http::accept('application/json')->contentType('application/json')->post($url,$payload );
            $ok = $request->ok();
            if($ok){
                return response()->json($request->json());
            }
            return  response()->json(['message'=>"Houve um problema com o servidor"],500);
        }catch (\Exception $e){
           return response()->json(ResponseErrorUtil::response($e),500);

        }
    }


    /**
     * @return JsonResponse
     */
        public function index(): \Illuminate\Http\JsonResponse
        {
            try {
                $data = RequestCertificate::where('business_id', request()->user()->business_id)->where('status','<>','FINALIZADO')->orderBy('created_at', 'desc')->first();
                return response()->json($data);
            } catch (\Exception $exception) {
                return response()->json(ResponseErrorUtil::response($exception));
            }

        }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->except(['status', 'username', 'password', 'anexo', '_token']);
            RequestCertificate::create($data);
            return response()->json(ResponseSuccessUtil::response());
        } catch (\Exception $exception) {
            return response()->json(ResponseErrorUtil::response($exception));
        }

    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->updateUtil($id, $request->except('_token'));
    }

    public function show($id = null)
    {
        try {
            $resultadoPagamento = $this->verificarPagamento();
            $this->verificarAprovacao();
            Log::debug('Verificação Feita');
            if(is_bool($resultadoPagamento)){
                return response()->json(["resultado"=>"tem registro"]);
            }
            return response()->json(["resultado"=>"nao tem registro"]);
        } catch (\Exception $exception) {
            Log::error(response()->json(ResponseErrorUtil::response($exception)));
            return response()->json(ResponseErrorUtil::response($exception),500);
        }
    }

    /**
     * @param $id
     * @param $data
     * @return JsonResponse
     */
    public function updateUtil($id, $data)
    {
        try {
            $requestCertificate = RequestCertificate::find($id);
            $requestCertificate->update($data);
            return response()->json(ResponseSuccessUtil::response());
        } catch (\Exception $exception) {
            return response()->json(ResponseErrorUtil::response($exception));
        }
    }

    /**
     * @return false|void
     */
    private function verificarPagamento()
    {
        Log::debug('Iniciando verificação...');
        $row = RequestCertificate::whereIn('status', ['AGUARDANDO','PAGO'])->where('business_id', request()->user()->business->id)->first();
        if (empty($row)) {
            return 'nao_tem_registro';
        }

        Log::debug('Verifica  se há algum pedido...');

        $url = getenv('URL_PAGE_CERTIFICATE') . '/check-request-certificate';
        $response = json_decode($row->response);

        Log::debug('Pesquisando ID para verificar status do pagamento...');

        $request = Http::get($url, ["utmId" => $response->utmId, 'target' => 'check-avaiable']);

        Log::error($request->body());
        $bodyRequest = $request->object();
        $ok = $request->ok();
        if ($ok && empty($bodyRequest->order->statusPayment)) {
            Log::debug('Erro ao consultar os aguardes');
            return false;
        }

        $countProtocols = count($bodyRequest->protocols);
        if($countProtocols>0){
            if ($ok && $bodyRequest->order->statusPayment == 'CONCLUIDO') {
                $this->updateUtil($row->id, ['status' => 'PAGO', 'username' => $bodyRequest->protocols[($countProtocols-1)]->username,'password'=>base64_decode(request()->user()->business->senha_certificado)]);
                Log::debug('Pagamento Concluído');
            return true;
            }
        }
        return false;
    }


    /**
     * @return false|true
     */
    private function verificarAprovacao(): bool
    {

        // Busca a primeira linha na tabela RequestCertificate que tenha status "AGUARDANDO" e business_id correspondente ao usuário atual
        $row = RequestCertificate::where('status', 'PAGO')->where('business_id', request()->user()->business->id)->first();

        // Verifica se não há linhas encontradas
        if (empty($row)) {
            Log::debug('Não há nenhuma linha aguardando');
            return false;
        }

        // Constrói a URL para a página de emissão do certificado com o returnType definido como "base64"
        $url = getenv('URL_PAGE_CERTIFICATE') . '/issue-certificate?returnType=base64';

        // Obtém o username da linha encontrada anteriormente
        $username = $row->username;

            // Faz uma requisição POST para a URL especificada, passando o username e a senha do certificado do usuário atual
            $request = Http::post($url, ["username" => $username, 'password' => $row->password]);


        // Verifica se a requisição foi bem-sucedida (status 2xx)
        $ok = $request->ok();

        // Obtém o corpo da resposta da requisição
        $bodyRequest = $request->object();
        Log::info("Business_id:".request()->user()->business->id. " Data: ".$request->body() );

        // Verifica se a requisição foi bem-sucedida e não possui um código de erro no corpo da resposta
        if ($ok && empty($bodyRequest->code)) {
            Log::debug('Ainda não concluído entrevista');
            return false;
        }

        // Verifica se a requisição foi bem-sucedida e possui um código de erro igual a 30 no corpo da resposta
        if ($ok && $bodyRequest->code == 30) {
            // Verifica se o campo "data" no corpo da resposta não está vazio
            if (!empty($bodyRequest->data)) {
                $dirName = 'uploads/business_certificados/';
                $filename = uniqid() . '_' . (request()->user()->business->name ?? '');

                // Chama a função base64ToPfx para converter o texto base64 em um arquivo PFX e salvá-lo no diretório especificado
                $this->base64ToPfx($bodyRequest->data, $dirName . $filename);

                // Atualiza o campo "certificado" do registro de Business do usuário atual com o conteúdo decodificado do texto base64
                $business = Business::find(request()->user()->business_id);
                $business->update(['certificado' => base64_decode($bodyRequest->data),'senha_certificado'=>base64_encode($row->password), 'certificado_urn' => $filename . '.pfx']);
                $row->update(['anexo'=>$dirName . $filename,'status'=>'FINALIZADO']);
            }
        }

        if ($request->status() == 403) {
            $request->session()->put('alert', ['message' => 'Senha de certificado incorreta', 'icon' => 'error', 'module' => 'password_certificate']);
        }
        if ($request->status() > 200) {
            return false;
        }
        return true;
    }

    /**
     * converte base 64 em pfx
     * @param $base64Text
     * @param $fileName
     * @return string
     */
    private function base64ToPfx($base64Text, $fileName): string
    {
        // Decodifica o texto base64
        $decodedText = base64_decode($base64Text);

        // Define o caminho para armazenar o arquivo PFX
        $filePath = public_path('/' . $fileName . '.pfx');

        // Salva o conteúdo decodificado em um arquivo PFX
        Storage::put($filePath, $decodedText);

        return $filePath;
    }

    public function destroy($id)
    {
        try {
            $certificate = RequestCertificate::where('id',$id)->where('status','AGUARDANDO')->first();
            if(!empty($certificate)){
                $certificate->delete();
            }

            return response()->json(ResponseSuccessUtil::response());
        } catch (\Exception $exception) {
            return response()->json(ResponseErrorUtil::response($exception));
        }

    }
}
