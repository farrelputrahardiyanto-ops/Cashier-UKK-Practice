<?php

use App\Http\Middleware\Roles;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware(['auth', 'role:admin'])->group(function(){


    Route::livewire('/products', 'products')->name('product');

    Route::livewire('/users', 'users')->name('users');

    Route::livewire('/customers', 'customers')->name('customers');

   
});

Route::middleware(['auth', 'role:admin,cashier'])->group(function(){
     Route::livewire('/cashier', 'cashier')->name('cashier');
    Route::livewire('/cashier/{id?}', 'cashier')->name('cashier_edit');

     Route::livewire('/invoice', 'invoices')->name('invoice');

    Route::livewire('/invoice_detail', 'invoice_detail')->name('invoice_detail');
});

 Route::livewire('/login', 'login')->name('login');