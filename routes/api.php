<?php

use App\Http\Controllers\Api\PbxController;
use Illuminate\Support\Facades\Route;

Route::prefix('pbx')->controller(PbxController::class)->group(function () {
    Route::get('validate', 'validate');
    Route::post('call-end', 'callEnd');
});
