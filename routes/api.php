<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register/customers', [AuthController::class, 'customerRegister']);
Route::post('login/customers', [AuthController::class, 'customerLogin']);
Route::post('login/admins', [AuthController::class, 'adminLogin']);
Route::post('register/admins', [AuthController::class, 'adminRegister']);


// Admin Routes
Route::middleware(['auth:sanctum', 'ensure.admin'])->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::post('products', [ProductController::class, 'store']);
});



//AuthRoutes
Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show'])->where('id', '[0-9]+');
    Route::post('carts', [CartController::class, 'store']);
    Route::delete('carts' , [CartController::class, 'destroy']);
    Route::get('products/search', [ProductController::class, 'getProducts']);
});


