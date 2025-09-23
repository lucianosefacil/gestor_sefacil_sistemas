<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NuvemShopConfig;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;

class NuvemShopController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
    }

    public function config()
    {
        if (!auth()->user()->can('nuvemshop.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $config = NuvemShopConfig::where('business_id', $business_id)->first();
        return view('nuvemshop.config', compact('config'));
    }

    public function save(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $result = false;
        if ($request->id == 0) {
            $result = NuvemShopConfig::create([
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'email' => $request->email,
                'business_id' => $business_id
            ]);
        } else {
            $config = NuvemShopConfig::where('business_id', $business_id)->first();
            $config->client_id = $request->client_id;
            $config->client_secret = $request->client_secret;
            $config->email = $request->email;
            $result = $config->save();
        }

        if ($result) {
            $output = [
                'success' => 1,
                'msg' => "Sucesso!!"
            ];
        } else {
            $output = [
                'success' => false,
                'msg' => "Erro ao Salvar"
            ];
        }
        return redirect()->route('nuvemshop.config')->with('status', $output);
    }

    public function categorias()
    {
        $store_info = session('store_info');

        if (!$store_info) {
            return redirect('/nuvemshop');
        }
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        try {
            $categorias = (array)$api->get("categories");
            $categorias = $categorias['body'];

            $this->validaCategorias($categorias);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }
        // echo "<pre>";
        // print_r($categorias);
        // echo "</pre>";
        // die;
        return view('nuvemshop/categorias')
            ->with('categorias', $categorias)
            ->with('title', 'Categorias');
    }

    private function validaCategorias($categorias)
    {
        $business_id = request()->session()->get('user.business_id');

        foreach ($categorias as $cat) {
            $result = Category::where('business_id', $business_id)
                ->where('nuvemshop_id', $cat->id)->first();

            if ($result == null) {
                $this->cadastrarCategoria($cat);
            }
        }
    }

    public function categoria_new()
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];

        return view('nuvemshop/categorias_form')
            ->with('categorias', $categorias)
            ->with('title', 'Nova Categoria');
    }

    public function categoria_edit($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        $categoria = (array)$api->get("categories/" . $id);
        $categoria = $categoria['body'];

        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];

        return view('nuvemshop/categorias_form')
            ->with('categoria', $categoria)
            ->with('categorias', $categorias)
            ->with('title', 'Editar Categoria');
    }

    public function categoria_delete($id)
    {
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');
        try {
            $response = $api->delete("categories/$id");

            $output = [
                'success' => 1,
                'msg' => ('Categoria removida!')
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => ('Erro inesperado')
            ];
        }
        return redirect('/nuvemshop/categorias')->with(['status' => $output]);
    }

    public function saveCategoria(Request $request)
    {
        $nome = $request->nome;
        $descricao = $request->descricao;
        $id = $request->id;
        $categoria_id = $request->categoria_id;
        try {
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App (' . $store_info['email'] . ')');

            if ($id > 0) {
                if ($categoria_id == 0) {
                    $response = $api->put("categories/$id", [
                        'name' => $nome,
                        'description' => $descricao
                    ]);
                } else {
                    $response = $api->put("categories/$id", [
                        'name' => $nome,
                        'parent' => $categoria_id,
                        'description' => $descricao
                    ]);
                }

                $cat = Category::where('nuvemshop_id', $id)->first();

                if($cat == null){

                }else{
                    $this->atualizarCategoria($request, $request->id);
                }

                if ($response) {
                    $output = [
                        'success' => 1,
                        'msg' => ('Categoria atualizada!')
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => ('Erro inesperado')
                    ];
                }
            } else {
                $response = $api->post("categories", [
                    'name' => $nome,
                    'parent' => $categoria_id,
                    'description' => $descricao
                ]);

                if ($response) {
                    $output = [
                        'success' => 1,
                        'msg' => ('Categoria criada!')
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => ('Erro inesperado')
                    ];
                }
            }
        } catch (\Exception $e) {
            $output = [
                'success' => 0,
                'msg' => ('Erro inesperado')
            ];
        }

        return redirect('/nuvemshop/categorias')->with(['status' => $output]);
    }

    private function cadastrarCategoria($request)
    {
        $business_id = request()->session()->get('user.business_id');
        $created_by = request()->session()->get('user.id');
        try {
            $data = [
                'name' => $request->name->pt,
                'short_code' => $request->name->pt,
                'category_type' => 'product',
                'description' => $request->description->pt,
                'destaque' => 0,
                'ecommerce' => 0,
                'nuvemshop_id' => $request->id,
                'business_id' => $business_id,
                'created_by' => $created_by
            ];
            $category = Category::create($data);
            $output = [
                'success' => true,
                'data' => $category,
                'msg' => __("category.added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function atualizarCategoria($categoria , $id)
    {
        $category = Category::where('nuvemshop_id', $id)->first();

        try {
            $category->name = $categoria->nome;
            $category->description =$categoria->descricao;
            $category->destaque = 0;
            $category->ecommerce = 0;

            $category->save();

            $output = [
                'success' => true,
                'msg' => __("category.updated_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect()->back()->with('status', $output);
    }
}
