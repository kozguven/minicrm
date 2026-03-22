<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/today');

Route::middleware('auth')->group(function () {
    Route::get('/today', function () {
        return 'Today';
    });

    Route::resource('roles', RoleController::class)
        ->except(['show', 'destroy']);

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
