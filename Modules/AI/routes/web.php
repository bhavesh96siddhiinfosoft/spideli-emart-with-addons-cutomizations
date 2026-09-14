<?php


use Illuminate\Support\Facades\Route;
use Modules\AI\app\Http\Controllers\ProductAutoFillController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['prefix' => 'ai', 'as' => 'ai.'], function () {
    Route::get('title-auto-fill', [ProductAutoFillController::class, 'titleAutoFill'])->name('title-auto-fill');
    Route::get('description-auto-fill', [ProductAutoFillController::class, 'descriptionAutoFill'])->name('description-auto-fill');
    Route::get('variation-setup-auto-fill', [ProductAutoFillController::class, 'variationSetupAutoFill'])->name('variation-setup-auto-fill');
    Route::post('analyze-image-auto-fill', [ProductAutoFillController::class, 'analyzeImageAutoFill'])->name('analyze-image-auto-fill');
    Route::post('generate-title-suggestions', [ProductAutoFillController::class, 'generateTitleSuggestions'])->name('generate-title-suggestions');
    Route::get('ingredients-auto-fill', [ProductAutoFillController::class, 'ingredientsAutoFill'])->name('ingredients-auto-fill');
    Route::get('addons-auto-fill', [ProductAutoFillController::class, 'addonsAutoFill'])->name('addons-auto-fill');
    Route::get('specification-auto-fill', [ProductAutoFillController::class, 'specificationAutoFill'])->name('specification-auto-fill');
});
