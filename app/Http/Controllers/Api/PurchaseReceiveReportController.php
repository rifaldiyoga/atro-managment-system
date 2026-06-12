<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceiveReportController extends Controller
{
  public function getReport(Request $request, $type)
  {
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    $query = null;

    switch ($type) {
      case 'detail-penerimaan':
        $query = DB::table('prcv')
          ->join('prcvd', 'prcv.id', '=', 'prcvd.prcv_id')
          ->leftJoin('bp', 'prcv.bp_id', '=', 'bp.id')
          ->select('prcv.trxno', 'prcv.trxdate', 'prcv.reqdtime', 'bp.name as vendor_name', 'prcvd.itemname', 'prcvd.qty', 'prcvd.unit', 'prcvd.listprice', 'prcvd.subtotal')
          ->where('prcv.isvoid', false);
        break;

      case 'rekap-penerimaan':
        $query = DB::table('prcv')
          ->leftJoin('bp', 'prcv.bp_id', '=', 'bp.id')
          ->select('prcv.trxno', 'prcv.trxdate', 'prcv.reqdtime', 'bp.name as vendor_name', 'prcv.status')
          ->where('prcv.isvoid', false);
        break;

      case 'rekap-produk':
        $query = DB::table('prcvd')
          ->join('prcv', 'prcv.id', '=', 'prcvd.prcv_id')
          ->select('prcvd.itemname', DB::raw('SUM(prcvd.qty) as total_qty'), 'prcvd.unit', DB::raw('SUM(prcvd.subtotal) as total_amount'))
          ->where('prcv.isvoid', false)
          ->groupBy('prcvd.itemname', 'prcvd.unit');
        break;

      case 'rekap-vendor':
        $query = DB::table('prcv')
          ->leftJoin('bp', 'prcv.bp_id', '=', 'bp.id')
          ->leftJoin('prcvd', 'prcv.id', '=', 'prcvd.prcv_id')
          ->select('bp.name as vendor_name', DB::raw('COUNT(DISTINCT prcv.id) as total_penerimaan'), DB::raw('SUM(prcvd.qty) as total_qty'))
          ->where('prcv.isvoid', false)
          ->groupBy('bp.name');
        break;

      default:
        return response()->json(['error' => 'Invalid report type'], 400);
    }

    $this->applyDateFilter($query, 'prcv.trxdate', $startDate, $endDate);

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
