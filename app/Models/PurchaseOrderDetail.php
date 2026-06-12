<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
  protected $table = 'pod';

  protected $fillable = [
    'po_id',
    'so_id',
    'dno',
    'item_id',
    'wh_id',
    'itemname',
    'qty',
    'unit',
    'conv',
    'qtyx',
    'cost',
    'baseprice',
    'listprice',
    'discexp',
    'discamt',
    'totaldiscamt',
    'disc2amt',
    'totaldisc2amt',
    'subtotal',
    'basesubtotal',
    'dnote',
    'tax_code',
    'taxableamt',
    'taxamt',
    'totaltaxamt',
    'basetotaltaxamt',
    'basefistotaltaxamt',
    'basetotaltaxamt4',
    'basefistotaltaxamt4',
  ];

  protected $casts = [
    'dno' => 'integer',
    'so_id' => 'integer',
    'qty' => 'decimal:4',
    'conv' => 'decimal:4',
    'qtyx' => 'decimal:4',
    'cost' => 'decimal:4',
    'baseprice' => 'decimal:4',
    'listprice' => 'decimal:4',
    'discamt' => 'decimal:4',
    'totaldiscamt' => 'decimal:4',
    'disc2amt' => 'decimal:4',
    'totaldisc2amt' => 'decimal:4',
    'subtotal' => 'decimal:4',
    'basesubtotal' => 'decimal:4',
    'taxableamt' => 'decimal:4',
    'taxamt' => 'decimal:4',
    'totaltaxamt' => 'decimal:4',
    'basetotaltaxamt' => 'decimal:4',
    'basefistotaltaxamt' => 'decimal:4',
    'basetotaltaxamt4' => 'decimal:4',
    'basefistotaltaxamt4' => 'decimal:4',
  ];

  public function purchaseOrder()
  {
    return $this->belongsTo(PurchaseOrder::class, 'po_id', 'id');
  }

  public function salesOrder()
  {
    return $this->belongsTo(SalesOrder::class, 'so_id', 'id');
  }

}
