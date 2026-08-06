<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportImageController;

// category routes
Route::get('/categories', [CategoryController::class, 'get_all_categories']);
Route::get('/reports', [ReportController::class, 'reports']);
Route::get('/category-wise-reports', [ReportController::class, 'categoryReports']);
Route::get('/get-single-report', [ReportController::class, 'getSingleReport']);

// runtime report chart images (SVG, served with a .webp filename)
Route::get('/report/{slug}/{filename}', [ReportImageController::class, 'render'])
    ->where('filename', '.*\.webp')
    ->where('slug', '[^/]+');
