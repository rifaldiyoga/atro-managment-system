<?php

use Illuminate\Support\Facades\Route;

// API routes for all resources will be registered here. 

Route::middleware('auth:sanctum')->group(function () {
  Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
  Route::apiResource('business-partners', App\Http\Controllers\Api\BusinessPartnerController::class);
  Route::apiResource('items', App\Http\Controllers\Api\ItemController::class);
  Route::apiResource('item-groups', App\Http\Controllers\Api\ItemGrpController::class);
  Route::apiResource('salesmen', App\Http\Controllers\Api\SalesmanController::class);
  Route::apiResource('salesmen-groups', App\Http\Controllers\Api\SalesmanGroupController::class);
  Route::apiResource('regs', App\Http\Controllers\Api\RegController::class);
  Route::get('regs/code/{code}', [App\Http\Controllers\Api\RegController::class, 'getByCode']);
  Route::apiResource('sales-orders', App\Http\Controllers\Api\SalesOrderController::class);
  Route::apiResource('purchases', App\Http\Controllers\Api\PurchaseController::class);
  Route::apiResource('purchase-orders', App\Http\Controllers\Api\PurchaseOrderController::class);
  Route::apiResource('purchase-receives', App\Http\Controllers\Api\PurchaseReceiveController::class);
  Route::apiResource('sales-quotations', App\Http\Controllers\Api\SalesQuotationController::class);
  Route::apiResource('deliveries', App\Http\Controllers\Api\DeliveryController::class);
  Route::get('sales-orders/{so}/deliveries', [App\Http\Controllers\Api\DeliveryController::class, 'bySalesOrder']);
  Route::apiResource('sales', App\Http\Controllers\Api\SaleController::class);
  Route::get('sales-orders/{so}/sales', [App\Http\Controllers\Api\SaleController::class, 'bySalesOrder']);
  Route::get('sales-quotation-report/{type}', [App\Http\Controllers\Api\SalesQuotationReportController::class, 'getReport']);
  Route::get('sales-order-report/{type}', [App\Http\Controllers\Api\SalesOrderReportController::class, 'getReport']);
  Route::get('sale-report/{type}', [App\Http\Controllers\Api\SaleReportController::class, 'getReport']);
  Route::get('delivery-report/{type}', [App\Http\Controllers\Api\DeliveryReportController::class, 'getReport']);
  Route::get('purchase-order-report/{type}', [App\Http\Controllers\Api\PurchaseOrderReportController::class, 'getReport']);
  Route::get('purchase-receive-report/{type}', [App\Http\Controllers\Api\PurchaseReceiveReportController::class, 'getReport']);
  Route::get('purchase-report/{type}', [App\Http\Controllers\Api\PurchaseReportController::class, 'getReport']);
  Route::get('dashboard/summary', [App\Http\Controllers\Api\DashboardController::class, 'summary']);
});
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
