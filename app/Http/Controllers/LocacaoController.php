<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\BusinessLocation;
use App\Models\Contact;
use App\Models\User;
use App\Models\Revenue;
use App\Models\Locacao;
use App\Utils\TransactionUtil;

use Illuminate\Support\Facades\DB;

use Yajra\DataTables\Facades\DataTables;

use Illuminate\Http\Request;

class LocacaoController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }
    public function index(Request $request)
    {
        if (!auth()->user()->can('sell.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('locacaos', 'transactions.id', '=', 'locacaos.transaction_id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.is_locacao', true)
                ->select(
                    'transactions.*',
                    'contacts.name',
                    'locacaos.data_abertura',
                    'locacaos.valor',
                    'locacaos.dias_em_locacao',
                    'locacaos.status as locacao_status'
                );

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            }

            if ($request->has('status') && $request->status != '') {
                if ($request->status === 'aberta') {
                    // Locação aberta
                    $query->whereHas('locacaos', function ($q) {
                        $q->where('status', 'aberta');
                    });
                } elseif ($request->status === 'fechada') {
                    // Locação fechada com transaction status pendente
                    $query->whereHas('locacaos', function ($q) {
                        $q->where('status', 'fechada');
                    });
                }
            }

            if ($request->has('status_pagamento') && $request->status_pagamento != '') {
                $query->where(function ($q) use ($request) {
                    if ($request->status_pagamento === 'pendente') {
                        $q->whereExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('revenues')
                                ->whereRaw("revenues.referencia LIKE CONCAT('Venda ', transactions.id)")
                                ->where('revenues.status', 0);
                        });
                    } elseif ($request->status_pagamento === 'pago') {
                        $q->whereNotExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('revenues')
                                ->whereRaw("revenues.referencia LIKE CONCAT('Venda ', transactions.id)")
                                ->where('revenues.status', 0);
                        });
                    }
                });
            }
          

            if (!empty($request->location_id)) {
                $query->where('location_id', $request->location_id);
            }

            if (!empty($request->customer_id)) {
                $query->where('contacts.id', $request->customer_id);
            }

            if (!empty($request->created_by)) {
                $query->where('created_by', $request->created_by);
            }

            return DataTables::of($query)
                ->filterColumn('name', function($query, $keyword) {
                    $query->whereRaw("LOWER(contacts.name) LIKE ?", ["%".strtolower($keyword)."%"]);
                })

                // ->addColumn('data_abertura', function ($row) {
                //     return $this->formatarDataHora($row->locacaos->first()->data_abertura) ?? '--';
                // })
                ->addColumn('data_abertura', function ($row) {
                    return $this->formatarDataHora($row->data_abertura) ?? '--';
                })

                ->addColumn('dias_em_locacao', function ($row) {
                    if ($row->locacaos->isNotEmpty()) {
                        $dias_em_locacao = $row->locacaos->first()->dias_em_locacao;
                        $dias_totais = $row->locacaos->first()->dias_total;
                        $status = $row->locacaos->first()->status;
                        if ($status == 'aberta') {
                            return $dias_em_locacao;
                        } else {
                            return $dias_totais;
                        }
                    }
                    return '--';
                })

                ->addColumn('excedentes', function ($row) {
                    if ($row->locacaos->isNotEmpty()) {
                        $locacao = $row->locacaos->first();
                        $status = $row->locacaos->first()->status;
                        if ($status == 'aberta') {
                            if (isset($locacao->dias_em_locacao)) {
                                $data_abertura = \Carbon\Carbon::parse($locacao->data_abertura);
                                $dias_em_locacao = $locacao->dias_em_locacao;
                                $data_limite = $data_abertura->addDays($dias_em_locacao);
                                if (\Carbon\Carbon::now()->greaterThan($data_limite)) {
                                    return $data_limite->diffInDays(\Carbon\Carbon::now());
                                }
                                return 0;
                            }
                        }
                    }
                    return '--';
                })

                ->addColumn(
                    'status',
                    function ($row) {
                        $status = '';
                        if ($row->locacaos->isNotEmpty()) {
                            $locacao = $row->locacaos->first();
                            $locacao_status = $locacao->status;
                            $transaction_status = $row->status; // Status da transação na tabela transactions
                            if ($locacao_status === 'fechada' && $transaction_status === 'pending') {
                                $status = __('Encerrada');
                            } elseif ($locacao_status === 'aberta') {
                                $status = __('Aberta');
                            } elseif ($locacao_status === 'fechada' && $transaction_status === 'final') {
                                $status = __('Finalizada');
                            }
                        }

                        return $status;
                    }
                )

                ->addColumn(
                    'status_pagamento',
                    function ($row) {
                        $row = $row->id;
                        $status_pagamento = DB::table('revenues')
                            ->where('referencia', 'LIKE', 'Venda ' . $row)
                            ->where('status', 0)
                            ->exists();
                        return $status_pagamento ? 'Pendente' : 'Pago';
                    }
                )

                

                ->addColumn('valor', function ($row) {
                    $valor = $row->locacaos->first()->valor ?? 0;
                    return number_format($valor, 2, ',', '.');
                })

                ->addColumn(
                    'action',
                    function ($row) {

                        $is_final = isset($row->status) && $row->status === 'final' && $row->payment_status != null;

                        $html = '<a href="#" data-href="' . action('SellController@show', [$row->id]) . '" class="btn btn-xs btn-success btn-modal" data-container=".view_modal">
                        <i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '
                     </a>';

                        if (!$is_final) {
                            $html .= '<a target="_blank" href="' . action('SellController@edit', [$row->id]) . '" class="btn btn-xs btn-primary m-2">
                        <i class="fas fa-edit"></i> ' . __('messages.edit') . '
                     </a>';

                            $html .= '&nbsp;<a href="' . action('LocacaoController@destroy', [$row->id]) . '" 
                     class="delete-locacao btn btn-xs  me-2 btn-danger">
                         <i class="fas fa-trash"></i> ' . __("messages.delete") . '
                     </a>';
                        }

                        if ($row->locacaos->isNotEmpty()) {
                            $locacao = $row->locacaos->first();
                            $status = $locacao->status;
                            $id = $locacao->id;
                            if (!$is_final) {
                                if ($status == 'fechada') {
                                    $html .= '&nbsp;<a href="' . action('LocacaoController@finalizar', [$row->id]) . '" class="btn btn-xs btn-warning btn-finalizar">
                                                <i class="fas fa-check"></i> ' . __("Finalizar") . '
                                            </a>';

                                    $html .= '&nbsp;<a href="' . action('LocacaoController@reabrir', [$id]) . '" class="btn btn-xs btn-success  me-2 reabrir-locacao">
                                                <i class="fas fa-redo"></i> ' . __("Reabrir") . '
                                            </a>';
                                } elseif ($status == 'aberta') {
                                    $html .= '&nbsp;<a href="' . action('LocacaoController@encerrar', [$id]) . '" class="btn btn-xs btn-info">
                                                <i class="fas fa-stop"></i> ' . __("Encerrar") . '
                                            </a>';
                                }
                            }
                        }

                        return $html;
                    }
                )

                ->addColumn('valor_total', function ($row) {
                    if ($row->locacaos->isNotEmpty()) {
                        $locacao = $row->locacaos->first();
                        $status = $row->locacaos->first()->status;

                        if ($status == 'aberta') {
                            if (isset($locacao->dias_em_locacao)) {
                                $data_abertura = \Carbon\Carbon::parse($locacao->data_abertura);
                                $dias_em_locacao = $locacao->dias_em_locacao;
                                $data_limite = $data_abertura->addDays($dias_em_locacao);
                                $dias_excedentes = 0;
                                if (\Carbon\Carbon::now()->greaterThan($data_limite)) {
                                    $dias_excedentes = $data_limite->diffInDays(\Carbon\Carbon::now());
                                }
                                $total_dias = $dias_em_locacao + $dias_excedentes;
                                $valor = $locacao->valor ?? 0;
                                $valor_total = $total_dias * $valor;
                                return number_format($valor_total, 2, ',', '.');
                            }
                        } else {
                            $valor_total = $row->locacaos->first()->valor_total;
                            return $valor_total;
                        }
                    }
                    return number_format(0, 2, ',', '.');
                })

                ->editColumn('transaction_date', '{{ @format_date($transaction_date) }}')
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ?? '--';
                })
                ->editColumn('name', function ($row) {
                    return $row->name ?? '--';
                })
                ->editColumn('business_location', function ($row) {
                    return $row->businessLocation->name ?? '--';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('locacao.index')
            ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    function formatarDataHora($datetime)
    {
        return date("d/m/Y H:i:s", strtotime($datetime));
    }


    public function destroy($id)
    {
        if (!auth()->user()->can('locacao.delete')) { // Permissão específica para locação
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                // Begin transaction
                DB::beginTransaction();

                // Buscar a transação e verificar se existe
                $transaction = Transaction::find($id);

                if (!$transaction) {
                    return [
                        'success' => false,
                        'msg' => "Transação não encontrada.",
                    ];
                }

                // Remover os registros associados na tabela de locação
                $locacao = $transaction->locacaos()->first(); // Relacionamento com locação
                if ($locacao) {
                    $locacao->delete();
                }

                // Verificar se a NFCe foi emitida
                if ($transaction->numero_nfce > 0) {
                    $output['success'] = false;
                    $output['msg'] = "Não é possível remover locação com NFCe emitida!";
                } else {
                    // Remover registros na tabela de receitas, se necessário
                    Revenue::where('referencia', 'Venda ' . $transaction->id)->delete();

                    // Remover a transação
                    $transaction->delete();

                    $output['success'] = true;
                    $output['msg'] = "Locação removida com sucesso!";
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong") . $e->getMessage();
            }

            return $output;
        }
    }

    public function encerrar($id)
    {
        try {
            DB::beginTransaction();
            $locacao = Locacao::findOrFail($id);
            if ($locacao->status !== 'aberta') {
                return response()->json([
                    'success' => false,
                    'msg' => 'Somente locações abertas podem ser encerradas.'
                ]);
            }
            $data_abertura = \Carbon\Carbon::parse($locacao->data_abertura);

            $dias_em_locacao_contratados = $locacao->dias_em_locacao ?? 0;
            $data_limite = $data_abertura->copy()->addDays($dias_em_locacao_contratados);
            $dias_excedentes = 0;
            if (\Carbon\Carbon::now()->greaterThan($data_limite)) {
                $dias_excedentes = $data_limite->diffInDays(\Carbon\Carbon::now());
            }
            $total_dias = $dias_em_locacao_contratados + $dias_excedentes;
            $valor_total = $total_dias * ($locacao->valor ?? 0);

            $locacao->status = 'fechada';
            $locacao->dias_total = $total_dias; // Atualiza os dias totais
            $locacao->dias_excedentes = $dias_excedentes; // Atualiza os dias excedentes
            $locacao->valor_total = $valor_total; // Atualiza o valor total
            $locacao->save();
            DB::commit();
            $output = [
                'success' => true,
                'msg' => 'Locação encerrada com sucesso!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erro ao encerrar locação: {$e->getMessage()}");

            $output = [
                'success' => false,
                'msg' => 'Erro ao encerrsar locação'
            ];
        }
        return redirect()->route('locacoes.index')->with('status', $output);
    }

    public function reabrir($id)
    {
        try {
            DB::beginTransaction();
            $locacao = Locacao::findOrFail($id);
            if ($locacao->status !== 'fechada') {
                return response()->json([
                    'success' => false,
                    'msg' => 'Somente locações fechadas podem ser reabertas.'
                ]);
            }
            $locacao->status = 'aberta';
            $locacao->dias_em_locacao = $locacao->dias_total; // Limpar dias fixos
            $locacao->valor_total = null; // Limpar valor total
            $locacao->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'msg' => 'Locação reaberta com sucesso! Os dias voltarão a ser contados.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erro ao reabrir locação: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'msg' => 'Falha ao reabrir locação: ' . $e->getMessage()
            ]);
        }
    }

    public function finalizar($id)
    {
        try {
            DB::beginTransaction();

            $locacao = Locacao::where('transaction_id', $id)->first();

            $transaction = Transaction::findOrFail($id);

            // $revenue = Revenue::where('referencia', 'Venda ' . $transaction->id)->first();
            // $revenue->status = true;
            // $revenue->valor_recebido = $revenue->valor_total;
            // $revenue->save();

            $transaction->status = 'final';
            $transaction->is_quotation = 0;
            $transaction->is_direct_sale = 0;
            $transaction->transaction_date = now();
            $transaction->final_total = $locacao->valor_total;
            $transaction->save();

            foreach ($transaction->sell_lines as $line) {
                $line->quantity = $locacao->dias_total; // Exemplo: Incrementa a quantidade
                $line->save();
            }

            // foreach ($transaction->payment_lines as $payment_line) {
            //     $payment_line->amount = $locacao->valor_total;
            //     $payment_line->save();
            // }

            DB::commit();

            // Redirecionar para a rota desejada
            // return redirect()->action('SellPosController@edit', [$transaction->id])
            //     ->with('success', 'Transação finalizada com sucesso!');
            return redirect()->action('SellPosController@edit', [$transaction->id, 'status' => 'locacao'])
                ->with('success', 'Transação finalizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erro ao finalizar transação: {$e->getMessage()}");

            return redirect()->back()->with('error', 'Ocorreu um erro ao finalizar a transação.');
        }
    }

    public function getDatatableLocacoes()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->with(['locacaos'])
                ->where('transactions.business_id', $business_id)
                ->where('transactions.is_locacao', true)
                ->select(
                    'transactions.*',
                    'contacts.name'
                );

            // Filtro por nome do cliente
            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $query->where('contacts.id', $customer_id);
            }

            return Datatables::of($query)
                ->addColumn('data_abertura', function ($row) {
                    return $this->formatarDataHora($row->locacaos->first()->data_abertura) ?? '--';
                })
                ->editColumn('name', function ($row) {
                    return $row->name ?? '--';
                })
                ->addColumn('status', function ($row) {
                    $status = '';
                    if ($row->locacaos->isNotEmpty()) {
                        $locacao = $row->locacaos->first();
                        $locacao_status = $locacao->status;
                        $transaction_status = $row->status;
                        if ($locacao_status === 'fechada' && $transaction_status === 'pending') {
                            $status = __('Encerrada');
                        } elseif ($locacao_status === 'aberta') {
                            $status = __('Aberta');
                        } elseif ($locacao_status === 'fechada' && $transaction_status === 'final') {
                            $status = __('Finalizada');
                        }
                    }
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    // Seu código de ações aqui
                    return '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}
