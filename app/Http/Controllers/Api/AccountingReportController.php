<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
  public function getReport(string $type, Request $request)
  {
    return match ($type) {
      'general-ledger' => $this->generalLedger($request),
      'trial-balance' => $this->trialBalance($request),
      default => response()->json(['status' => 'error', 'message' => 'Report not found', 'data' => null], 404),
    };
  }

  private function generalLedger(Request $request)
  {
    $query = JournalEntryLine::query()
      ->join('jnl', 'jnld.jnl_id', '=', 'jnl.id')
      ->join('acc', 'jnld.acc_id', '=', 'acc.id')
      ->where('jnl.isdraft', false)
      ->where('jnl.isvoid', false)
      ->select('jnl.trxdate', 'jnl.trxno', 'jnl.note as journal_note', 'acc.code as account_code', 'acc.name as account_name', 'jnld.dnote', 'jnld.debit', 'jnld.credit')
      ->orderBy('jnl.trxdate')
      ->orderBy('jnl.id')
      ->orderBy('jnld.dno');

    if ($request->query('account_id')) $query->where('jnld.acc_id', $request->query('account_id'));
    if ($request->query('date_from')) $query->whereDate('jnl.trxdate', '>=', $request->query('date_from'));
    if ($request->query('date_to')) $query->whereDate('jnl.trxdate', '<=', $request->query('date_to'));

    return response()->json(['status' => 'success', 'message' => 'General ledger fetched successfully', 'data' => $query->get()]);
  }

  private function trialBalance(Request $request)
  {
    $accounts = Account::query()
      ->leftJoin('jnld', 'acc.id', '=', 'jnld.acc_id')
      ->leftJoin('jnl', function ($join) use ($request) {
        $join->on('jnld.jnl_id', '=', 'jnl.id')
          ->where('jnl.isdraft', false)
          ->where('jnl.isvoid', false);
        if ($request->query('date_from')) $join->whereDate('jnl.trxdate', '>=', $request->query('date_from'));
        if ($request->query('date_to')) $join->whereDate('jnl.trxdate', '<=', $request->query('date_to'));
      })
      ->groupBy('acc.id', 'acc.code', 'acc.name', 'acc.type')
      ->orderBy('acc.code')
      ->selectRaw('acc.id, acc.code, acc.name, acc.type, COALESCE(SUM(CASE WHEN jnl.id IS NOT NULL THEN jnld.debit ELSE 0 END),0) as debit, COALESCE(SUM(CASE WHEN jnl.id IS NOT NULL THEN jnld.credit ELSE 0 END),0) as credit')
      ->get()
      ->map(function ($row) {
        $row->balance = round((float) $row->debit - (float) $row->credit, 4);
        return $row;
      });

    return response()->json([
      'status' => 'success',
      'message' => 'Trial balance fetched successfully',
      'data' => $accounts,
      'meta' => [
        'total_debit' => round((float) $accounts->sum('debit'), 4),
        'total_credit' => round((float) $accounts->sum('credit'), 4),
      ],
    ]);
  }
}
