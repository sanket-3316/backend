<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportImageController;

// category routes
Route::get('/categories', [CategoryController::class, 'get_all_categories']);
Route::get('/reports', [ReportController::class, 'reports']);
Route::get('/category-wise-reports', [ReportController::class, 'categoryReports']);
Route::get('/get-single-report', [ReportController::class, 'getSingleReport']);

// career routes
Route::get('/careers', [CareerController::class, 'get_all_careers']);

// lead capture (request sample / buy now modal)
Route::post('/report-sample', [LeadController::class, 'store']);

// runtime report chart images (SVG, served with a .webp filename)
Route::get('/report/{slug}/{filename}', [ReportImageController::class, 'render'])
    ->where('filename', '.*\.webp')
    ->where('slug', '[^/]+');

// runtime report card thumbnail (SVG, served with a .svg filename)
Route::get('/report/thumbnail/{filename}', [ReportImageController::class, 'renderCardThumbnail'])
    ->where('filename', '.*\.svg');
