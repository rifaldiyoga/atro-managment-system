<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\StockPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
  public function index(Request $request)
  {
    $query = StockMovement::with('item')->orderBy('trxdate', 'desc')->orderBy('id', 'desc');
    if ($request->query('item_id')) $query->where('item_id', $request->query('item_id'));
    if ($request->query('source_type')) $query->where('source_type', strtoupper($request->query('source_type')));

    $records = $query->paginate($request->query('per_page', 10));

    return response()->json([
      'status' => 'success',
      'message' => 'Stock movement list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
      ],
    ]);
  }

  public function store(Request $request, StockPostingService $stockPosting)
  {
    $data = $request->validate([
      'trxno' => 'nullable|string|max:50',
      'trxdate' => 'required|date',
      'note' => 'nullable|string',
      'details' => 'required|array|min:1',
      'details.*.item_id' => 'required|integer|exists:items,id',
      'details.*.wh_id' => 'nullable|integer',
      'details.*.movement_type' => 'required|in:IN,OUT',
      'details.*.qty' => 'required|numeric|min:0.0001',
      'details.*.unit_cost' => 'nullable|numeric|min:0',
      'details.*.note' => 'nullable|string',
    ]);

    $adjustment = DB::transaction(function () use ($data, $stockPosting) {
      $adjustment = StockAdjustment::create([
        'trxno' => $data['trxno'] ?? 'AUTO',
        'trxdate' => $data['trxdate'],
        'status' => 'POSTED',
        'note' => $data['note'] ?? null,
        'created_by' => auth()->id() ?? 1,
        'updated_by' => auth()->id() ?? 1,
      ]);

      foreach ($data['details'] as $index => $detail) {
        $adjustmentDetail = $adjustment->details()->create([
          'dno' => $index + 1,
          ...$detail,
        ]);

        $stockPosting->record([
          'trxdate' => $adjustment->trxdate,
          'trxno' => $adjustment->trxno,
          'movement_type' => $detail['movement_type'],
          'source_type' => 'SADJ',
          'source_id' => $adjustment->id,
          'source_detail_id' => $adjustmentDetail->id,
          'item_id' => $detail['item_id'],
          'wh_id' => $detail['wh_id'] ?? null,
          'qty_in' => $detail['movement_type'] === 'IN' ? $detail['qty'] : 0,
          'qty_out' => $detail['movement_type'] === 'OUT' ? $detail['qty'] : 0,
          'unit_cost' => $detail['unit_cost'] ?? 0,
          'total_cost' => ($detail['unit_cost'] ?? 0) * $detail['qty'],
          'note' => $detail['note'] ?? $adjustment->note,
        ]);
      }

      return $adjustment->load('details');
    });

    return response()->json(['status' => 'success', 'message' => 'Stock adjustment created successfully', 'data' => $adjustment], 201);
  }

  public function show($id)
  {
    $movement = StockMovement::with('item')->find($id);
    if (!$movement) return response()->json(['status' => 'error', 'message' => 'Stock movement not found', 'data' => null], 404);

    return response()->json(['status' => 'success', 'message' => 'Stock movement fetched successfully', 'data' => $movement]);
  }
}
