<?php
/*
 * -----------------------------------
 * V1.0 APIs
 * -----------------------------------
 */
Route::prefix('1.0')->group(function () {
    Route::post('/orderaction', 'OrderAction\SaveController');
    Route::put('/orderaction', 'OrderAction\SaveController');
    Route::get('/orderaction/action', 'Action\ListController');
    Route::get('/orderaction/{id?}', 'OrderAction\ListController');
    Route::delete('/orderaction/{id}', 'OrderAction\DeleteController');
});
