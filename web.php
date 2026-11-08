<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchPhotoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnvironmentLogController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\GrowthLogController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\HarvestScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('search', SearchController::class)->name('search');

    Route::resource('batches', BatchController::class);
    Route::post('batches/{batch}/photos', [BatchPhotoController::class, 'store'])->name('batches.photos.store');
    Route::delete('batches/{batch}/photos/{photo}', [BatchPhotoController::class, 'destroy'])->name('batches.photos.destroy');

    Route::resource('feedings', FeedingController::class)->except('show');
    Route::resource('growth', GrowthLogController::class)->except('show');
    Route::resource('environment', EnvironmentLogController::class)->except('show');
    Route::resource('harvests', HarvestController::class)->except('show');

    Route::get('schedule', [HarvestScheduleController::class, 'index'])->name('schedule.index');
    Route::patch('schedule/{batch}', [HarvestScheduleController::class, 'update'])->name('schedule.update');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/excel', [ReportController::class, 'excel'])->name('reports.excel');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});
