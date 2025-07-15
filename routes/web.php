<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;

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
    return view('app');
});

Route::post('/api/check-user', [UserController::class, 'checkUser']);
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);

Route::get('/api/progress-items', [ProgressController::class, 'getProgressItems']);
Route::post('/api/submit-progress', [ProgressController::class, 'submitProgress']);
Route::get('/api/today-progress', [ProgressController::class, 'getTodayProgress']);

Route::get('/api/target', [TargetController::class, 'getTarget']);
Route::post('/api/update-target', [TargetController::class, 'updateTarget']);

Route::get('/api/history/monthly', [HistoryController::class, 'getMonthlyData']);
Route::get('/api/history/detail', [HistoryController::class, 'getProgressDetail']);
Route::get('/api/history/months', [HistoryController::class, 'getAvailableMonths']);

Route::get('/api/reports/available', [ReportController::class, 'getAvailableReports']);
Route::get('/api/reports/monthly', [ReportController::class, 'getMonthlyReport']);
Route::get('/api/reports/download-monthly', [ReportController::class, 'downloadMonthlyReport']);
Route::get('/api/reports/download-daily', [ReportController::class, 'downloadDailyReport']);
Route::get('/api/reports/preview-daily', [ReportController::class, 'previewDailyReport']);
Route::get('/api/reports/preview-monthly', [ReportController::class, 'previewMonthlyReport']);

Route::get('/api/admin/users', [AdminController::class, 'getAllUsers']);
Route::get('/api/admin/stats', [AdminController::class, 'getUserStats']);
Route::post('/api/admin/update-user', [AdminController::class, 'updateUserAdmin']);
Route::delete('/api/admin/delete-user', [AdminController::class, 'deleteUser']);

Route::get('/test-cloudinary', function() {
    try {
        $result = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload('https://via.placeholder.com/150', [
            'folder' => 'test',
            'public_id' => 'test_image_' . time()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Cloudinary working',
            'url' => $result->getSecurePath()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
