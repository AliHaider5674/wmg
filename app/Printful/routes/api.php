<?php

\Illuminate\Support\Facades\Route::post(
    'printful/webhook/{key}',
    \App\Printful\Http\Controllers\PrintfulWebhookController::class
)->name(\App\Printful\Constants\ConfigConstant::WEBHOOK_ROUTE_NAME)
->middleware([\App\Printful\Http\Middleware\PrintfulWebhookAuthMiddleware::class]);
