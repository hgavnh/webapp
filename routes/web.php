<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

Route::get('/', [CustomerController::class, 'index'])->name('customer.order');
Route::get('/table/{table_id}', [CustomerController::class, 'index'])->name('customer.order.table');
Route::get('/tables', [CustomerController::class, 'tables_map'])->name('customer.tables');
Route::post('/api/create_order', [CustomerController::class, 'store']);
Route::post('/api/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
Route::post('/api/delete_order_item', [CustomerController::class, 'deleteItem'])->name('customer.delete_item');
Route::post('/api/update_order_item_qty', [CustomerController::class, 'updateItemQty'])->name('customer.update_item_qty');

Route::get('/orders/{order}/receipt', [CustomerController::class, 'receipt'])->name('order.receipt');

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('language.switch');
