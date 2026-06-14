<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockBalance;
use Illuminate\Http\Request;

class StockBalanceController extends Controller
{
  public function index(Request $request)
  {
    $query = StockBalance::with('item')->orderBy('item_id');
    if ($request->query('item_id')) $query->where('item_id', $request->query('item_id'));

    $records = $query->paginate($request->query('per_page', 10));

    return response()->json([
      'status' => 'success',
      'message' => 'Stock balance list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
      ],
    ]);
  }

  public function show($id)
  {
    $balance = StockBalance::with('item')->find($id);
    if (!$balance) return response()->json(['status' => 'error', 'message' => 'Stock balance not found', 'data' => null], 404);

    return response()->json(['status' => 'success', 'message' => 'Stock balance fetched successfully', 'data' => $balance]);
  }
}
