<?php

declare(strict_types=1);

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/api/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::post('/api/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
Route::post('/api/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/api/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::post('/api/tickets', [TicketController::class, 'store'])->name('tickets.store');
