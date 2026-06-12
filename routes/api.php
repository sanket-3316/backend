<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;

// category routes
Route::get('/categories', [CategoryController::class, 'get_all_categories']);
Route::get('/reports', [ReportController::class, 'reports']);
Route::get('/category-wise-reports', [ReportController::class, 'categoryReports']);
Route::get('/get-single-report', [ReportController::class, 'getSingleReport']);
