<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GalleryController;

// ── Splash & Auth ──
Route::get('/', [AuthController::class, 'splash'])->name('splash');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp'])->name('auth.send-otp');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');
Route::get('/auth/check', [AuthController::class, 'checkSession'])->name('auth.check');

// ── Protected Gallery ──
Route::middleware(['auth'])->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
    Route::post('/gallery/upload', [GalleryController::class, 'store'])->name('gallery.upload');
    Route::delete('/gallery/{photo}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
