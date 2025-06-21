<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::get('/', function () {
    return Auth::check()
        ? redirect('/admin')   // ถ้า login แล้ว ไป admin
        : redirect('/login');  // ถ้ายังไม่ login ไป login
});

Route::get('/admin/login', function () {
    return redirect('/login');
});

// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

Route::get('/admin/login', function () { //fake filament auth
    return redirect('/login');
})->name('filament.admin.auth.login');

Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::patch('/change-password', [PasswordController::class, 'update'])->name('password.update');
});

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/dashboard', function () {
            return view('dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
