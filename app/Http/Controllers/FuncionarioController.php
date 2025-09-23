<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FuncionarioController extends Controller
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
            $funcionarios = Funcionario::where('business_id', $business_id)
                ->select(['id', 'nome', 'codigo', 'percentual_comissao']);

            return Datatables::of($funcionarios)

                ->editColumn('comissao', function ($row) {
                    return number_format($row->comissao, 2, ',', '');
                })

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<form id="funcionarios' . $row['id'] . '" method="POST" action="' . route('funcionarios.destroy', $row['id']) . '">';

                        $html .= '<a href="' . action('FuncionarioController@edit', [$row->id]) . '" class="btn btn-xs btn-primary edit_funcionarios"><i class="glyphicon glyphicon-edit"></i> Editar</a>
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

        return view('funcionarios.index');
    }

    public function create()
    {
        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
        // dd($request);
        $business_id = request()->session()->get('user.business_id');
        try {
            $data = [
                'nome' => $request->nome,
                'business_id' => $business_id,
                'percentual_comissao' => str_replace(",", ".", $request->comissao),
                'codigo' => $request->codigo,
                'cpf' => $request->cpf,
                'celular' => $request->celular,
                'status' => $request->status
            ];

            $result = Funcionario::create($data);

            $output = [
                'success' => 1,
                'msg' => "Funcinário Adicionado!"
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => false,
                'msg' => "Erro ao adicionar Funcionário"
            ];
        }
        return redirect('funcionarios')->with('status', $output);
    }

    public function edit($id)
    {
        $funcionario = Funcionario::find($id);
        return view('funcionarios.edit', compact('funcionario'));
    }

    public function update(Request $request, $id)
    {
        $funcionario = Funcionario::find($id);
        try {
            $funcionario->nome = $request->nome;
            $funcionario->percentual_comissao = str_replace(",", ".", $request->comissao);
            $funcionario->codigo = $request->codigo;
            $funcionario->cpf = $request->cpf;
            $funcionario->celular = $request->celular;
            $funcionario->status = $request->status;

            $funcionario->save();

            $output = [
                'success' => 1,
                'msg' => "Dados Atualizados!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Erro ao atualizar dados"
            ];
        }
        return redirect('funcionarios')->with('status', $output);
    }

    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            Funcionario::where('business_id', $business_id)
                ->where('id', $id)->delete();

            $output = [
                'success' => true,
                'msg' => 'Funcionário removido'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }

        return redirect('funcionarios')->with('status', $output);
    }
}
