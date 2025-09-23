<?php

namespace App\Http\Controllers;

use App\Models\BusinessLocation;
use App\Models\Transaction;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Datatables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FiscalInventoryReportController extends Controller
{
    private TransactionUtil $helper;

    private $purchase_qtd = 0;
    private $sell_qtd = 0;
    private $business_id;

    public function __construct(TransactionUtil $helper)
    {
        $this->helper = $helper;
    }

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        return view('fiscal-inventory-report.index', ['business_locations' => $business_locations]);
    }

    public function fillReport(Request $request)
    {
        $query = $this->buildBaseQuery(request()->session()->get('user.business_id'));
        $this->applyLocationFilter($query, $request->input('location_id'));
        $this->applyDateRangeFilter($query, $request->input('start_date'), $request->input('end_date'));
        return $this->mountDataTable($query);
    }

    private function buildBaseQuery($id)
    {
        return Transaction::query()->select(['id', 'location_id', 'estado', 'type'])
            ->with([
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.variations',
                'sell_lines', 
                'sell_lines.product',
                'sell_lines.variations',
            ])
            ->whereNotNull('chave')
            ->whereNotNull('chave_entrada')
            ->where('business_id', $id)
            ->where('estado', 'APROVADO');
    }

    private function applyLocationFilter(Builder $query, $locationId)
    {
        if (!empty($locationId)) {
            $query->where('location_id', $locationId);
        }
    }

    private function applyDateRangeFilter(Builder $query, $startDate, $endDate)
    {
        if (!empty($startDate) && !empty($endDate)) {
            $start = Carbon::parse($this->helper->uf_date($startDate))->startOfDay();
            $end = Carbon::parse($this->helper->uf_date($endDate))->endOfDay();

            $query->where(function (Builder $subQuery) use ($start, $end) {
                $subQuery->whereHas('purchase_lines', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                    ->orWhereHas('sell_lines', fn($q) => $q->whereBetween('created_at', [$start, $end]));
            });
        }
    }

    private function mountDataTable($queryBuilder)
    {
        $normalizedData = $this->normalizeData($queryBuilder);
        $groupedData = $this->groupBySku($normalizedData);

        $datatable = Datatables::of(collect($groupedData))
            ->editColumn('sku', fn($row) => $row['sku'])
            ->editColumn('product', fn($row) => $row['product'])
            ->editColumn('qtd_purchase', fn($row) => $row['qtd_purchase'])
            ->editColumn('qtd_sell', fn($row) => $row['qtd_sell'])
            ->editColumn('amount_purchase', fn($row) => "R$" . number_format($row['amount_purchase'], 2, ',', '.'))
            ->editColumn('amount_sell', fn($row) => "R$" . number_format($row['amount_sell'], 2, ',', '.'))
            ->editColumn('currente_stock', fn($row) => $row['currente_stock']);
        return $datatable->make(true);
    }

    private function normalizeData($queryBuilder)
    {
        $data = $queryBuilder->get()->toArray();

        $normalized = [];

        foreach ($data as $row) {
            foreach ($row['purchase_lines'] as $purchaseLine) {
                $normalized[] = [
                    'sku' => $purchaseLine['product']['sku'],
                    'product' => $purchaseLine['product']['name'],
                    'qtd_purchase' => (float)$purchaseLine['quantity'],
                    'qtd_sell' => 0,
                    'amount_purchase' => (float)$purchaseLine['variations']['default_purchase_price'],
                    'amount_sell' => 0,
                    'currente_stock' => 0,
                ];
            }

            foreach ($row['sell_lines'] as $sellLine) {
                $normalized[] = [
                    'sku' => $sellLine['product']['sku'],
                    'product' => $sellLine['product']['name'],
                    'qtd_purchase' => 0,
                    'qtd_sell' => (float)$sellLine['quantity'],
                    'amount_purchase' => 0,
                    'amount_sell' => (float)$sellLine['variations']['sell_price_inc_tax'],
                    'currente_stock' => 0,
                ];
            }
        }
        return $normalized;
    }

    private function groupBySku($data)
    {
        $grouped = [];

        foreach ($data as $row) {
            $sku = $row['sku'];

            if (!isset($grouped[$sku])) {
                $grouped[$sku] = $row;

                $grouped[$sku]['currente_stock'] = $grouped[$sku]['qtd_purchase'] - $grouped[$sku]['qtd_sell'];
            } else {
                $grouped[$sku]['qtd_purchase'] += $row['qtd_purchase'];
                $grouped[$sku]['qtd_sell'] += $row['qtd_sell'];

                if ($grouped[$sku]['amount_purchase'] == 0 && $row['amount_purchase'] > 0) {
                    $grouped[$sku]['amount_purchase'] = $row['amount_purchase'];
                }

                if ($grouped[$sku]['amount_sell'] == 0 && $row['amount_sell'] > 0) {
                    $grouped[$sku]['amount_sell'] = $row['amount_sell'];
                }
                
                $grouped[$sku]['currente_stock'] = $grouped[$sku]['qtd_purchase'] - $grouped[$sku]['qtd_sell'];
            }
        }
        
        return array_values($grouped);
    }
}
