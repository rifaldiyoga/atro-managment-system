<?php

namespace App\Services;

use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class MovingAverageCostService
{
  public function rebuildItem(int $itemId, ?int $warehouseId = null): StockBalance
  {
    $balance = StockBalance::firstOrCreate(
      ['item_id' => $itemId, 'wh_id' => $warehouseId],
      ['qty' => 0, 'avg_cost' => 0, 'total_cost' => 0]
    );

    $qty = 0.0;
    $avgCost = 0.0;
    $totalCost = 0.0;

    $query = StockMovement::where('item_id', $itemId)->where('wh_id', $warehouseId);
    $movements = $query->orderBy('trxdate')->orderBy('id')->get();

    foreach ($movements as $movement) {
      $qtyIn = (float) $movement->qty_in;
      $qtyOut = (float) $movement->qty_out;

      if ($qtyIn > 0) {
        $lineCost = $qtyIn * (float) $movement->unit_cost;
        $qty += $qtyIn;
        $totalCost += $lineCost;
        $avgCost = $qty > 0 ? $totalCost / $qty : 0;
        DB::table('stocklog')->where('id', $movement->id)->update(['total_cost' => $lineCost]);
      }

      if ($qtyOut > 0) {
        $lineCost = $qtyOut * $avgCost;
        $qty -= $qtyOut;
        $totalCost -= $lineCost;
        if ($qty <= 0) {
          $qty = 0;
          $totalCost = 0;
        }
        DB::table('stocklog')->where('id', $movement->id)->update([
          'unit_cost' => $avgCost,
          'total_cost' => $lineCost,
        ]);
      }
    }

    $balance->update([
      'qty' => $qty,
      'avg_cost' => $qty > 0 ? $avgCost : 0,
      'total_cost' => $totalCost,
    ]);

    return $balance->refresh();
  }
}
