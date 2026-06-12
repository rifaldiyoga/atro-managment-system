<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller
{
  public function getReport(Request $request, $type)
  {
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    $query = null;

    switch ($type) {
      case 'detail-faktur':
        $query = DB::table('purc')
          ->join('purcd', 'purc.id', '=', 'purcd.purc_id')
          ->leftJoin('bp', 'purc.bp_id', '=', 'bp.id')
          ->select('purc.trxno', 'purc.trxdate', DB::raw('NULL as due_date'), 'bp.name as vendor_name', 'purcd.itemname', 'purcd.qty', 'purcd.unit', 'purcd.listprice', 'purcd.subtotal')
          ->where('purc.isvoid', false);
        break;

      case 'rekap-faktur':
        $query = DB::table('purc')
          ->leftJoin('bp', 'purc.bp_id', '=', 'bp.id')
          ->select('purc.trxno', 'purc.trxdate', DB::raw('NULL as due_date'), 'bp.name as vendor_name', 'purc.subtotal', 'purc.discamt', 'purc.taxamt', 'purc.total', 'purc.status')
          ->where('purc.isvoid', false);
        break;

      case 'rekap-produk':
        $query = DB::table('purcd')
          ->join('purc', 'purc.id', '=', 'purcd.purc_id')
          ->select('purcd.itemname', DB::raw('SUM(purcd.qty) as total_qty'), 'purcd.unit', DB::raw('SUM(purcd.subtotal) as total_amount'))
          ->where('purc.isvoid', false)
          ->groupBy('purcd.itemname', 'purcd.unit');
        break;

      case 'rekap-vendor':
        $query = DB::table('purc')
          ->leftJoin('bp', 'purc.bp_id', '=', 'bp.id')
          ->select('bp.name as vendor_name', DB::raw('COUNT(purc.id) as total_invoices'), DB::raw('SUM(purc.total) as total_amount'))
          ->where('purc.isvoid', false)
          ->groupBy('bp.name');
        break;

      default:
        return response()->json(['error' => 'Invalid report type'], 400);
    }

    $this->applyDateFilter($query, 'purc.trxdate', $startDate, $endDate);

    return response()->json([
      'success' => true,
      'data' => $query->get(),
    ]);
  }

  private function applyDateFilter($query, string $column, ?string $startDate, ?string $endDate): void
  {
    if ($startDate) $query->where($column, '>=', $startDate);
    if ($endDate) $query->where($column, '<=', $endDate . ' 23:59:59');
  }
}
