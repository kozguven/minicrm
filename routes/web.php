<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/today');

Route::middleware('auth')->group(function () {
    Route::get('/today', function () {
        return 'Today';
    });

    Route::resource('roles', RoleController::class)
        ->except(['show', 'destroy']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::get('/team/create', [TeamController::class, 'create']);
    Route::post('/team', [TeamController::class, 'store']);

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
