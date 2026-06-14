<?php

namespace App\Services;

use App\Models\PurchaseReceive;
use App\Models\Sale;
use App\Models\StockMovement;

class StockPostingService
{
  public function __construct(private MovingAverageCostService $costService)
  {
  }

  public function resetSource(string $sourceType, int $sourceId): void
  {
    $affected = StockMovement::where('source_type', $sourceType)
      ->where('source_id', $sourceId)
      ->get(['item_id', 'wh_id']);

    StockMovement::where('source_type', $sourceType)->where('source_id', $sourceId)->delete();

    foreach ($affected as $row) {
      $this->costService->rebuildItem((int) $row->item_id, $row->wh_id ? (int) $row->wh_id : null);
    }
  }

  public function record(array $data): StockMovement
  {
    $movement = StockMovement::create($data);
    $this->costService->rebuildItem((int) $movement->item_id, $movement->wh_id ? (int) $movement->wh_id : null);

    return $movement->refresh();
  }

  public function postSale(Sale $sale): void
  {
    $sale->loadMissing('details');
    $this->resetSource('SALE', $sale->id);

    foreach ($sale->details as $detail) {
      if (!$detail->item_id) continue;
      $qty = (float) ($detail->qtyx ?? $detail->qty ?? 0);
      if ($qty <= 0) continue;

      $this->record([
        'trxdate' => $sale->trxdate,
        'trxno' => $sale->trxno,
        'movement_type' => 'OUT',
        'source_type' => 'SALE',
        'source_id' => $sale->id,
        'source_detail_id' => $detail->id,
        'item_id' => $detail->item_id,
        'wh_id' => $detail->wh_id,
        'qty_in' => 0,
        'qty_out' => $qty,
        'unit_cost' => (float) ($detail->cost ?? 0),
        'total_cost' => 0,
        'note' => $sale->trxno,
      ]);
    }
  }

  public function postPurchaseReceive(PurchaseReceive $receive): void
  {
    $receive->loadMissing('details');
    $this->resetSource('PRCV', $receive->id);

    foreach ($receive->details as $detail) {
      if (!$detail->item_id) continue;
      $qty = (float) ($detail->qtyx ?? $detail->qty ?? 0);
      if ($qty <= 0) continue;

      $unitCost = (float) ($detail->cost ?? $detail->baseprice ?? 0);
      $this->record([
        'trxdate' => $receive->trxdate,
        'trxno' => $receive->trxno,
        'movement_type' => 'IN',
        'source_type' => 'PRCV',
        'source_id' => $receive->id,
        'source_detail_id' => $detail->id,
        'item_id' => $detail->item_id,
        'wh_id' => $detail->wh_id,
        'qty_in' => $qty,
        'qty_out' => 0,
        'unit_cost' => $unitCost,
        'total_cost' => $qty * $unitCost,
        'note' => $receive->trxno,
      ]);
    }
  }
}
