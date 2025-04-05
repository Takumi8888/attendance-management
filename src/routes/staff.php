<?php

use App\Http\Controllers\Staff\Auth\LoginController;
use App\Http\Controllers\Staff\Auth\RegisteredUserController;
use App\Http\Controllers\Staff\AttendanceController;
use App\Http\Controllers\Staff\AttendanceRegisterController;
use App\Http\Controllers\Staff\CorrectionRequestController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

// 認証画面
Route::post('/register', [RegisteredUserController::class, 'store'])->name('registeredUser.store');
Route::get('/', [LoginController::class, 'create'])->name('login.create');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('email')->name('login.store');

// ログイン後
Route::middleware(['auth', 'verified'])->group(function () {
	// ログアウト処理
	Route::post('/logout', [LoginController::class, 'destroy'])->name('login.destroy');

	Route::prefix('attendance')->group(function () {
		// 勤怠登録画面
		Route::get('/', [AttendanceRegisterController::class, 'index'])->name('attendanceRegister.index');
		Route::post('/workTime', [AttendanceRegisterController::class, 'clockIn'])->name('attendanceRegister.clockIn');
		Route::put('/workTime', [AttendanceRegisterController::class, 'clockOut'])->name('attendanceRegister.clockOut');
		Route::post('/breakTime', [AttendanceRegisterController::class, 'breakTimeStart'])->name('attendanceRegister.breakTimeStart');
		Route::put('/breakTime', [AttendanceRegisterController::class, 'breakTimeEnd'])->name('attendanceRegister.breakTimeEnd');

		// 勤怠一覧画面
		Route::get('/list', [AttendanceController::class, 'index'])->name('attendance.index');
		Route::post('/list', [AttendanceController::class, 'paginationMonth'])->name('attendance.paginationMonth');

		// 修正依頼画面
		Route::get('/{workTime}', [CorrectionRequestController::class, 'create'])->name('correctionRequest.create');
		Route::post('/{workTime}', [CorrectionRequestController::class, 'store'])->name('correctionRequest.store');
	});

	// 申請一覧画面
	Route::prefix('stamp_correction_request')->group(function () {
		Route::get('/list', [CorrectionRequestController::class, 'pendingApproval'])->name('correctionRequest.pendingApproval');
		Route::post('/list', [CorrectionRequestController::class, 'approval'])->name('correctionRequest.approval');
	});
});