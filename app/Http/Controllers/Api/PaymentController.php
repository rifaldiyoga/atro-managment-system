<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\AccountingPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
  public function index(Request $request)
  {
    $records = Payment::with('cashAccount')
      ->when($request->query('type'), fn ($q, $type) => $q->where('type', strtoupper($type)))
      ->orderBy('trxdate', 'desc')
      ->paginate($request->query('per_page', 10));

    return response()->json([
      'status' => 'success',
      'message' => 'Payment list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
      ],
    ]);
  }

  public function store(Request $request, AccountingPostingService $posting)
  {
    $data = $request->validate([
      'trxno' => 'nullable|string|max:50',
      'trxdate' => 'required|date',
      'type' => 'required|in:IN,OUT',
      'bp_id' => 'nullable|integer',
      'cash_acc_id' => 'required|integer|exists:acc,id',
      'source_type' => 'nullable|string|max:50',
      'source_id' => 'nullable|integer',
      'amount' => 'required|numeric|min:0.01',
      'note' => 'nullable|string',
      'details' => 'nullable|array',
    ]);

    $payment = DB::transaction(function () use ($data, $posting) {
      $payment = Payment::create([
        ...collect($data)->except('details')->toArray(),
        'trxno' => $data['trxno'] ?? 'AUTO',
        'status' => 'POSTED',
        'created_by' => auth()->id() ?? 1,
        'updated_by' => auth()->id() ?? 1,
      ]);

      $details = $data['details'] ?? [[
        'source_type' => $data['source_type'] ?? ($data['type'] === 'IN' ? 'SALE' : 'PURC'),
        'source_id' => $data['source_id'] ?? 0,
        'amount' => $data['amount'],
      ]];

      foreach ($details as $index => $detail) {
        $payment->details()->create([
          'dno' => $index + 1,
          'source_type' => $detail['source_type'],
          'source_id' => $detail['source_id'],
          'amount' => $detail['amount'],
          'note' => $detail['note'] ?? null,
        ]);
      }

      $lines = $payment->type === 'IN'
        ? [
          ['acc_id' => $payment->cash_acc_id, 'debit' => (float) $payment->amount, 'credit' => 0, 'note' => $payment->trxno],
          ['acc_id' => $posting->accountId('accounts_receivable_account_id'), 'debit' => 0, 'credit' => (float) $payment->amount, 'note' => $payment->trxno],
        ]
        : [
          ['acc_id' => $posting->accountId('accounts_payable_account_id'), 'debit' => (float) $payment->amount, 'credit' => 0, 'note' => $payment->trxno],
          ['acc_id' => $payment->cash_acc_id, 'debit' => 0, 'credit' => (float) $payment->amount, 'note' => $payment->trxno],
        ];

      $posting->createPostedEntry('PAY', $payment->id, (string) $payment->trxdate, "Pembayaran {$payment->trxno}", $lines);

      return $payment->load('details', 'cashAccount');
    });

    return response()->json(['status' => 'success', 'message' => 'Payment created successfully', 'data' => $payment], 201);
  }

  public function show($id)
  {
    $payment = Payment::with('details', 'cashAccount')->find($id);
    if (!$payment) return response()->json(['status' => 'error', 'message' => 'Payment not found', 'data' => null], 404);

    return response()->json(['status' => 'success', 'message' => 'Payment fetched successfully', 'data' => $payment]);
  }

  public function update(Request $request, $id)
  {
    return response()->json(['status' => 'error', 'message' => 'Posted payments cannot be updated', 'data' => null], 400);
  }

  public function destroy($id)
  {
    $payment = Payment::find($id);
    if (!$payment) return response()->json(['status' => 'error', 'message' => 'Payment not found', 'data' => null], 404);

    app(AccountingPostingService::class)->reverseSource('PAY', $payment->id, now()->toDateTimeString(), "Reverse {$payment->trxno}");
    $payment->status = 'VOID';
    $payment->save();

    return response()->json(['status' => 'success', 'message' => 'Payment voided successfully', 'data' => $payment]);
  }
}
