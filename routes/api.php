<?php

use App\Http\Controllers\ApiLegacyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/legacy', [ApiLegacyController::class, 'handle']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
