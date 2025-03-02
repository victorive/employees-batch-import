<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;

Route::prefix('employee')->group(function () {
    Route::post('/', [EmployeeController::class, 'importEmployees']);
    Route::get('/{id}', [EmployeeController::class, 'getEmployee']);
    Route::delete('/{id}', [EmployeeController::class, 'deleteEmployee']);
});
