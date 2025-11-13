<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//CATEGORIES
Route::get('/categories', [CategoriesController::class, 'listCategories']);
Route::get('/categories/{id}', [CategoriesController::class, 'getCategory']);
Route::post('/create/categories', [CategoriesController::class, 'createCategory']);
Route::post('/update/categories/{id}', [CategoriesController::class, 'updateCategory']);
