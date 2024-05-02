<?php

use App\Http\Controllers\VimeoFoldersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vimeo/folders', [VimeoFoldersController::class, 'index'])->name('vimeo.folders');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/vimeo/folders', [VimeoFoldersController::class, 'index'])->name('vimeo.folders');
});
