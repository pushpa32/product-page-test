<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Authenticated with sanctum
Route::middleware('auth.sanctum')->group(function () {
    // product
    Route::post('/addProducts', [ProductController::class, 'addProduct']);
    Route::post('/deleteProduct', [ProductController::class, 'deleteProduct']);
    Route::get('/get/all/products', [ProductController::class, 'getAllProduct']);
    Route::get('/get/all/products/{id}', [ProductController::class, 'getAllProductById']);
    Route::get('/update/product/{id}/status/{status}', [ProductController::class, 'updateProductStatus']);

    // images
    Route::post('/add/product/images/{id}', [ProductController::class, 'addProductImages']);
    Route::get('/get/all/images', [ProductController::class, 'getAllProductImages']);
    Route::post('/deleteImage', [ProductController::class, 'deleteImage']);

    // discount
    Route::post('/add/product/discount', [ProductController::class, 'addProductDiscount']);
    Route::get('/get/all/discount', [ProductController::class, 'getAllProductDiscount']);
    Route::post('/deleteDiscount', [ProductController::class, 'deleteDiscount']);


    // main api to get all details by id
    Route::get('/get/product/info/{id}', [ProductController::class, 'getProductInfo']);
    //get all active product
    Route::get('/get/all-active-product', [ProductController::class, 'getAllActiveProduct']);


    // only get the active product info by id
    Route::get('/get/active/product/info/{id}', [ProductController::class, 'getProductInfoIfActive']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
