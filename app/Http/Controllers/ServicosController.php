<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Product;
use App\Models\Servico;
use Illuminate\Http\Request;
use MercadoPago\Card;
use Yajra\DataTables\Facades\DataTables;


class ServicosController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $naturezas = Servico::where('business_id', $business_id)
                ->select(['id', 'nome', 'codigo', 'valor']);

            return Datatables::of($naturezas)

                ->editColumn('valor', function ($row) {
                    return number_format($row->valor, 2, ',', '');
                })

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<form id="servicos' . $row['id'] . '" method="POST" action="' . route('servicos.destroy', $row['id']) . '">';

                        $html .= '<a href="' . action('ServicosController@edit', [$row->id]) . '" class="btn btn-xs btn-primary edit_servicos"><i class="glyphicon glyphicon-edit"></i> Editar</a>
					&nbsp;';

                        $html .= '<button type="button" class="btn btn-xs btn-danger btn-delete"><i class="glyphicon glyphicon-trash"></i> Excluir</button>
					' . method_field('DELETE') . '
					' . csrf_field() . '
					</form>';
                        return $html;
                    }
                )

                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('servicos.index');
    }

    public function create()
    {
        return view('servicos.create');
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        try {
            $data = [
                'nome' => $request->nome,
                'valor' => str_replace(",", ".", $request->valor),
                'business_id' => $business_id,
                'codigo' => $request->codigo
            ];

            $result = Servico::create($data);

            $output = [
                'success' => 1,
                'msg' => "Serviço Salvo!"
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => false,
                'msg' => "Erro ao salvar Serviço"
            ];
        }
        return redirect('servicos')->with('status', $output);
    }

    public function edit($id)
    {
        $servico = Servico::find($id);
        return view('servicos.edit', compact('servico'));
    }

    public function update(Request $request, $id)
    {
        $servico = Servico::find($id);
        try {
            $servico->nome = $request->nome;
            $servico->valor = str_replace(",", ".", $request->valor);
            $servico->codigo = $request->codigo;

            $servico->save();

            $output = [
                'success' => 1,
                'msg' => "Serviço Atualizado!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Erro ao atualizar Serviço"
            ];
        }
        return redirect('servicos')->with('status', $output);
    }

    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            Servico::where('business_id', $business_id)
                ->where('id', $id)->delete();

            $output = [
                'success' => true,
                'msg' => 'Serviço removido'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }

        return redirect('servicos')->with('status', $output);
    }

    public function findId($id)
    {
        $item = Servico::where('id', $id)
            ->first();
        return response()->json($item, 200);
    }

    public function linhaServico(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $valor = $request->valor;
            $servico_id = $request->servico_id;
            $status = $request->status;
            $nome = $request->nome;

            $servico = OrdemServico::findOrFail($servico_id);
            return view('ordem_servico.partials.row_servico', compact('servico', 'qtd', 'valor', 'status', 'nome'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function findProduto($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $item = Product::where('business_id', $business_id)
            ->where('id', $id)
            ->with([
                'variations',
                'variations.product_variation',
                'unit',
                'product_locations'
            ])
            ->first();

        return response()->json($item, 200);
    }

    public function linhaProduto(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $valor = $request->valor;
            $produto_id = $request->produto_id;
            $produto = OrdemServico::findOrFail($produto_id);
            $variation_id = $request->variation_id;
            return view('ordem_servico.partials.row_produto', compact('produto', 'qtd', 'valor', 'variation_id'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }
}
