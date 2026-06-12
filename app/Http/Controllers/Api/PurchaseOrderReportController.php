<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReportController extends Controller
{
  public function getReport(Request $request, $type)
  {
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    $query = null;

    switch ($type) {
      case 'detail-faktur':
        $query = DB::table('po')
          ->join('pod', 'po.id', '=', 'pod.po_id')
          ->leftJoin('so', 'pod.so_id', '=', 'so.id')
          ->leftJoin('bp', 'po.bp_id', '=', 'bp.id')
          ->select('po.trxno', 'so.trxno as so_trxno', 'po.trxdate', 'bp.name as vendor_name', 'pod.itemname', 'pod.qty', 'pod.unit', 'pod.listprice', 'pod.subtotal')
          ->where('po.isvoid', false);
        break;

      case 'rekap-faktur':
        $query = DB::table('po')
          ->leftJoin('bp', 'po.bp_id', '=', 'bp.id')
          ->select('po.trxno', 'po.trxdate', 'bp.name as vendor_name', 'po.subtotal', 'po.discamt', 'po.taxamt', 'po.total', 'po.status')
          ->where('po.isvoid', false);
        break;

      case 'rekap-produk':
        $query = DB::table('pod')
          ->join('po', 'po.id', '=', 'pod.po_id')
          ->select('pod.itemname', DB::raw('SUM(pod.qty) as total_qty'), 'pod.unit', DB::raw('SUM(pod.subtotal) as total_amount'))
          ->where('po.isvoid', false)
          ->groupBy('pod.itemname', 'pod.unit');
        break;

      case 'riwayat-produk':
        $query = DB::table('pod')
          ->join('po', 'po.id', '=', 'pod.po_id')
          ->leftJoin('bp', 'po.bp_id', '=', 'bp.id')
          ->select('po.trxdate', 'po.trxno', 'bp.name as vendor_name', 'pod.itemname', 'pod.qty', 'pod.unit', 'pod.listprice')
          ->where('po.isvoid', false);
        break;

      case 'rekap-vendor':
        $query = DB::table('po')
          ->leftJoin('bp', 'po.bp_id', '=', 'bp.id')
          ->select('bp.name as vendor_name', DB::raw('COUNT(po.id) as total_orders'), DB::raw('SUM(po.total) as total_amount'))
          ->where('po.isvoid', false)
          ->groupBy('bp.name');
        break;

      default:
        return response()->json(['error' => 'Invalid report type'], 400);
    }

    $this->applyDateFilter($query, 'po.trxdate', $startDate, $endDate);

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
