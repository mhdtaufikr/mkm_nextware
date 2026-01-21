
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ApiEndpointController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\InventoryReorderLevelController;





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

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::any('/auth/login', [AuthController::class, 'postLogin']);
Route::get('auth/microsoft', [AuthController::class, 'handleAzureCallback']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::post('request/access', [AuthController::class, 'requestAccess']);

Route::middleware(['auth'])->group(function () {
    // Handle password change
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');
    //Home Controller
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home/detail', [HomeController::class, 'getDetail'])->name('home.detail');

    //Dropdown Controller
    Route::get('/dropdown', [DropdownController::class, 'index']);
    Route::post('/dropdown/store', [DropdownController::class, 'store']);
    Route::patch('/dropdown/update/{id}', [DropdownController::class, 'update']);
    Route::delete('/dropdown/delete/{id}', [DropdownController::class, 'delete']);

    //Rules Controller
    Route::get('/rule', [RulesController::class, 'index']);
    Route::post('/rule/store', [RulesController::class, 'store']);
    Route::patch('/rule/update/{id}', [RulesController::class, 'update']);
    Route::delete('/rule/delete/{id}', [RulesController::class, 'delete']);

    //User Controller
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::post('/user/store', [UserController::class, 'store']);
    Route::post('/user/store-partner', [UserController::class, 'storePartner']);
    Route::patch('/user/update/{user}', [UserController::class, 'update']);
    Route::get('/user/revoke/{user}', [UserController::class, 'revoke']);
    Route::get('/user/access/{user}', [UserController::class, 'access']);

    Route::prefix('location')->name('location.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::post('/sync', [LocationController::class, 'sync'])->name('sync');
        Route::patch('/{location}/toggle-active', [LocationController::class, 'toggleActive'])->name('toggleActive');
    });

    Route::prefix('api-endpoint')->group(function () {
        Route::get('/', [ApiEndpointController::class, 'index'])->name('api-endpoint.index');
        Route::post('/', [ApiEndpointController::class, 'store'])->name('api-endpoint.store');
        Route::get('/{id}', [ApiEndpointController::class, 'show'])->name('api-endpoint.show');
        Route::put('/{id}', [ApiEndpointController::class, 'update'])->name('api-endpoint.update');
        Route::delete('/{id}', [ApiEndpointController::class, 'destroy'])->name('api-endpoint.destroy');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/sync', [InventoryController::class, 'sync'])->name('sync');
    });

    Route::prefix('order-items')->name('order-items.')->group(function () {
        Route::get('/', [OrderItemController::class, 'index'])->name('index');
        Route::post('/sync', [OrderItemController::class, 'sync'])->name('sync');
        Route::get('/{id}/detail', [OrderItemController::class, 'detail'])->name('detail');
    });

    Route::prefix('inventory-item')->group(function () {
        Route::get('/', [InventoryItemController::class, 'index'])->name('inventory-item.index');
        Route::post('/sync', [InventoryItemController::class, 'sync'])->name('inventory-item.sync');
        Route::get('/{id}', [InventoryItemController::class, 'show'])->name('inventory-item.show');
        Route::post('/', [InventoryItemController::class, 'store'])->name('inventory-item.store');
        Route::put('/{id}', [InventoryItemController::class, 'update'])->name('inventory-item.update');
        Route::delete('/{id}', [InventoryItemController::class, 'destroy'])->name('inventory-item.destroy');
    });

    // routes/web.php
    Route::prefix('planning')->group(function () {
    Route::get('/', [PlanningController::class, 'index'])->name('planning.index');
    Route::get('/meta', [PlanningController::class, 'meta'])->name('planning.meta');
    Route::get('/table', [PlanningController::class, 'table'])->name('planning.table');
    Route::post('/upsert', [PlanningController::class, 'upsert'])->name('planning.upsert');
    // ✅ New routes for template & import
    Route::get('/template/download', [PlanningController::class, 'downloadTemplate'])->name('planning.template.download');
    Route::post('/import', [PlanningController::class, 'import'])->name('planning.import');
});


    Route::prefix('inventory-reorder')
    ->name('inventory-reorder.')
    ->group(function () {

        Route::get('/', [InventoryReorderLevelController::class, 'index'])->name('index');
        Route::get('/create', [InventoryReorderLevelController::class, 'create'])->name('create');
        Route::post('/', [InventoryReorderLevelController::class, 'store'])->name('store');

        Route::get('/{id}/edit', [InventoryReorderLevelController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InventoryReorderLevelController::class, 'update'])->name('update');
        Route::post('/autosave', [InventoryReorderLevelController::class, 'autosave'])->name('autosave');
        Route::delete('/{id}', [InventoryReorderLevelController::class, 'destroy'])->name('destroy');
    });

});

