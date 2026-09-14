<?php

use Illuminate\Support\Facades\Route;

use Modules\AI\app\Http\Controllers\Api\ProductAutoFillController;

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

Route::group(['prefix' => 'v1', 'as' => 'v1.','middleware' => ['apiKeyAuth']], function () {
    Route::post('generate-title-and-description', [ProductAutoFillController::class, 'generateTitleAndDescription']);
    Route::post('generate-variation-data', [ProductAutoFillController::class, 'generateVariationData']);
    Route::post('generate-ingredients', [ProductAutoFillController::class, 'generateIngredients']);
    Route::post('generate-addons', [ProductAutoFillController::class, 'generateAddons']);
    Route::post('generate-specification', [ProductAutoFillController::class, 'generateSpecification']);
    Route::post('generate-image-data', [ProductAutoFillController::class, 'generateImageData']);
});
