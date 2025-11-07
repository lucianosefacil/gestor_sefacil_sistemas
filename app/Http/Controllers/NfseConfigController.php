<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NfseConfig;
use App\Models\Cidade;
use App\Models\ConfigSystem;
use App\Models\ConfigNota;
use CloudDfe\SdkPHP\Softhouse;
use CloudDfe\SdkPHP\Emitente;
use Illuminate\Support\Str;
use CloudDfe\SdkPHP\Certificado;
use App\Models\Business;
use App\Models\City;

class NfseConfigController extends Controller
{

    protected $empresa_id = null;
    // public function __construct(){
    //     $this->middleware(function ($request, $next) {
    //         $this->empresa_id = $request->empresa_id;
    //         $value = session('user_logged');
    //         if(!$value){
    //             return redirect("/login");
    //         }
    //         return $next($request);
    //     });
    // }

    public function certificado(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $config = Business::find($business_id);

        if ($config == null) {
            $output = [
                'success' => 0,
                'msg' => 'Sem dados de configuração superadmin!'
            ];
            return redirect()->back()->with('status', $output);
        }

        $certificadoApi = $this->getCertificado();
        return view('nfse_config.certificado', compact('certificadoApi'));
    }

    private function getCertificado()
    {
        $business_id = request()->session()->get('user.business_id');
        $item = NfseConfig::where('empresa_id', $business_id)
            ->first();
        $empresa = Business::find($business_id);
        $params = [
            'token' => $item->token,
            'ambiente' => $empresa->ambiente,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => ''
            ]
        ];
        $certificado = new Certificado($params);
        $resp = $certificado->mostra();
        return $resp;
    }

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $config = Business::find($business_id);
        if ($config == null) {
            $output = [
                'success' => 0,
                'msg' => 'Sem dados de configuração superadmin!'
            ];
            return redirect()->back()->with('status', $output);
        }
        // if (env('token_integra_notas') == null) {
        //     session()->flash('mensagem_erro', 'Sem dados do token integra notas de configuração superadmin!');
        //     return redirect()->back();
        // }
        $item = NfseConfig::where('empresa_id', $business_id)
            ->first();
        $cidades = City::all();
        $configNota = Business::where('id', $business_id)
            ->first();
        if ($configNota == null) {
            $output = [
                'success' => 0,
                'msg' => 'Configure o emitente!'
            ];
            return redirect('/configNF')->with('status', $output);
        }
        $tokenNfse = $configNota->token_nfse;
        return view('nfse_config.index', compact('item', 'cidades', 'tokenNfse', 'business_id'));
    }

    public function store(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $item = NfseConfig::create($request->all());
            $resp = $this->storeSofthouse($request);
            if ($resp->codigo == 200) {
                $item->token = $resp->token;
                $item->save();
                $configNota = Business::where('id', $business_id)
                    ->first();
                $configNota->token_nfse = $resp->token;
                $configNota->integracao_nfse = 'integranotas';
                $configNota->save();
                $output = [
                    'success' => 1,
                    'msg' => 'Configurado com sucesso!'
                ];
                return redirect()->back()->with('status', $output);
            } else {
                $output = [
                    'success' => 0,
                    'msg' => $resp->mensagem
                ];
                return redirect()->back()->with('status', $output);
            }
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }

    private function storeSofthouse($request)
    {
        try {
            $config = Business::first();

            $params = [
                'token' => $config->token_integra_notas,
                'ambiente' => 2,
                'options' => [
                    'debug' => false,
                    'timeout' => 60,
                    'port' => 443,
                    'http_version' => CURL_HTTP_VERSION_NONE
                ]
            ];
            $softhouse = new Softhouse($params);
            $documento = preg_replace('/[^0-9]/', '', $request->documento);
            $telefone = preg_replace('/[^0-9]/', '', $request->telefone);
            $cep = preg_replace('/[^0-9]/', '', $request->cep);

            $cidade = City::findOrFail($request->cidade_id);

            $payload = [
                "nome" => $request->nome,
                "razao" => $request->razao_social,
                "cnae" => $request->cnae,
                "crt" => $request->regime == 'simples' ? 1 : 3,
                "ie" => $request->ie,
                "im" => $request->im,
                "login_prefeitura" => $request->login_prefeitura,
                "senha_prefeitura" => $request->senha_prefeitura,
                "telefone" => $telefone,
                "email" => $request->email,
                "rua" => $request->rua,
                "numero" => $request->numero,
                "complemento" => $request->complemento,
                "bairro" => $request->bairro,
                "municipio" => $cidade->nome,
                "cmun" => $cidade->codigo,
                "uf" => $cidade->uf,
                "cep" => $cep,
                "plano" => 'Emitente',
                "documentos" => [
                    "nfse" => true,
                ]
            ];

            if (strlen($documento) == 11) {
                $payload['cpf'] = $documento;
            } else {
                $payload['cnpj'] = $documento;
            }
            // dd($payload);
            $resp = $softhouse->criaEmitente($payload);

            return $resp;
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $item = NfseConfig::findOrFail($id);
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extensao = $file->getClientOriginalExtension();
                $file_name = Str::random(25) . "." . $extensao;
                $file->move(public_path('logos'), $file_name);
            } else {
                $file_name = $item->logo;
            }

            $request->merge([
                'logo' => $file_name
            ]);

            // dd($request->all());
            $item->fill($request->all())->save();

            $resp = $this->atualizaSofthouse($request, $item);

            if (is_object($resp) && property_exists($resp, 'codigo') && $resp->codigo == 200) {
                $output = [
                    'success' => 1,
                    'msg' => 'Atualizado com sucesso!'
                ];
                return redirect()->route('nfse-config.index')->with('status', $output);
            }

            if ($resp instanceof \Illuminate\Http\RedirectResponse) {
                return $resp;
            }

            $output = [
                'success' => 0,
                'msg' => $resp->mensagem ?? 'Falha ao atualizar emitente.'
            ];
            return redirect()->route('nfse-config.index')->with('status', $output);
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            return redirect()->route('nfse-config.index')->with('status', $output);
        }
    }

    private function atualizaSofthouse($request, $item)
    {
        try {
            $config = Business::find($item->empresa_id);
            $params = [
                'token' => $item->token,
                'ambiente' => $config->ambiente,
                'options' => [
                    'debug' => false,
                    'timeout' => 60,
                    'port' => 443,
                    'http_version' => CURL_HTTP_VERSION_NONE
                ]
            ];
            $softhouse = new Emitente($params);
            $documento = preg_replace('/[^0-9]/', '', $request->documento);
            $telefone = preg_replace('/[^0-9]/', '', $request->telefone);
            $cep = preg_replace('/[^0-9]/', '', $request->cep);

            $cidade = City::findOrFail($request->cidade_id);

            $payload = [
                "nome" => $request->nome,
                "razao" => $request->razao_social,
                "cnae" => $request->cnae,
                "crt" => $request->regime == 'simples' ? 1 : 3,
                "ie" => $request->ie,
                "im" => $request->im,
                "login_prefeitura" => $request->login_prefeitura,
                "senha_prefeitura" => $request->senha_prefeitura,
                "telefone" => $telefone,
                "email" => $request->email,
                "rua" => $request->rua,
                "numero" => $request->numero,
                "complemento" => $request->complemento,
                "bairro" => $request->bairro,
                "municipio" => $cidade->nome,
                "cmun" => $cidade->codigo,
                "uf" => $cidade->uf,
                "cep" => $cep,
                "plano" => 'Emitente',
                "documentos" => [
                    "nfse" => true,
                ]
            ];

            if (strlen($documento) == 11) {
                $payload['cpf'] = $documento;
            } else {
                $payload['cnpj'] = $documento;
            }

            if ($item->logo != null) {
                if (file_exists(public_path('logos/') . $item->logo)) {
                    $file = file_get_contents(public_path('logos/') . $item->logo);
                    $payload['logo'] = base64_encode($file);
                }
            }
            // dd($payload);
            $resp = $softhouse->atualiza($payload);

            return $resp;
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }

    public function removeLogo()
    {
        $item = NfseConfig::where('empresa_id', $this->empresa_id)
            ->first();
        if ($item->logo != null && file_exists(public_path('logos/') . $item->logo)) {
            unlink(public_path('logos/') . $item->logo);
        }

        $item->logo = null;
        $item->save();

        $params = [
            'token' => $item->token,
            'ambiente' => 2,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => CURL_HTTP_VERSION_NONE
            ]
        ];
        $softhouse = new Emitente($params);

        $payload['logo'] = null;
        $resp = $softhouse->atualiza($payload);
        // dd($resp);

        $output = [
            'success' => 1,
            'msg' => 'Logo removida com sucesso!'
        ];
        return redirect()->back()->with('status', $output);
    }

    public function uploadCertificado(Request $request)
    {
        // if(!is_dir(public_path('certificado_temp'))){
        //     mkdir(public_path('certificado_temp'), 0777, true);
        // }

        if (!$request->hasFile('file')) {
            $output = [
                'success' => 0,
                'msg' => 'Selecione o Certificado!'
            ];
            return redirect()->back()->with('status', $output);
        }

        $file = base64_encode(file_get_contents($request->file('file')->path()));
        // dd($file);
        $senha = $request->senha;
        try {
            $config = Business::first();
            $item = NfseConfig::where('empresa_id', $this->empresa_id)
                ->first();
            $params = [
                'token' => $item->token,
                'ambiente' => 2,
                'options' => [
                    'debug' => false,
                    'timeout' => 60,
                    'port' => 443,
                    'http_version' => CURL_HTTP_VERSION_NONE
                ]
            ];
            $certificado = new Certificado($params);

            $payload = [
                'certificado' => $file,
                'senha' => $senha
            ];

            $resp = $certificado->atualiza($payload);
            if ($resp->codigo == 200) {

                $output = [
                    'success' => 1,
                    'msg' => 'Upload realizado com sucesso!'
                ];
                return redirect()->back()->with('status', $output);
            } else {
                $output = [
                    'success' => 0,
                    'msg' => $resp->mensagem
                ];
                return redirect()->back()->with('status', $output);
            }
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }

    public function newToken()
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            // $config = ConfigSystem::first();
            $item = NfseConfig::where('empresa_id', $business_id)
                ->first();
            $empresa = Business::find($business_id);
            $params = [
                'token' => $item->token,
                'ambiente' => $empresa->ambiente,
                'options' => [
                    'debug' => false,
                    'timeout' => 60,
                    'port' => 443,
                    'http_version' => CURL_HTTP_VERSION_NONE
                ]
            ];
            $emitente = new Emitente($params);

            $resp = $emitente->token();
            if ($resp->codigo == 200) {
                $output = [
                    'success' => 1,
                    'msg' => 'Token gerado com sucesso!'
                ];
                return redirect()->back()->with('status', $output);
            } else {
                $output = [
                    'success' => 0,
                    'msg' => $resp->mensagem
                ];
                return redirect()->back()->with('status', $output);
            }
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => 'Algo deu errado: ' . $e->getMessage()
            ];
            return redirect()->back()->with('status', $output);
        }
    }
}
