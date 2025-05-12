<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CorrectionRequestController;
use Illuminate\Support\Facades\Route;

// ログイン
Route::prefix('admin')->group(function () {
	Route::get('/login', [LoginController::class, 'create'])->name('admin.login.create');
	Route::post('/login', [LoginController::class, 'store'])->middleware('email')->name('admin.login.store');
});

// 管理者
Route::group(['prefix'=>'admin', 'middleware'=>'auth:admins'], function () {
	Route::post('/logout', [LoginController::class, 'destroy'])->name('admin.login.destroy');

	Route::prefix('attendance')->group(function () {
		// 勤怠一覧画面
		Route::get('/list', [AttendanceController::class, 'index'])->name('admin.attendance.index');
		Route::post('/list', [AttendanceController::class, 'paginationDate'])->name('admin.attendance.paginationDate');

		// スタッフ別勤怠一覧画面
		Route::get('/staff/{user}', [AttendanceController::class, 'staffAttendance'])->name('admin.attendance.staffAttendance');
		Route::post('/staff/{user}', [AttendanceController::class, 'paginationMonth'])->name('admin.attendance.paginationMonth');
		Route::post('/staff/{user}/export', [AttendanceController::class, 'export'])->name('admin.attendance.export');

		// 修正依頼画面
		Route::get('/{workTime}', [CorrectionRequestController::class, 'create'])->name('admin.correctionRequest.create');
		Route::put('/{workTime}', [CorrectionRequestController::class, 'store'])->name('admin.correctionRequest.store');
	});

	// スタッフ一覧画面
	Route::get('/staff/list', [AttendanceController::class, 'staff'])->name('admin.attendance.staff');

	// 申請一覧画面
	Route::prefix('stamp_correction_request')->group(function () {
		Route::get('/list', [CorrectionRequestController::class, 'pendingApproval'])->name('admin.correctionRequest.pendingApproval');
		Route::get('/list/approval', [CorrectionRequestController::class, 'approval'])->name('admin.correctionRequest.approval');
		Route::post('/list/approval', [CorrectionRequestController::class, 'approval'])->name('admin.correctionRequest.approval');

		Route::get('/approve/{workTime}', [CorrectionRequestController::class, 'edit'])->name('admin.correctionRequest.edit');
		Route::put('/approve/{workTime}', [CorrectionRequestController::class, 'update'])->name('admin.correctionRequest.update');
		Route::post('/approve/{workTime}', [CorrectionRequestController::class, 'approved'])->name('admin.correctionRequest.approved');
	});

});