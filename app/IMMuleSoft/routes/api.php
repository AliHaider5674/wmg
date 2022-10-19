<?php

use App\IMMuleSoft\Constants\RouteConstant;
use App\IMMuleSoft\Http\Controllers\OrderStatusController;
use App\IMMuleSoft\Http\Controllers\ProductController;
use App\IMMuleSoft\Http\Controllers\ShipmentServicesController;
use App\IMMuleSoft\Http\Controllers\StockLevelController;
use Illuminate\Support\Facades\Route;
use App\IMMuleSoft\Http\Controllers\AsyncResponseController;

Route::prefix('1.0/ingram')->middleware('wmg.auth.basic')->group(function () {
    Route::post(RouteConstant::STOCK_LEVEL_URI, StockLevelController::class)
        ->name(RouteConstant::STOCK_LEVEL_NAME);

    Route::post(RouteConstant::ORDER_STATUS_URI, OrderStatusController::class)
        ->name(RouteConstant::ORDER_STATUS_NAME);

    Route::post(RouteConstant::ORDER_STATUS_URI, OrderStatusController::class)
        ->name(RouteConstant::ORDER_STATUS_NAME);

    Route::post(RouteConstant::SHIPPING_SERVICE_URI, ShipmentServicesController::class)
        ->name(RouteConstant::SHIPPING_SERVICE_NAME);

    Route::post(RouteConstant::PRODUCT_URI, ProductController::class)
        ->name(RouteConstant::PRODUCT_NAME);

    Route::post(RouteConstant::ASYNC_RESPONSE_URI, AsyncResponseController::class)
        ->name(RouteConstant::ASYNC_RESPONSE_NAME);
});
