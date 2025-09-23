<?php

namespace App\Http\Controllers;

use App\Models\NuvemShopConfig;
use Illuminate\Http\Request;

class NuvemShopAuthController extends Controller
{
    // protected $empresa_id = null;
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

    private function getConfig()
    {
        $business_id = request()->session()->get('user.business_id');
        return NuvemShopConfig::where('business_id', $business_id)->first();
    }

    public function index(Request $request)
    {
        $config = $this->getConfig();
        if ($config != null) {

            $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
            $url = $auth->login_url_brazil();
            return redirect($url);
            if (!$url) {
                $output = [
                    'success' => 1,
                    'msg' => "Sucesso!!"
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => "Configurar credênciais"
                ];
            }
            return redirect()->route('nuvemshop.config')->with('status', $output);
        }
    }

    public function auth(Request $request)
    {
        $config = $this->getConfig();
        if ($config != null) {
            $code = $request->code;
            $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
            $store_info = $auth->request_access_token($code);
            $store_info['email'] = $config->email;

            session(['store_info' => $store_info]);
           
            $output = [
                'success' => 1,
                'msg' => ('Autenticação realizada')
            ];
            // return redirect('/nuvemshop/pedidos', $output);
            return redirect('/nuvemshop/pedidos')->with('status', $output);
        } else {
            $output = [
                'success' => 0,
                'msg' => ('Fazer a configuração')
            ];
        }
        return redirect('/nuvemshop/config')->with(['status' => $output]);
    }

    public function app()
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $response = $api->get("categories");
        print_r($response);
    }
}
