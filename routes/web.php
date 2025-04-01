<?php

use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoice/{invoice}/order-pdf', [PDFController::class, 'InvoicePdf'])->name('invoice.pdf');
Route::get('/production/{production}/production-detail', [PDFController::class, 'ProductionDetail'])->name('ProductionDetail.pdf');


