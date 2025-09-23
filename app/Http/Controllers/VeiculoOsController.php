<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomerGroup;
use App\Models\Veiculo;
use App\Utils\ContactUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class VeiculoOsController extends Controller
{
    protected $contactUtil;

    public function __construct(
        ContactUtil $contactUtil,
    ) {
        $this->contactUtil = $contactUtil;
    }

    public function index()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $veiculos = Veiculo::where('business_id', $business_id)
                ->select(['id', 'placa', 'cor', 'modelo', 'cliente_id']);


            return Datatables::of($veiculos)

                ->addColumn('cliente', function ($row) {
                    $rem = Contact::find($row->cliente_id);
                    return $rem ? $rem->name : '--';
                })

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<form id="veiculos' . $row['id'] . '" method="POST" action="' . route('veiculo-os.destroy', $row['id']) . '">';
                        $html .= '<a href="' . action('VeiculoOsController@edit', [$row->id]) . '" class="btn btn-xs btn-primary edit_veiculos"><i class="glyphicon glyphicon-edit"></i> Editar</a>
					&nbsp;';

                        $html .= '<button type="button" class="btn btn-xs btn-danger btn-delete"><i class="glyphicon glyphicon-trash"></i> Excluir</button>
					' . method_field('DELETE') . '
					' . csrf_field() . '
					</form>';
                        return $html;
                    }
                )

                ->removeColumn('id')
                ->rawColumns(['action', 'cliente'])
                ->make(true);
        }
        return view('veiculo_os.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $ufs = Veiculo::cUF();
        $clientes = Contact::where('business_id', $business_id)->where('type', 'customer')->get();


        return view('veiculo_os.create', compact('ufs', 'clientes'));
    }

    public function store(Request $request)
    {
        // dd($request);
        try {
            $veiculo = $request->only(['tipo', 'placa', 'uf', 'cor', 'marca', 'modelo', 'cliente_id', 'observacao', 'ano_fabricacao', 'ano_modelo']);

            $business_id = $request->session()->get('user.business_id');
            $veiculo['business_id'] = $business_id;

            $nat = Veiculo::create($veiculo);

            $output = [
                'success' => 1,
                'msg' => 'Veiculo adicionado com sucesso!'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $ufs = Veiculo::cUF();
        $clientes = Contact::where('business_id', $business_id)->where('type', 'customer')->get();

        $veiculo = Veiculo::find($id);

        return view('veiculo_os.edit', compact('ufs', 'clientes', 'veiculo'));
    }

    public function update(Request $request, $id)
    {
        try {
			$veiculo = $request->only(['tipo', 'placa', 'uf', 'cor', 'marca', 'modelo', 'cliente_id', 'observacao', 'ano_fabricacao', 'ano_modelo']);

			$vec = Veiculo::findOrFail($id);
			
			$vec->update($veiculo);

			$output = [
				'success' => 1,
				'msg' => 'Editado com sucesso!'
			];
		} catch (\Exception $e) {
			\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

			$output = [
				'success' => 0,
				'msg' => __("messages.something_went_wrong")
			];
			print_r($output);
		}

		return redirect('veiculo-os')->with('status', $output);
    }

    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            Veiculo::where('business_id', $business_id)
                ->where('id', $id)->delete();

            $output = [
                'success' => true,
                'msg' => 'Veículo removido'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }

        return redirect('veiculo-os')->with('status', $output);
    }

}
