<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockReportController extends Controller
{
  public function getReport(string $type, Request $request)
  {
    return match ($type) {
      'stock-card' => $this->stockCard($request),
      'stock-summary' => $this->stockSummary($request),
      default => response()->json(['status' => 'error', 'message' => 'Report not found', 'data' => null], 404),
    };
  }

  private function stockCard(Request $request)
  {
    $query = StockMovement::with('item')->orderBy('trxdate')->orderBy('id');
    if ($request->query('item_id')) $query->where('item_id', $request->query('item_id'));
    if ($request->query('date_from')) $query->whereDate('trxdate', '>=', $request->query('date_from'));
    if ($request->query('date_to')) $query->whereDate('trxdate', '<=', $request->query('date_to'));

    return response()->json(['status' => 'success', 'message' => 'Stock card fetched successfully', 'data' => $query->get()]);
  }

  private function stockSummary(Request $request)
  {
    $query = StockBalance::with('item')->orderBy('item_id');
    if ($request->query('item_id')) $query->where('item_id', $request->query('item_id'));

    return response()->json(['status' => 'success', 'message' => 'Stock summary fetched successfully', 'data' => $query->get()]);
  }
}
