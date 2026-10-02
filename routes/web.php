<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportKeywordController;
use App\Http\Controllers\ReportPriceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RedirectController;

// public
Route::get('/', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'loginPost']);

// protected
Route::middleware(['auth.check'])->group(function () {
    Route::post('/upload-image', [DashboardController::class, 'upload_editor_image']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/report-stats', [DashboardController::class, 'reportStats']);
    Route::get('/dashboard/lead-stats', [DashboardController::class, 'leadStats']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::post('/update', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });

    Route::prefix('users')->middleware(['admin.check'])->group(function () {
        Route::get('/dashboard', [UserController::class, 'index']);
        Route::post('/store', [UserController::class, 'store']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::post('/delete', [UserController::class, 'delete']);
        Route::get('/list', [UserController::class, 'ajaxList']);
    });

    Route::prefix('logs')->middleware(['admin.check'])->group(function () {
        Route::get('/', [LogController::class, 'index']);
        Route::get('/list', [LogController::class, 'ajaxList']);
        Route::post('/bulk-delete', [LogController::class, 'bulkDelete']);
    });

    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::get('/list', [LeadController::class, 'ajaxList']);
        Route::get('/export', [LeadController::class, 'exportCsv']);
        Route::get('/show/{id}', [LeadController::class, 'show']);
        Route::post('/store', [LeadController::class, 'adminStore']);
        Route::post('/update/{id}', [LeadController::class, 'update']);
        Route::post('/delete', [LeadController::class, 'delete']);
        Route::post('/bulk-delete', [LeadController::class, 'bulkDelete']);
        Route::post('/restore', [LeadController::class, 'restore']);
        Route::post('/bulk-restore', [LeadController::class, 'bulkRestore']);
    });

    Route::prefix('contact-messages')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index']);
        Route::get('/list', [ContactMessageController::class, 'ajaxList']);
        Route::get('/show/{id}', [ContactMessageController::class, 'show']);
        Route::post('/delete', [ContactMessageController::class, 'delete']);
        Route::post('/bulk-delete', [ContactMessageController::class, 'bulkDelete']);
        Route::post('/restore', [ContactMessageController::class, 'restore']);
        Route::post('/bulk-restore', [ContactMessageController::class, 'bulkRestore']);
    });
    // category
    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/list', [CategoryController::class, 'list']);
        Route::get('/english-list', [CategoryController::class, 'englishList']);
        Route::get('/delete-translation', [CategoryController::class, 'deleteTranslation']);
        // Route::get('/english-list', [CategoryController::class, 'englishList']);
        Route::get('/translations/{id}', [CategoryController::class, 'getTranslations']);
        Route::get('/all-translations/{id}', [CategoryController::class, 'getAllTranslations']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::post('/update/{id}', [CategoryController::class, 'update']);
        Route::post('/delete', [CategoryController::class, 'delete']);
    });

    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/list', [ReportController::class, 'getReports']);
        Route::post('store', [ReportController::class, 'store']);
        Route::post('/generate-from-keyword', [ReportController::class, 'generateFromKeyword']);
        Route::get('/edit/{id}', [ReportController::class, 'edit']);
        Route::post('/update/{id}', [ReportController::class, 'update']);
        Route::delete('/delete/{id}', [ReportController::class, 'destroy']);
        Route::post('/bulk-delete', [ReportController::class, 'bulkDestroy']);
        Route::get('/languages/{id}', [ReportController::class, 'getReportLanguages']);
        Route::delete('/deleteReport/{id}', [ReportController::class, 'deleteReport']);

        Route::get('/recycle-bin', [ReportController::class, 'recycleBin']);
        Route::get('/recycle-bin/list', [ReportController::class, 'recycleBinList']);
        Route::post('/restore/{id}', [ReportController::class, 'restore']);
        Route::post('/bulk-restore', [ReportController::class, 'bulkRestore']);
        Route::delete('/permanent-delete/{id}', [ReportController::class, 'permanentDestroy']);
        Route::post('/bulk-permanent-delete', [ReportController::class, 'bulkPermanentDestroy']);
    });

    Route::prefix('report-price')->group(function () {
        Route::get('/', [ReportPriceController::class, 'index']);
        Route::post('/apply', [ReportPriceController::class, 'applyToAll']);
    });

    Route::prefix('settings')->middleware(['admin.check'])->group(function () {
        Route::get('/api-key', [SettingController::class, 'apiKey']);
        Route::post('/api-key/update', [SettingController::class, 'updateApiKey']);
        Route::get('/contact-details', [SettingController::class, 'contactDetails']);
        Route::post('/contact-details/update', [SettingController::class, 'updateContactDetails']);
    });

    Route::prefix('redirect')->group(function () {
        Route::get('/', [RedirectController::class, 'index']);
        Route::get('/list', [RedirectController::class, 'ajaxList']);
        Route::post('/store', [RedirectController::class, 'store']);
        Route::post('/update/{id}', [RedirectController::class, 'update']);
        Route::post('/delete', [RedirectController::class, 'delete']);
    });

    Route::prefix('career')->group(function () {
        Route::get('/', [CareerController::class, 'index']);
        Route::get('/list', [CareerController::class, 'list']);
        Route::get('/show/{id}', [CareerController::class, 'show']);
        Route::post('/store', [CareerController::class, 'store']);
        Route::post('/update/{id}', [CareerController::class, 'update']);
        Route::post('/delete', [CareerController::class, 'delete']);
    });

    Route::prefix('keywords')->group(function () {
        Route::get('/', [ReportKeywordController::class, 'index']);
        Route::get('/list', [ReportKeywordController::class, 'ajaxList']);

        Route::post('/store', [ReportKeywordController::class, 'store']);
        Route::post('/update/{id}', [ReportKeywordController::class, 'update']);
        Route::post('/delete', [ReportKeywordController::class, 'delete']);
        Route::post('/bulk-delete', [ReportKeywordController::class, 'bulkDelete']);
        Route::post('/bulk-update-status', [ReportKeywordController::class, 'bulkUpdateStatus']);

        Route::get('/download-template', [ReportKeywordController::class, 'downloadTemplate']);
        Route::post('/import-csv', [ReportKeywordController::class, 'importCsv']);
    });

    Route::prefix('prompts')->group(function () {
        Route::get('/', [PromptController::class, 'index']);
        Route::post('/store', [PromptController::class, 'store']);
        Route::post('/update/{id}', [PromptController::class, 'update']);
        Route::post('/toggle-active/{id}', [PromptController::class, 'toggleActive']);
        Route::post('/delete', [PromptController::class, 'delete']);
    });
});



