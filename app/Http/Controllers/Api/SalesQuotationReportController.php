<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesQuotationReportController extends Controller
{
  public function getReport(Request $request, $type)
  {
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    $query = null;

    switch ($type) {
      case 'detail-faktur':
        $query = DB::table('sq')
          ->join('sqd', 'sq.id', '=', 'sqd.sq_id')
          ->leftJoin('bp', 'sq.bp_id', '=', 'bp.id')
          ->select(
            'sq.trxno',
            'sq.trxdate',
            'bp.name as customer_name',
            'sqd.itemname',
            'sqd.qty',
            'sqd.unit',
            'sqd.listprice',
            'sqd.subtotal'
          )
          ->where('sq.isvoid', false);
        break;

      case 'rekap-faktur':
        $query = DB::table('sq')
          ->leftJoin('bp', 'sq.bp_id', '=', 'bp.id')
          ->select(
            'sq.trxno',
            'sq.trxdate',
            'bp.name as customer_name',
            'sq.subtotal',
            'sq.discamt',
            'sq.taxamt',
            'sq.total',
            'sq.status'
          )
          ->where('sq.isvoid', false);
        break;

      case 'rekap-produk':
        $query = DB::table('sqd')
          ->join('sq', 'sq.id', '=', 'sqd.sq_id')
          ->select(
            'sqd.itemname',
            DB::raw('SUM(sqd.qty) as total_qty'),
            'sqd.unit',
            DB::raw('SUM(sqd.subtotal) as total_amount')
          )
          ->where('sq.isvoid', false)
          ->groupBy('sqd.itemname', 'sqd.unit');
        break;

      case 'riwayat-produk':
        $query = DB::table('sqd')
          ->join('sq', 'sq.id', '=', 'sqd.sq_id')
          ->leftJoin('bp', 'sq.bp_id', '=', 'bp.id')
          ->select(
            'sq.trxdate',
            'sq.trxno',
            'bp.name as customer_name',
            'sqd.itemname',
            'sqd.qty',
            'sqd.unit',
            'sqd.listprice'
          )
          ->where('sq.isvoid', false);
        break;

      case 'rekap-salesman':
        $query = DB::table('sq')
          ->leftJoin('srep', 'sq.srep_id', '=', 'srep.id')
          ->select(
            'srep.name as salesman_name',
            DB::raw('COUNT(sq.id) as total_quotations'),
            DB::raw('SUM(sq.total) as total_amount')
          )
          ->where('sq.isvoid', false)
          ->groupBy('srep.name');
        break;

      default:
        return response()->json(['error' => 'Invalid report type'], 400);
    }

    $this->applyDateFilter($query, 'sq.trxdate', $startDate, $endDate);

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
