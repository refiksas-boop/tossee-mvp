<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Listings routes
Route::get('/skelbimai', [ListingController::class, 'index'])->name('listings.index');
Route::get('/k/{category}', [ListingController::class, 'byCategory'])->name('listings.category');
Route::get('/k/{category}/{subcategory}', [ListingController::class, 'bySubcategory'])->name('listings.subcategory');
Route::get('/skelbimas/{id}-{slug}', [ListingController::class, 'show'])->name('listings.show');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/registracija', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/registracija', [RegisterController::class, 'register']);
    Route::get('/prisijungimas', [LoginController::class, 'showForm'])->name('login');
    Route::post('/prisijungimas', [LoginController::class, 'login']);
});

Route::post('/atsijungti', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// User dashboard routes
Route::middleware('auth')->prefix('mano-paskyra')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/skelbimai', [DashboardController::class, 'myListings'])->name('dashboard.listings');
    Route::get('/skelbimai/naujas', [DashboardController::class, 'createListing'])->name('dashboard.listings.create');
    Route::post('/skelbimai', [DashboardController::class, 'storeListing'])->name('dashboard.listings.store');
});
