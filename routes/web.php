<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarDownloadController;
use App\Http\Controllers\ImageUploadController;

Route::get('/', function () {
    return Auth::check()
        ? redirect('/admin')   // ถ้า login แล้ว ไป admin
        : redirect('/login');  // ถ้ายังไม่ login ไป login
});

Route::get('/admin/login', function () {
    return redirect('/login');
});

Route::get('/admin/login', function () { //fake filament auth
    return redirect('/login');
})->name('filament.admin.auth.login');

Route::get('/dashboard', function () {
            return view('dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/car/download/{car_no}', [CarDownloadController::class, 'download'])->name('car.download');

//Route::post('/upload', [ImageUploadController::class, 'upload'])->name('upload');

require __DIR__.'/auth.php';
