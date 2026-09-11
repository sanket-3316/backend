<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportKeywordController;
use App\Http\Controllers\ReportPriceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RedirectController;

// public
Route::get('/', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'loginPost']);

// protected
Route::middleware(['auth.check'])->group(function () {
    Route::post('/upload-image', [DashboardController::class, 'upload_editor_image']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::prefix('users')->middleware(['admin.check'])->group(function () {
        Route::get('/dashboard', [UserController::class, 'index']);
        Route::post('/store', [UserController::class, 'store']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::post('/delete', [UserController::class, 'delete']);
        Route::get('/list', [UserController::class, 'ajaxList']);
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
        Route::get('/languages/{id}', [ReportController::class, 'getReportLanguages']);
        Route::delete('/deleteReport/{id}', [ReportController::class, 'deleteReport']);
    });

    Route::prefix('report-price')->group(function () {
        Route::get('/', [ReportPriceController::class, 'index']);
        Route::post('/apply', [ReportPriceController::class, 'applyToAll']);
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

        Route::get('/download-template', [ReportKeywordController::class, 'downloadTemplate']);
        Route::post('/import-csv', [ReportKeywordController::class, 'importCsv']);
    });
});



// Route::get('/welcome', function () {
//     return view('welcome');
// });
