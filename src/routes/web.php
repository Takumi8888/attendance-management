<?php

use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//管理画面・スタッフ画面用のファイル呼び出し
include __DIR__ . '/../routes/admin.php';
include __DIR__ . '/../routes/staff.php';

// メール認証
// 会員登録画面 → メール認証画面
Route::get('/email/verify', function () {
	return view('Staff.Auth.verify-email');
})->name('verification.notice');

// メール認証画面 → 勤怠登録画面
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
	$request->fulfill();
	session()->forget('unauthenticated_user');
	return redirect('/attendance');
})->name('verification.verify');

// 認証メール再送ボタン押下
Route::post('/email/verification-notification', function (Request $request) {
	session()->get('unauthenticated_user')->sendEmailVerificationNotification();
	session()->put('resent', true);
	return back()->with('message', 'Verification link sent!');
})->name('verification.send');