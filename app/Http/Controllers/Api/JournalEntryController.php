<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Services\AccountingPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
  public function index(Request $request)
  {
    $perPage = $request->query('per_page', 10);
    $search = $request->query('search');
    $status = $request->query('status');
    $sort = $request->query('sort', 'trxdate');
    $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

    $query = JournalEntry::query();
    if ($search) {
      $query->where(fn ($q) => $q
        ->whereRaw('LOWER(trxno) LIKE ?', ['%' . strtolower($search) . '%'])
        ->orWhereRaw('LOWER(note) LIKE ?', ['%' . strtolower($search) . '%']));
    }
    if ($status) {
      match (strtoupper($status)) {
        'DRAFT' => $query->where('isdraft', true)->where('isvoid', false),
        'POSTED' => $query->where('isdraft', false)->where('isvoid', false),
        'VOID' => $query->where('isvoid', true),
        default => $query->where('status', strtoupper($status)),
      };
    }
    if (!in_array($sort, ['id', 'trxno', 'trxdate', 'status', 'total', 'created_at'])) $sort = 'trxdate';

    $records = $query->orderBy($sort, $direction)->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'message' => 'Journal entry list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
        'sort' => $sort,
        'direction' => $direction,
      ],
    ]);
  }

  public function store(Request $request)
  {
    return $this->saveJournal($request);
  }

  public function show($id)
  {
    $journal = JournalEntry::with('lines.account')->find($id);
    if (!$journal) return response()->json(['status' => 'error', 'message' => 'Journal entry not found', 'data' => null], 404);

    return response()->json(['status' => 'success', 'message' => 'Journal entry fetched successfully', 'data' => $journal]);
  }

  public function update(Request $request, $id)
  {
    $journal = JournalEntry::find($id);
    if (!$journal) return response()->json(['status' => 'error', 'message' => 'Journal entry not found', 'data' => null], 404);
    if ($journal->isPosted()) {
      return response()->json(['status' => 'error', 'message' => 'Posted journal cannot be updated', 'data' => null], 400);
    }

    return $this->saveJournal($request, $journal);
  }

  public function post($id)
  {
    $journal = JournalEntry::with('lines')->find($id);
    if (!$journal) return response()->json(['status' => 'error', 'message' => 'Journal entry not found', 'data' => null], 404);

    $debitTotal = round((float) $journal->lines->sum('debit'), 4);
    $creditTotal = round((float) $journal->lines->sum('credit'), 4);

    if ($journal->lines->count() < 2 || abs($debitTotal - $creditTotal) > 0.0001) {
      return response()->json(['status' => 'error', 'message' => 'Journal entry must balance before posting', 'data' => null], 400);
    }

    $journal->update([
      'isdraft' => false,
      'isvoid' => false,
      'status' => 'POSTED',
      'total' => $debitTotal,
    ]);

    return response()->json(['status' => 'success', 'message' => 'Journal entry posted successfully', 'data' => $journal->load('lines.account')]);
  }

  public function destroy($id)
  {
    $journal = JournalEntry::with('lines')->find($id);
    if (!$journal) return response()->json(['status' => 'error', 'message' => 'Journal entry not found', 'data' => null], 404);

    if ($journal->isPosted()) {
      app(AccountingPostingService::class)->reverseSource($journal->reftype ?? 'JNL', $journal->refid ?? $journal->id, now()->toDateTimeString(), "Reverse {$journal->trxno}");
      $journal->update(['isvoid' => true, 'status' => 'VOID']);
      return response()->json(['status' => 'success', 'message' => 'Journal entry reversed successfully', 'data' => $journal]);
    }

    $journal->delete();
    return response()->json(['status' => 'success', 'message' => 'Journal entry deleted successfully']);
  }

  private function saveJournal(Request $request, ?JournalEntry $journal = null)
  {
    $data = $request->validate([
      'trxno' => 'nullable|string|max:50',
      'trxdate' => 'required|date',
      'status' => 'nullable|in:DRAFT,POSTED',
      'reftype' => 'nullable|string|max:25',
      'refid' => 'nullable|integer',
      'trxtype' => 'nullable|string|max:25',
      'isautogen' => 'nullable|boolean',
      'note' => 'nullable|string',
      'lines' => 'required|array|min:2',
      'lines.*.acc_id' => 'required|integer|exists:acc,id',
      'lines.*.dnote' => 'nullable|string',
      'lines.*.debit' => 'nullable|numeric|min:0',
      'lines.*.credit' => 'nullable|numeric|min:0',
    ]);

    $lines = collect($data['lines'])->map(fn ($line) => [
      ...$line,
      'debit' => (float) ($line['debit'] ?? 0),
      'credit' => (float) ($line['credit'] ?? 0),
    ]);
    $debitTotal = round((float) $lines->sum('debit'), 4);
    $creditTotal = round((float) $lines->sum('credit'), 4);

    if (($data['status'] ?? 'DRAFT') === 'POSTED' && abs($debitTotal - $creditTotal) > 0.0001) {
      return response()->json(['status' => 'error', 'message' => 'Journal entry debit and credit must balance', 'data' => null], 400);
    }

    $journal = DB::transaction(function () use ($data, $lines, $debitTotal, $creditTotal, $journal) {
      $journal ??= new JournalEntry();
      $journal->fill([
        'trxno' => $data['trxno'] ?? $journal->trxno ?? 'AUTO',
        'trxdate' => $data['trxdate'],
        'reftype' => $data['reftype'] ?? $journal->reftype ?? 'JNL',
        'refid' => $data['refid'] ?? $journal->refid,
        'trxtype' => $data['trxtype'] ?? $journal->trxtype ?? 'JNL',
        'isdraft' => ($data['status'] ?? 'DRAFT') !== 'POSTED',
        'isvoid' => false,
        'status' => $data['status'] ?? 'DRAFT',
        'note' => $data['note'] ?? null,
        'total' => $debitTotal,
        'crc_id' => 1,
        'excrate' => 1,
        'fisrate' => 1,
        'isautogen' => (bool) ($data['isautogen'] ?? false),
        'created_by' => $journal->created_by ?? (auth()->id() ?? 1),
        'updated_by' => auth()->id() ?? 1,
      ]);
      $journal->save();
      if (!$journal->refid && $journal->reftype === 'JNL') {
        $journal->refid = $journal->id;
        $journal->save();
      }
      $journal->lines()->delete();
      foreach ($lines as $index => $line) {
        $journal->lines()->create([
          'dno' => $index + 1,
          'acc_id' => $line['acc_id'],
          'dk' => $line['debit'] > 0 ? 'D' : 'K',
          'debit' => $line['debit'],
          'credit' => $line['credit'],
          'amount' => max($line['debit'], $line['credit']),
          'amountforex' => max($line['debit'], $line['credit']),
          'dnote' => $line['dnote'] ?? null,
        ]);
      }

      return $journal->load('lines.account');
    });

    return response()->json(['status' => 'success', 'message' => 'Journal entry saved successfully', 'data' => $journal], $journal->wasRecentlyCreated ? 201 : 200);
  }
}
