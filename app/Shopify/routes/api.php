<?php
use Illuminate\Support\Facades\Route;
use App\Shopify\Constants\RouteConstant;
use App\Shopify\Http\Controllers\FulfillmentService\OrderNotificationController;
use App\Shopify\Http\Controllers\FulfillmentService\StockController;
use App\Shopify\Http\Controllers\FulfillmentService\TrackingController;
use App\Shopify\Http\Controllers\Fulfillment\CreateController;
use App\Shopify\Http\Controllers\Fulfillment\UpdateController;

Route::prefix('1.0/shopify')->group(function () {
    Route::prefix('fulfillment_service/{shop}/{warehouse_code}')->group(function () {
        Route::post('fulfillment_order_notification', OrderNotificationController::class)
            ->name(RouteConstant::FULFILLMENT_SERVICE_NOTIFY_ROUTE_NAME);
    });
    Route::prefix('fulfillment_service/{shop}/{warehouse_code}')
        ->middleware(['shopify'])
        ->group(function () {
            Route::get('')->name(RouteConstant::FULFILLMENT_SERVICE_ROUTE_NAME);
            Route::get('fetch_stock.{ext}', StockController::class)
                ->name(RouteConstant::FULFILLMENT_SERVICE_STOCK_ROUTE_NAME);
            Route::get('fetch_tracking_numbers.{ext}', TrackingController::class)
                ->name(RouteConstant::FULFILLMENT_SERVICE_TRACKING_ROUTE_NAME);
        });
});
