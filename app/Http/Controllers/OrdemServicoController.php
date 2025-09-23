<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Contact;
use App\Models\CustomerGroup;
use App\Models\Funcionario;
use App\Models\Servico;
use App\Models\User;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use Modules\Repair\Entities\DeviceModel;
use Modules\Repair\Entities\RepairStatus;
use Modules\Repair\Utils\RepairUtil;
use App\Utils\ContactUtil;
use App\Models\BusinessLocation;
use App\Models\City;
use App\Models\OrdemServico;
use App\Models\Pais;
use App\Models\Product;
use App\Models\ProdutoOs;
use App\Models\ServicoOs;
use App\Models\Veiculo;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Utils\ModuleUtil;
use App\Models\InvoiceScheme;
use App\Models\NaturezaOperacao;
use App\Models\TaxRate;
use App\Models\Transportadora;
use App\Utils\TransactionUtil;
use App\Utils\CashRegisterUtil;

class OrdemServicoController extends Controller
{
    protected $businessUtil;
    protected $repairUtil;
    protected $contactUtil;
    protected $moduleUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;


    public function __construct(
        RepairUtil $repairUtil,
        ContactUtil $contactUtil,
        BusinessUtil $businessUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,

    ) {
        $this->businessUtil = $businessUtil;
        $this->repairUtil = $repairUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;

        $this->dummyPaymentLine = [
            'method' => '',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
            'data_base' => date('d/m/Y'),
            'intervalo' => '',
            'vencimento' => date('d/m/Y'),
            'qtd_parcelas' => 1
        ];
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);

        if (request()->ajax()) {
            $query = OrdemServico::leftJoin('transactions', 'ordem_servicos.venda_id', '=', 'transactions.id')
                ->where('ordem_servicos.business_id', $business_id)
                ->orderBy('ordem_servicos.id', 'desc');
            $os = $query->select(
                'ordem_servicos.id',
                'ordem_servicos.data_entrega',
                'ordem_servicos.nfe_id',
                'ordem_servicos.venda_id',
                'ordem_servicos.status_id',
                'ordem_servicos.cliente_id',
                'ordem_servicos.location_id',
                'ordem_servicos.valor',
                'ordem_servicos.created_at',
                'transactions.invoice_no'
            )->groupBy('ordem_servicos.id');

            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            if (!empty($location_id) && $location_id != 'none') {
                if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
                    $query->whereHas('product_locations', function ($query) use ($location_id) {
                        $query->where('product_locations.location_id', '=', $location_id);
                    });
                }
            } elseif ($location_id == 'none') {
                $query->doesntHave('product_locations');
            } else {
                if ($permitted_locations != 'all') {
                    $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                        $query->whereIn('product_locations.location_id', $permitted_locations);
                    });
                } else {
                    $query->with('product_locations');
                }
            }

            if (!empty(request()->cliente_id)) {
                $cliente_id = request()->cliente_id;
                $os->where('ordem_servicos.cliente_id', $cliente_id);
            }

            if (!empty(request()->status_id)) {
                $status_id = request()->status_id;
                $os->where('ordem_servicos.status_id', $status_id);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $os->whereDate('ordem_servicos.created_at', '>=', $start)
                    ->whereDate('ordem_servicos.created_at', '<=', $end);
            }

            if (!empty(request()->funcionario_id)) {
                $funcionario_id = request()->funcionario_id;
                $os->where('ordem_servicos.funcionario_id', $funcionario_id);
            }

            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end =  request()->end_date;
            //     $os->whereDate('transactions.transaction_date', '>=', $start)
            //         ->whereDate('transactions.transaction_date', '<=', $end);
            // }
            // $os = OrdemServico::where('business_id', $business_id)
            //     ->select(['id', 'data_entrega', 'nfe_id', 'status_id', 'cliente_id', 'location_id', 'valor', 'created_at']);
            $os->groupBy('ordem_servicos.id');

            // if ($this->businessUtil->isModuleEnabled('subscription')) {
            //     $os->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            // }

            return Datatables::of($os)
                ->addColumn('action', function ($row) {
                    $html =
                        '<div class="btn-group"><button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" 
                    aria-expanded="false">' . __("messages.actions") . '<span class="caret"></span><span class="sr-only">
                    Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .=
                        '<li><a href="' . action('OrdemServicoController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';

                    $html .=
                        '<li><a href="' . action('OrdemServicoController@addParts', [$row->id]) . '"><i class="fa fa-eye"></i> ' . __("Atribuir Produto/Serviço") . '</a></li>';

                    $html .=
                        '<li><a target="_blank" href="' . action('OrdemServicoController@imprimirViaCliente', [$row->id]) . '"><i class="fa fa-print"></i> ' . __("Imprimir Via Cliente") . '</a></li>';

                    $html .=
                        '<li><a target="_blank" href="' . action('OrdemServicoController@imprimirViaFuncionario', [$row->id]) . '"><i class="fa fa-print"></i> ' . __("Imprimir Via Profissional") . '</a></li>';

                    $html .=
                        '<li><a href="' . action('OrdemServicoController@destroy', [$row->id]) . '" class="delete-os"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';

                    $html .=
                        '<li><a href="' . action('OrdemServicoController@gerarNfe', [$row->id]) . '" class=""><i class="fa fa-th-large"></i> ' . __("Finalizar") . '</a></li>';
                    return $html;
                })

                ->editColumn('data_entrega', function ($row) {
                    return \Carbon\Carbon::parse($row->data_entrega)->format('d/m/Y H:i:s');
                })

                ->editColumn('venda_id', function ($row) {
                    return $row ? $row->invoice_no : '--';
                })

                ->editColumn('nfe_id', function ($row) {
                    return $row ? $row->nfe_id : '--';
                })

                ->editColumn('status_id', function ($row) {
                    $sta = RepairStatus::find($row->status_id);
                    return $sta ? $sta->name : '--';
                })

                ->editColumn('cliente_id', function ($row) {
                    $rem = Contact::find($row->cliente_id);
                    return $rem ? $rem->name : '--';
                })

                ->editColumn('location_id', function ($row) {
                    $loc = BusinessLocation::find($row->location_id);
                    return $loc ? $loc->name : '--';
                })

                ->addColumn('valor', function ($row) {
                    return $row ? $row->valor : '--';
                })

                ->addColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('d/m/Y');
                })

                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $clientes = Contact::customersDropdown($business_id, false);
        $status = RepairStatus::forDropdown($business_id);
        $funcionario = Funcionario::forDropdown($business_id);
        // $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        return view('ordem_servico.index')
            ->with(compact('business_locations', 'clientes', 'status', 'funcionario'));
    }

    public function create()
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $repair_statuses = RepairStatus::getRepairSatuses($business_id);
        $device_models = DeviceModel::forDropdown($business_id);
        $repair_settings = $this->repairUtil->getRepairSettings($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $default_status = '';
        if (!empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        $tipo = 'customer';

        $usuario = User::allUsersDropdown($business_id, false);

        $funcionario = Funcionario::where('business_id', $business_id)->where('status', 'ativo')->get();

        $servicos = Servico::where('business_id', $business_id)->get();

        $veiculos = $this->prepareVeiculos();

        $ufs = Veiculo::cUF();

        $clientes = Contact::where('business_id', $business_id)->where('type', 'customer')->get();

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
            }
        }

        // $bl_attributes = $business_locations['attributes'];

        return view('ordem_servico.create')
            ->with(compact(
                'repair_statuses',
                'device_models',
                'default_status',
                'business_locations',
                'types',
                'customer_groups',
                'walk_in_customer',
                'repair_settings',
                'tipo',
                'usuario',
                'funcionario',
                'servicos',
                'veiculos',
                'ufs',
                'clientes',
                'default_location',
            ))
            ->with('cities', $this->prepareCities())
            ->with('estados', $this->prepareEstados())
            ->with('paises', $this->preparePaises());
    }

    private function prepareVeiculos()
    {
        $business_id = request()->session()->get('user.business_id');

        $veiculos = Veiculo::where('business_id', $business_id)
            ->get();
        $temp = [];
        foreach ($veiculos as $v) {
            $temp[$v->id] = "$v->placa - $v->modelo";
        }
        return $temp;
    }

    private function prepareCities()
    {
        $cities = City::all();
        $temp = [];
        foreach ($cities as $c) {
            // array_push($temp, $c->id => $c->nome);
            $temp[$c->id] = $c->nome . " ($c->uf)";
        }
        return $temp;
    }

    private function preparePaises()
    {
        $paises = Pais::all();
        $temp = [];
        foreach ($paises as $p) {
            // array_push($temp, $c->id => $c->nome);
            $temp[$p->codigo] = "$p->codigo - $p->nome";
        }
        return $temp;
    }

    private function prepareEstados()
    {
        return [
            'AC' => 'AC',
            'AL' => 'AL',
            'AM' => 'AM',
            'AP' => 'AP',
            'BA' => 'BA',
            'CE' => 'CE',
            'DF' => 'DF',
            'ES' => 'ES',
            'GO' => 'GO',
            'MA' => 'MA',
            'MG' => 'MG',
            'MS' => 'MS',
            'MT' => 'MT',
            'PA' => 'PA',
            'PB' => 'PB',
            'PE' => 'PE',
            'PI' => 'PI',
            'PR' => 'PR',
            'RJ' => 'RJ',
            'RN' => 'RN',
            'RO' => 'RO',
            'RR' => 'RR',
            'RS' => 'RS',
            'SC' => 'SC',
            'SP' => 'SP',
            'SE' => 'SE',
            'TO' => 'TO'
        ];
    }

    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $data = [
                'business_id' => $business_id,
                'location_id' => $request->location_id,
                'cliente_id' => $request->contact_id,
                'service_type' => $request->service_type,
                'pick_up_on_site_addr' => $request->pick_up_on_site_addr,
                'descricao' => $request->comment_by_ss,
                'valor' => 0,
                'status_id' => $request->status_id,
                'data_entrega' => $this->parseDate($request->delivery_date),
                'nfe_id' => 0,
                'observacao' => $request->observacao,
                'funcionario_id' => $request->funcionario_id,
                'veiculo_id' => $request->veiculo_id
            ];

            $ordem = OrdemServico::create($data);

            if (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action('OrdemServicoController@addParts', [$ordem->id])
                    ->with('status', [
                        'success' => true,
                        'msg' => __("lang_v1.success")
                    ]);
            }
            $output = [
                'success' => 1,
                'msg' => "OS salva com sucesso!"
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->route('ordem-servico.index')->with('status', $output);
    }

    private function parseDate($date, $plusDay = false)
    {
        if ($plusDay == false)
            return date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d H:i:s', strtotime("+1 day", strtotime(str_replace("/", "-", $date))));
    }

    public function edit($id)
    {
        $ordem = OrdemServico::find($id);

        $business_id = request()->session()->get('user.business_id');

        $repair_statuses = RepairStatus::getRepairSatuses($business_id);
        $device_models = DeviceModel::forDropdown($business_id);
        $repair_settings = $this->repairUtil->getRepairSettings($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $types = Contact::getContactTypes();
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $default_status = '';
        if (!empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        $tipo = 'customer';

        $usuario = User::allUsersDropdown($business_id, false);

        $funcionario = Funcionario::where('business_id', $business_id)->get();

        $clientes =  Contact::where('business_id', $business_id)->get();

        $veiculos = Veiculo::where('business_id', $business_id)->get();

        return view('ordem_servico.edit')
            ->with(compact(
                'ordem',
                'clientes',
                'repair_statuses',
                'device_models',
                'default_status',
                'business_locations',
                'types',
                'walk_in_customer',
                'repair_settings',
                'tipo',
                'usuario',
                'funcionario',
                'veiculos'
            ))
            ->with('cities', $this->prepareCities())
            ->with('estados', $this->prepareEstados())
            ->with('paises', $this->preparePaises());
    }

    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        $ordem = OrdemServico::find($id);

        try {
            $ordem->business_id = $business_id;
            $ordem->location_id = $request->location_id;
            $ordem->cliente_id = $request->cliente_id;
            $ordem->service_type = $request->service_type;
            $ordem->pick_up_on_site_addr = $request->pick_up_on_site_addr;
            $ordem->descricao = $request->comment_by_ss;
            $ordem->valor = $request->valor ? $request->total_os : $ordem->valor;
            $ordem->status_id = $request->status_id;
            $ordem->data_entrega = $this->parseDate($request->delivery_date);
            $ordem->nfe_id = 0;
            $ordem->observacao = $request->observacao;
            $ordem->funcionario_id = $request->funcionario_id;
            $ordem->veiculo_id = $request->veiculo_id;
            $ordem->save();

            if (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action('OrdemServicoController@addParts', [$ordem->id])
                    ->with('status', [
                        'success' => true,
                        'msg' => __("lang_v1.success")
                    ]);
            }
            $output = [
                'success' => 1,
                'msg' => "OS atualizada com sucesso!"
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->route('ordem-servico.index')->with('status', $output);
    }

    public function updateValorOs(Request $request, $id)
    {
        // dd($request);
        $item = OrdemServico::find($id);
        try {
            $item->valor = $this->__convert_value_bd($request->total_os);
            $item->save();
            $output = [
                'success' => 1,
                'msg' => "Valor da Os atualizado com sucesso!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->route('ordem-servico.index')->with('status', $output);
    }

    // private function prepareClientes()
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     $clientes = Contact::where('business_id', $business_id)
    //         ->orderBy('name')
    //         ->get();

    //     $temp = [];
    //     foreach ($clientes as $c) {
    //         if ($c->name != 'Cliente padrão')
    //             $temp[$c->id] = $c->name . " ($c->cpf_cnpj)";
    //     }
    //     return $temp;
    // }

    public function addParts($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $ordem_servico = OrdemServico::find($id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $servicos = Servico::where('business_id', $business_id)->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
            }
        }

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;

        if ($check_qty == true) {
            $produtos = Product::leftJoin('variation_location_details as vld', 'vld.product_id', '=', 'products.id')
                ->Join('variations as v', 'v.id', '=', 'vld.variation_id')
                ->where('business_id', $business_id)
                ->where('not_for_selling', false)
                ->select('products.*', 'vld.qty_available', 'v.default_sell_price')
                ->where('vld.qty_available', '>', 0)
                ->get();
        } else {
            $produtos = Product::where('business_id', $business_id)->get();
            // dd($produtos);
        }

        return view('ordem_servico.parts.add_parts', compact('ordem_servico', 'servicos', 'produtos', 'default_location', 'check_qty'));
    }

    public function storeServico(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $valor = $ordem->valor + ($this->__convert_value_bd($request->valor) * $this->__convert_value_bd($request->quantidade));
        $ordem->valor = $valor;
        $ordem->save();
        try {
            ServicoOs::create([
                'servico_id' => $request->servico_id,
                'ordem_servico_id' => $ordem->id,
                'quantidade' => $this->__convert_value_bd($request->quantidade),
                'valor_unitario' => $this->__convert_value_bd($request->valor),
                'sub_total' => $this->__convert_value_bd($request->quantidade) * $this->__convert_value_bd($request->valor)
            ]);
            $output = [
                'success' => 1,
                'msg' => "Serviço adicionado com sucesso!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function deletarServico($id)
    {
        $produtoOs = ServicoOs::where('id', $id)->first();
        $ordem = OrdemServico::where('id', $produtoOs->ordem_servico_id)->first();
        $valor = $ordem->valor - $produtoOs->subtotal;
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $produtoOs->delete();

            $output = [
                'success' => 1,
                'msg' => "Serviço removido com sucesso!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function storeProduto(Request $request)
    {
        // dd($request);
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $valor = $ordem->valor + ($this->__convert_value_bd($request->valor_produto) * $this->__convert_value_bd($request->quantidade_produto));
        $ordem->valor = $valor;
        $ordem->save();
        try {
            ProdutoOs::create([
                'produto_id' => $request->produto_id,
                'ordem_servico_id' => $ordem->id,
                'quantidade' => $this->__convert_value_bd($request->quantidade_produto),
                'valor_unitario' => $this->__convert_value_bd($request->valor_produto),
                'sub_total' => $this->__convert_value_bd($request->quantidade_produto) * $this->__convert_value_bd($request->valor_produto),
                'variation_id' => $request->variation_id
            ]);

            $output = [
                'success' => 1,
                'msg' => "Produto adicionado com sucesso!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    public function deletarProduto($id)
    {
        $produtoOs = ProdutoOs::where('id', $id)->first();
        $ordem = OrdemServico::where('id', $produtoOs->ordem_servico_id)->first();
        $valor = $ordem->valor - $produtoOs->subtotal;
        $ordem->valor = $valor;
        $ordem->save();
        try {
            $produtoOs->delete();
            $output = [
                'success' => 1,
                'msg' => "Produto removido com sucesso!"
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => "Algo deu errado: " . $e->getMessage()
            ];
        }
        return redirect()->back()->with('status', $output);
    }

    private function __convert_value_bd($valor)
    {
        if (strlen($valor) >= 8) {
            $valor = str_replace(".", "", $valor);
        }
        $valor = str_replace(",", ".", $valor);
        return $valor;
    }

    public function imprimirViaCliente($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $business_id = request()->session()->get('user.business_id');

        $config = Business::where('id', $business_id)->first();

        return view('ordem_servico.imprimir_cliente')
            ->with(compact('ordem', 'config'));
    }

    public function imprimirViaFuncionario($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        $business_id = request()->session()->get('user.business_id');

        $config = Business::where('id', $business_id)->first();

        return view('ordem_servico.imprimir_funcionario')
            ->with(compact('ordem', 'config'));
    }


    public function destroy($id)
    {
        if (request()->ajax()) {
            $item = OrdemServico::findOrFail($id);
            try {
                $business_id = request()->session()->get('user.business_id');
                $item->servicos()->delete();
                $item->relatorios()->delete();
                $item->itens()->delete();

                $item->delete();

                $output = [
                    'success' => 1,
                    'msg' => "OS removida com sucesso!"
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong") . $e->getMessage();
            }
            return $output;
        }
    }

    public function gerarNfe($id)
    {
        $row_count = request()->get('product_row');
        $row_count = $row_count + 1;

        $business_id = request()->session()->get('user.business_id');
        $ordem = OrdemServico::find($id);
        // echo $ordem->itens;
        // die;
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
            }
        }
        $business_details = $this->businessUtil->getDetails($business_id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $default_datetime = $this->businessUtil->format_date('now', true);
        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        $naturezas = $this->prepareNaturezas();
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $payment_lines[] = $this->dummyPaymentLine;
        $pt = ($this->transactionUtil->payment_types());

        $payment_types = $pt;
        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $usuario = User::allUsersDropdown($business_id, false);

        $isOrdemServico = 1;

        $tipo_venda = 1;


        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        return view('sell.create')
            ->with('tipo', 'customer')
            ->with('cities', $this->prepareCities())
            ->with('ufs', $this->prepareUFs())
            ->with('tiposFrete', $this->prepareTiposFrete())
            ->with('transportadoras', $this->prepareTransportadoras())

            ->with(compact(
                'default_location',
                'business_details',
                'walk_in_customer',
                'default_datetime',
                'invoice_schemes',
                'default_invoice_schemes',
                'naturezas',
                'taxes',
                'shipping_statuses',
                'payment_lines',
                'payment_types',
                'change_return',
                'types',
                'usuario',
                'isOrdemServico',
                'ordem',
                'row_count',
                'tipo_venda'
            ));
    }

    private function prepareNaturezas()
    {
        $business_id = request()->session()->get('user.business_id');
        $naturezas = NaturezaOperacao::where('business_id', $business_id)
            ->where('finNFe', '!=', 4)
            ->get();
        $temp = [];
        foreach ($naturezas as $c) {
            $temp[$c->id] = $c->natureza;
        }
        return $temp;
    }

    private function prepareUFs()
    {
        return [
            "AC" => "AC",
            "AL" => "AL",
            "AM" => "AM",
            "AP" => "AP",
            "BA" => "BA",
            "CE" => "CE",
            "DF" => "DF",
            "ES" => "ES",
            "GO" => "GO",
            "MA" => "MA",
            "MG" => "MG",
            "MS" => "MS",
            "MT" => "MT",
            "PA" => "PA",
            "PB" => "PB",
            "PE" => "PE",
            "PI" => "PI",
            "PR" => "PR",
            "RJ" => "RJ",
            "RN" => "RN",
            "RS" => "RS",
            "RO" => "RO",
            "RR" => "RR",
            "SC" => "SC",
            "SE" => "SE",
            "SP" => "SP",
            "TO" => "TO"
        ];
    }

    private function prepareTiposFrete()
    {
        return [
            '0' => 'Emitente',
            '1' => 'Destinatário',
            '2' => 'Terceiros',
            '9' => 'Sem frete',
        ];
    }

    private function prepareTransportadoras()
    {
        $business_id = request()->session()->get('user.business_id');
        $transportadoras = Transportadora::where('business_id', $business_id)
            ->get();
        $temp = [];
        foreach ($transportadoras as $t) {
            $temp[$t->id] = $t->razao_social;
        }
        return $temp;
    }
}
