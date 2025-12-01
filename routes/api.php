<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FishController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\MarketListingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationTypeController;
use App\Http\Controllers\Api\PondController;
use App\Http\Controllers\Api\PondSlotController;
use App\Http\Controllers\Api\PondSlotStatusController;
use App\Http\Controllers\Api\UserInventoryController;
use App\Http\Controllers\Api\UserMissionController;
use App\Http\Controllers\Api\ToolUsageController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TutorialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    Route::get('users/{user}/missions', [UserMissionController::class, 'index']);
    Route::post('users/{user}/missions/events', [UserMissionController::class, 'recordEvent']);
    Route::post('users/{user}/missions/{mission}/claim', [UserMissionController::class, 'claim']);

    Route::get('fish', [FishController::class, 'index']);
    Route::get('fish/{fish}', [FishController::class, 'show']);

    Route::get('inventory-items', [InventoryItemController::class, 'index']);

    Route::get('notification-types', [NotificationTypeController::class, 'index']);

    Route::get('market/listings/double-offer', [MarketListingController::class, 'showDoubleOffer']);
    Route::post('market/listings/double-offer/activate', [MarketListingController::class, 'activateDoubleOffer']);
    Route::post('market/listings/double-offer/deactivate', [MarketListingController::class, 'deactivateDoubleOffer']);

    Route::get('pond-slot-statuses', [PondSlotStatusController::class, 'index']);

    Route::get('ponds', [PondController::class, 'index']);
    Route::post('ponds', [PondController::class, 'store']);
    Route::get('ponds/{pond}', [PondController::class, 'show']);
    Route::put('ponds/{pond}', [PondController::class, 'update']);
    Route::patch('ponds/{pond}', [PondController::class, 'update']);
    Route::delete('ponds/{pond}', [PondController::class, 'destroy']);

    Route::get('ponds/{pond}/slots', [PondSlotController::class, 'index']);
    Route::post('ponds/{pond}/slots/{slot}/stock', [PondSlotController::class, 'stock']);
    Route::post('ponds/{pond}/slots/{slot}/feed', [PondSlotController::class, 'feed']);
    Route::post('ponds/{pond}/slots/{slot}/plant', [PondSlotController::class, 'plant']);
    Route::post('ponds/{pond}/slots/{slot}/supplement', [PondSlotController::class, 'supplement']);
    Route::post('ponds/{pond}/slots/{slot}/clean', [PondSlotController::class, 'clean']);
    Route::post('ponds/{pond}/slots/{slot}/advance', [PondSlotController::class, 'advance']);
    Route::post('ponds/{pond}/slots/{slot}/harvest', [PondSlotController::class, 'harvest']);
    Route::post('ponds/{pond}/slots/{slot}/mark-dead', [PondSlotController::class, 'markDead']);
    Route::post('ponds/{pond}/slots/{slot}/issues/{issue}', [PondSlotController::class, 'raiseIssue']);
    Route::post('ponds/{pond}/slots/{slot}/issues/{issue}/resolve', [PondSlotController::class, 'resolveIssue']);

    Route::get('users/{user}/wallet', [WalletController::class, 'show']);
    Route::patch('users/{user}/wallet', [WalletController::class, 'update']);
    Route::get('users/{user}/inventory', [UserInventoryController::class, 'index']);
    Route::post('users/{user}/inventory', [UserInventoryController::class, 'store']);
    Route::put('users/{user}/inventory/{item}', [UserInventoryController::class, 'update']);
    Route::get('users/{user}/notifications', [NotificationController::class, 'index']);
    Route::post('users/{user}/notifications', [NotificationController::class, 'store']);
    Route::post('users/{user}/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

    Route::get('users/{user}/tool-usage', [ToolUsageController::class, 'index']);
    Route::post('users/{user}/tool-usage', [ToolUsageController::class, 'store']);

    Route::get('stats', [StatsController::class, 'index']);

    Route::get('users/{user}/tutorial', [TutorialController::class, 'show']);
    Route::patch('users/{user}/tutorial', [TutorialController::class, 'update']);
});
