<?php

use App\Http\Controllers\AdminShiftController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    Log::error('Debug Test');
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 可用性管理ルート（従業員用）
    Route::prefix('groups/{group}')->group(function () {
        Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
        Route::get('/availability/create', [AvailabilityController::class, 'create'])->name('availability.create');
        Route::post('/availability', [AvailabilityController::class, 'store'])->name('availability.store');
        Route::delete('/availability/{availability}', [AvailabilityController::class, 'destroy'])->name('availability.destroy');

        // 管理者用シフト管理ルート
        Route::get('/admin/shifts', [AdminShiftController::class, 'index'])->name('admin.shifts.index');
        Route::get('/admin/shifts/create', [AdminShiftController::class, 'create'])->name('admin.shifts.create');
        Route::post('/admin/shifts', [AdminShiftController::class, 'store'])->name('admin.shifts.store');
        Route::delete('/admin/shifts/{shift}', [AdminShiftController::class, 'destroy'])->name('admin.shifts.destroy');
        Route::get('/admin/availabilities', [AdminShiftController::class, 'availabilities'])->name('admin.availabilities.index');
        Route::post('/admin/search-users', [AdminShiftController::class, 'searchAvailableUsers'])->name('admin.search-users');
    });

    // グループチャット作成ルート（prefix外で定義）
    Route::post('/groups/{group}/chat', [ChatController::class, 'createGroupChat'])->name('chats.create-group');
});

require __DIR__.'/auth.php';

// チャット機能ルート（認証が必要）
Route::middleware(['auth'])->group(function () {
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chats.index');
        Route::get('/{chat}', [ChatController::class, 'show'])->name('chats.show');
        Route::post('/{chat}/messages', [ChatController::class, 'sendMessage'])->name('chats.send-message');
        Route::delete('/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
        Route::get('/search/users', [ChatController::class, 'searchUsers'])->name('chats.search-users');
    });
    
    // DM作成ルート
    Route::post('/chats/dm/{user}', [ChatController::class, 'createDM'])->name('chats.create-dm');
});

Route::resource('users', UserController::class)->only(['index', 'show']);
Route::get('/users/{user}/groups', [UserController::class, 'showGroups'])->name('users.groups');
