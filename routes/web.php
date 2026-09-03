<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamilyDocumentController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('transactions.index');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Family Members & Documents
    Route::resource('family-members', FamilyMemberController::class)->except(['show']);
    Route::get('/family-members/{familyMember}/download-image/{type}', [FamilyMemberController::class, 'downloadImage'])
        ->name('family-members.download-image');
    Route::post('/family-members/{familyMember}/documents', [FamilyDocumentController::class, 'store'])
        ->name('family-documents.store');
    Route::get('/family-documents/{document}/download', [FamilyDocumentController::class, 'download'])
        ->name('family-documents.download');
    Route::delete('/family-documents/{document}', [FamilyDocumentController::class, 'destroy'])
        ->name('family-documents.destroy');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    });
});
