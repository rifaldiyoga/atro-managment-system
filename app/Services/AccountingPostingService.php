<?php

namespace App\Services;

use App\Models\AccountingSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Purchase;
use App\Models\PurchaseReceive;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingPostingService
{
  public function accountId(string $key): int
  {
    $setting = AccountingSetting::where('key', $key)->first();
    if (!$setting || !$setting->value) {
      throw new InvalidArgumentException("Missing accounting setting: {$key}");
    }

    return (int) $setting->value;
  }

  public function createPostedEntry(string $sourceType, int $sourceId, string $trxdate, string $note, array $lines): JournalEntry
  {
    $cleanLines = collect($lines)
      ->map(fn ($line) => [
        'acc_id' => (int) $line['acc_id'],
        'dnote' => $line['dnote'] ?? $line['note'] ?? null,
        'debit' => round((float) ($line['debit'] ?? 0), 4),
        'credit' => round((float) ($line['credit'] ?? 0), 4),
      ])
      ->filter(fn ($line) => $line['debit'] > 0 || $line['credit'] > 0)
      ->values();

    if ($cleanLines->count() < 2) {
      throw new InvalidArgumentException('Journal entry needs at least two lines.');
    }

    $debitTotal = round((float) $cleanLines->sum('debit'), 4);
    $creditTotal = round((float) $cleanLines->sum('credit'), 4);

    if (abs($debitTotal - $creditTotal) > 0.0001) {
      throw new InvalidArgumentException('Journal entry debit and credit must balance.');
    }

    return DB::transaction(function () use ($sourceType, $sourceId, $trxdate, $note, $cleanLines, $debitTotal, $creditTotal) {
      $journal = JournalEntry::where('reftype', $sourceType)
        ->where('refid', $sourceId)
        ->where('trxtype', $sourceType)
        ->where('isvoid', false)
        ->first();

      if (!$journal) {
        $journal = new JournalEntry([
          'trxno' => 'AUTO',
          'reftype' => $sourceType,
          'refid' => $sourceId,
          'trxtype' => $sourceType,
        ]);
      }

      $journal->fill([
        'trxdate' => $trxdate,
        'reftype' => $sourceType,
        'refid' => $sourceId,
        'trxtype' => $sourceType,
        'isdraft' => false,
        'isvoid' => false,
        'status' => 'POSTED',
        'note' => $note,
        'total' => $debitTotal,
        'crc_id' => 1,
        'excrate' => 1,
        'fisrate' => 1,
        'isautogen' => true,
        'created_by' => auth()->id() ?? 1,
        'updated_by' => auth()->id() ?? 1,
      ]);
      $journal->save();

      $journal->lines()->delete();
      foreach ($cleanLines as $index => $line) {
        JournalEntryLine::create([
          'jnl_id' => $journal->id,
          'dno' => $index + 1,
          'acc_id' => $line['acc_id'],
          'dk' => $line['debit'] > 0 ? 'D' : 'K',
          'debit' => $line['debit'],
          'credit' => $line['credit'],
          'amount' => max($line['debit'], $line['credit']),
          'amountforex' => max($line['debit'], $line['credit']),
          'dnote' => $line['dnote'],
        ]);
      }

      return $journal->load('lines.account');
    });
  }

  public function postSale(Sale $sale): void
  {
    $sale->loadMissing('details');
    $inventoryValue = (float) $sale->details->sum(fn ($detail) => (float) ($detail->cost ?? 0) * (float) ($detail->qtyx ?? $detail->qty ?? 0));

    $lines = [
      ['acc_id' => $this->accountId('accounts_receivable_account_id'), 'debit' => (float) $sale->total, 'credit' => 0, 'note' => $sale->trxno],
      ['acc_id' => $this->accountId('sales_revenue_account_id'), 'debit' => 0, 'credit' => (float) $sale->subtotal, 'note' => $sale->trxno],
      ['acc_id' => $this->accountId('output_tax_account_id'), 'debit' => 0, 'credit' => (float) $sale->taxamt, 'note' => $sale->trxno],
      ['acc_id' => $this->accountId('sales_discount_account_id'), 'debit' => (float) $sale->discamt, 'credit' => 0, 'note' => $sale->trxno],
      ['acc_id' => $this->accountId('cogs_account_id'), 'debit' => $inventoryValue, 'credit' => 0, 'note' => $sale->trxno],
      ['acc_id' => $this->accountId('inventory_account_id'), 'debit' => 0, 'credit' => $inventoryValue, 'note' => $sale->trxno],
    ];

    $this->createPostedEntry('SALE', $sale->id, (string) $sale->trxdate, "Invoice penjualan {$sale->trxno}", $lines);
  }

  public function postPurchaseReceive(PurchaseReceive $receive): void
  {
    $receive->loadMissing('details');
    $inventoryValue = (float) $receive->details->sum(fn ($detail) => (float) ($detail->cost ?? $detail->baseprice ?? 0) * (float) ($detail->qtyx ?? $detail->qty ?? 0));

    $this->createPostedEntry('PRCV', $receive->id, (string) $receive->trxdate, "Penerimaan barang {$receive->trxno}", [
      ['acc_id' => $this->accountId('inventory_account_id'), 'debit' => $inventoryValue, 'credit' => 0, 'note' => $receive->trxno],
      ['acc_id' => $this->accountId('gr_clearance_account_id'), 'debit' => 0, 'credit' => $inventoryValue, 'note' => $receive->trxno],
    ]);
  }

  public function postPurchase(Purchase $purchase): void
  {
    $clearing = strtoupper((string) $purchase->reftype) === 'PRCV';
    $expenseAccount = $clearing ? 'gr_clearance_account_id' : 'purchase_account_id';

    $lines = [
      ['acc_id' => $this->accountId($expenseAccount), 'debit' => (float) $purchase->subtotal, 'credit' => 0, 'note' => $purchase->trxno],
      ['acc_id' => $this->accountId('input_tax_account_id'), 'debit' => (float) $purchase->taxamt, 'credit' => 0, 'note' => $purchase->trxno],
      ['acc_id' => $this->accountId('purchase_discount_account_id'), 'debit' => 0, 'credit' => (float) $purchase->discamt, 'note' => $purchase->trxno],
      ['acc_id' => $this->accountId('accounts_payable_account_id'), 'debit' => 0, 'credit' => (float) $purchase->total, 'note' => $purchase->trxno],
    ];

    $this->createPostedEntry('PURC', $purchase->id, (string) $purchase->trxdate, "Pembelian {$purchase->trxno}", $lines);
  }

  public function reverseSource(string $sourceType, int $sourceId, string $date, string $note): void
  {
    $journal = JournalEntry::with('lines')
      ->where('reftype', $sourceType)
      ->where('refid', $sourceId)
      ->where('isvoid', false)
      ->first();
    if (!$journal) return;

    $lines = $journal->lines->map(fn ($line) => [
      'acc_id' => $line->acc_id,
      'debit' => (float) $line->credit,
      'credit' => (float) $line->debit,
      'dnote' => $note,
    ])->all();

    $this->createPostedEntry("REV-{$sourceType}", $sourceId, $date, $note, $lines);
    $journal->update(['isvoid' => true, 'status' => 'VOID']);
  }
}
