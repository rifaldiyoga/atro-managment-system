<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
  public function index()
  {
    $perPage = request()->query('per_page', 10);
    $search = request()->query('search');
    $sort = request()->query('sort', 'id');
    $direction = request()->query('direction', 'asc');
    $status = request()->query('status');
    $active = request()->query('active');

    $query = PurchaseOrder::with([
      'bp',
      'srep',
      'details:id,po_id,so_id',
      'details.salesOrder:id,trxno',
    ]);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->whereRaw('LOWER(trxno) LIKE ?', ['%' . strtolower($search) . '%']);
      });
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
      'status' => 'success',
      'message' => 'Purchase Order list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
        'sort' => $sort,
        'direction' => $direction,
      ]
    ], 200);
  }

  public function show($id)
  {
    $record = PurchaseOrder::with([
      'details.salesOrder:id,trxno',
      'bp',
      'srep',
      'attachments'
    ])->find($id);
    if (!$record) {
      return response()->json([
        'status' => 'error',
        'message' => 'Purchase Order not found',
        'data' => null
      ], 404);
    }

    return response()->json([
      'status' => 'success',
      'message' => 'Purchase Order fetched successfully',
      'data' => $record
    ], 200);
  }

  public function store(Request $request)
  {
    DB::beginTransaction();
    try {
      $inputData = $request->has('payload')
          ? json_decode($request->input('payload'), true)
          : $request->all();

      $data = collect($inputData)->except('details')->toArray();
      $data['created_by'] = auth()->id() ?? 1;
      $data['updated_by'] = auth()->id() ?? 1;
      if (!array_key_exists('version', $data)) {
        $data['version'] = 1;
      }
      if (empty($data['billaddr']) && isset($data['shipaddr'])) {
        $data['billaddr'] = $data['shipaddr'];
      }
      $details = $inputData['details'] ?? [];

      $record = PurchaseOrder::create($data);

      $dno = 1;
      foreach ($details as $detail) {
        $detail['po_id'] = $record->id;
        $detail['dno']   = $dno++;
        PurchaseOrderDetail::create($detail);
      }

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          $path = $file->store('attachments', 'public');
          \App\Models\Attachment::create([
            'reftype' => 'PO',
            'refid' => $record->id,
            'bucket' => 'public',
            'objkey' => $path,
            'caption' => $file->getClientOriginalName(),
            'created_by' => auth()->id() ?? 1,
          ]);
        }
      }

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Purchase Order created successfully',
        'data' => $record->load('details', 'attachments')
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create purchase order',
        'data' => null,
        'error' => $e->getMessage()
      ], 400);
    }
  }

  public function update(Request $request, $id)
  {
    $record = PurchaseOrder::find($id);
    if (!$record) {
      return response()->json([
        'status' => 'error',
        'message' => 'Purchase Order not found',
        'data' => null
      ], 404);
    }

    DB::beginTransaction();
    try {
      $inputData = $request->has('payload')
          ? json_decode($request->input('payload'), true)
          : $request->all();

      $data = collect($inputData)->except('details')->toArray();
      $data['updated_by'] = auth()->id() ?? 1;
      $data['version'] = $record->version + 1;
      if (empty($data['billaddr']) && isset($data['shipaddr'])) {
        $data['billaddr'] = $data['shipaddr'];
      }
      $details = $inputData['details'] ?? [];

      $record->update($data);

      $record->details()->delete();
      foreach ($details as $detail) {
        $detail['po_id'] = $record->id;
        PurchaseOrderDetail::create($detail);
      }

      if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
          $path = $file->store('attachments', 'public');
          \App\Models\Attachment::create([
            'reftype' => 'PO',
            'refid' => $record->id,
            'bucket' => 'public',
            'objkey' => $path,
            'caption' => $file->getClientOriginalName(),
            'created_by' => auth()->id() ?? 1,
          ]);
        }
      }

      if ($request->has('sync_attachments')) {
        $keptAttachments = $request->input('kept_attachments', []);
        $existingAttachments = \App\Models\Attachment::where('reftype', 'PO')
            ->where('refid', $record->id)
            ->get();

        foreach ($existingAttachments as $attachment) {
          if (!in_array($attachment->id, $keptAttachments)) {
            if ($attachment->objkey) {
              Storage::disk('public')->delete($attachment->objkey);
            }
            $attachment->delete();
          }
        }
      }

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => 'Purchase Order updated successfully',
        'data' => $record->load('details', 'attachments')
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update purchase order',
        'data' => null,
        'error' => $e->getMessage()
      ], 400);
    }
  }

  public function destroy($id)
  {
    $record = PurchaseOrder::find($id);
    if (!$record) {
      return response()->json([
        'status' => 'error',
        'message' => 'Purchase Order not found',
        'data' => null
      ], 404);
    }

    if ($record->isvoid) {
      return response()->json([
        'status' => 'error',
        'message' => 'Purchase Order is already voided',
        'data' => null
      ], 400);
    }

    $record->isvoid = true;
    $record->save();

    return response()->json([
      'status' => 'success',
      'message' => 'Purchase Order voided successfully',
      'data' => $record
    ], 200);
  }
}
