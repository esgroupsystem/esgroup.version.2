<?php

use App\Http\Controllers\Payroll\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('payroll')->name('payroll.')->group(function (): void {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::get('/create', [PayrollController::class, 'create'])->name('create');
    Route::post('/', [PayrollController::class, 'store'])->name('store');

    Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
    Route::post('/{payroll}/finalize', [PayrollController::class, 'finalize'])->name('finalize');
    Route::delete('/{payroll}', [PayrollController::class, 'destroy'])->name('destroy');

    Route::get('/{payroll}/export/excel', [PayrollController::class, 'exportExcel'])->name('export.excel');
    Route::get('/{payroll}/export/pdf', [PayrollController::class, 'exportPdf'])->name('export.pdf');

    Route::get('/{payroll}/items/{item}', [PayrollController::class, 'showItem'])->name('items.show');
});
