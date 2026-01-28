<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CvApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




// 🔥 REVIEW ROUTE FIRST
Route::get('/cv/review/{filename}', [CvApiController::class, 'reviewCv'])
    ->where('filename', '.*');

// 🔥 PARSE ROUTE SECOND
Route::get('/cv/{filename}', [CvApiController::class, 'autoParseCv'])
    ->where('filename', '.*');
