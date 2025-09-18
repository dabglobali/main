<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Dashboard route
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile edit route
    Route::get('/profile/edit', function () {
        return view('profile.edit');
    })->name('profile.edit');

    // Chat routes
    Route::get('/chat/{friendId}', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/{friendId}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{friendId}/fetch', [ChatController::class, 'fetchMessages'])->name('chat.fetch');
});
