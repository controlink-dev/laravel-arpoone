<?php

use Controlink\LaravelArpoone\Http\Controllers\ArpooneEmailLogController;
use Controlink\LaravelArpoone\Http\Controllers\ArpooneSmsLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('arpoone')->group(function () {
    Route::post('webhook/sms/{status?}', [ArpooneSmsLogController::class, 'updateStatus'])->name('arpoone.webhook.sms.status');
    Route::post('webhook/email/{status?}', [ArpooneEmailLogController::class, 'updateStatus'])->name('arpoone.webhook.email.status');
});
