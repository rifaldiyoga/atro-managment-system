<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalesOrder;
use App\Services\AccountingPostingService;
use App\Services\StockPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
  public function index()
  {
    $perPage   = request()->query('per_page', 10);
    $search    = request()->query('search');
    $sort      = request()->query('sort', 'id');
    $direction = request()->query('direction', 'asc');
    $so_id     = request()->query('so_id');
    $status    = request()->query('status');
    $active    = request()->query('active');

    $query = Sale::with(['bp', 'srep']);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->whereRaw('LOWER(trxno) LIKE ?', ['%' . strtolower($search) . '%']);
      });
    }

    if ($so_id) {
      $query->where('reftype', 'SO')->where('refid', $so_id);
    }

    if ($status) {
      $query->where('status', strtoupper($status));
    }

    if ($active !== null && $active !== '') {
      $query->where('active', filter_var($active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true);
    }

    $allowedSortFields = ['id', 'trxno', 'trxdate', 'status', 'total', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) {
      $sort = 'id';
    }

    $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

    $records = $query->orderBy($sort, $direction)->paginate($perPage);

    return response()->json([
      'status'  => 'success',
      'message' => 'Sale list fetched successfully',
      'data'    => $records->items(),
      'meta'    => [
        'current_page' => $records->currentPage(),
        'last_page'    => $records->lastPage(),
        'per_page'     => $records->perPage(),
        'total'        => $records->total(),
        'sort'         => $sort,
        'direction'    => $direction,
      ]
    ], 200);
  }

  public function show($id)
  {
    $record = Sale::with(['details.item', 'bp', 'srep', 'salesOrder', 'attachments'])->find($id);
    if (!$record) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Sale not found',
        'data'    => null
      ], 404);
    }

    return response()->json([
      'status'  => 'success',
      'message' => 'Sale fetched successfully',
      'data'    => $record
    ], 200);
  }

  public function store(Request $request)
  {
    DB::beginTransaction();
    try {
      $inputData = $request->has('payload')
        ? json_decode($request->input('payload'), true)
        : $request->all();
      $data    = collect($inputData)->except('details')->toArray();
      $details = $inputData['details'] ?? [];

      $data['created_by'] = auth()->id() ?? 1;
      $data['updated_by'] = auth()->id() ?? 1;
      if (!array_key_exists('version', $data)) {
        $data['version'] = 1;
      }
      if (empty($data['billaddr']) && isset($data['shipaddr'])) {
        $data['billaddr'] = $data['shipaddr'];
      }

      $record = Sale::create($data);

      $dno = 1;
      foreach ($details as $detail) {
        $detail['sale_id'] = $record->id;
        $detail['dno']     = $dno++;
        SaleDetail::create($detail);
      }

      // Deactivate source document when converting
      if (!empty($data['reftype']) && !empty($data['refid'])) {
        $this->deactivateSource($data['reftype'], $data['refid']);
      }

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          $path = $file->store('attachments', 'public');
          \App\Models\Attachment::create([
            'reftype'    => 'SALE',
            'refid'      => $record->id,
            'bucket'     => 'public',
            'objkey'     => $path,
            'caption'    => $file->getClientOriginalName(),
            'created_by' => auth()->id() ?? 1,
          ]);
        }
      }

      app(StockPostingService::class)->postSale($record->load('details'));
      app(AccountingPostingService::class)->postSale($record->load('details'));

      DB::commit();

      return response()->json([
        'status'  => 'success',
        'message' => 'Sale created successfully',
        'data'    => $record->load('details', 'attachments')
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status'  => 'error',
        'message' => 'Failed to create sale',
        'data'    => null,
        'error'   => $e->getMessage()
      ], 400);
    }
  }

  /**
   * Mark the source document as converted (active=false, status='CONVERTED').
   * Uses DB::table() to bypass any Eloquent model hooks (e.g. trxno auto-generation).
   */
  private function deactivateSource(string $reftype, mixed $refid): void
  {
    $id = (int) $refid;
    if ($id <= 0) return;

    match (strtoupper($reftype)) {
      'SO'   => DB::table('so')->where('id', $id)->update(['active' => false, 'status' => 'CONVERTED']),
      'SQ'   => DB::table('sq')->where('id', $id)->update(['active' => false, 'status' => 'CONVERTED']),
      'DELI' => DB::table('deli')->where('id', $id)->update(['active' => false, 'status' => 'CONVERTED']),
      default => null,
    };
  }

  public function update(Request $request, $id)
  {
    $record = Sale::find($id);
    if (!$record) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Sale not found',
        'data'    => null
      ], 404);
    }

    DB::beginTransaction();
    try {
      $inputData = $request->has('payload')
        ? json_decode($request->input('payload'), true)
        : $request->all();
      $data    = collect($inputData)->except('details')->toArray();
      $details = $inputData['details'] ?? [];

      $data['updated_by'] = auth()->id() ?? 1;
      $data['version']    = $record->version + 1;
      if (empty($data['billaddr']) && isset($data['shipaddr'])) {
        $data['billaddr'] = $data['shipaddr'];
      }

      $record->update($data);

      // Replace all details
      $record->details()->delete();
      foreach ($details as $detail) {
        $detail['sale_id'] = $record->id;
        SaleDetail::create($detail);
      }

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          $path = $file->store('attachments', 'public');
          \App\Models\Attachment::create([
            'reftype'    => 'SALE',
            'refid'      => $record->id,
            'bucket'     => 'public',
            'objkey'     => $path,
            'caption'    => $file->getClientOriginalName(),
            'created_by' => auth()->id() ?? 1,
          ]);
        }
      }

      if ($request->has('sync_attachments')) {
        $keptAttachments = $request->input('kept_attachments', []);
        $existingAttachments = \App\Models\Attachment::where('reftype', 'SALE')
          ->where('refid', $record->id)->get();
        foreach ($existingAttachments as $attachment) {
          if (!in_array($attachment->id, $keptAttachments)) {
            if ($attachment->objkey) Storage::disk('public')->delete($attachment->objkey);
            $attachment->delete();
          }
        }
      }

      app(StockPostingService::class)->postSale($record->load('details'));
      app(AccountingPostingService::class)->postSale($record->load('details'));

      DB::commit();

      return response()->json([
        'status'  => 'success',
        'message' => 'Sale updated successfully',
        'data'    => $record->load('details', 'attachments')
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status'  => 'error',
        'message' => 'Failed to update sale',
        'data'    => null,
        'error'   => $e->getMessage()
      ], 400);
    }
  }

  public function destroy($id)
  {
    $record = Sale::find($id);
    if (!$record) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Sale not found',
        'data'    => null
      ], 404);
    }

    if ($record->isvoid) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Sale is already voided',
        'data'    => null
      ], 400);
    }

    DB::transaction(function () use ($record) {
      app(AccountingPostingService::class)->reverseSource('SALE', $record->id, now()->toDateTimeString(), "Reverse {$record->trxno}");
      app(StockPostingService::class)->resetSource('SALE', $record->id);
      $record->isvoid = true;
      $record->save();
    });

    return response()->json([
      'status'  => 'success',
      'message' => 'Sale voided successfully',
      'data'    => $record
    ], 200);
  }

  /**
   * Get sales linked to a specific Sales Order.
   */
  public function bySalesOrder($soId)
  {
    $so = SalesOrder::find($soId);
    if (!$so) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Sales order not found',
        'data'    => null
      ], 404);
    }

    $sales = Sale::with(['details.item'])
      ->where('reftype', 'SO')
      ->where('refid', $soId)
      ->get();

    return response()->json([
      'status'  => 'success',
      'message' => 'Sales for sales order fetched successfully',
      'data'    => $sales
    ], 200);
  }
}
