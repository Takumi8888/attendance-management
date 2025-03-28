<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
	public function index()
	{
		return view('Admin.Auth.login');
	}

	// 参照：https://labo.kon-ruri.co.jp/laravel9-multi-authentication/
	// 参照：https://www.larajapan.com/2020/10/03/withinput-witherrors-with/
	public function store(LoginRequest $request)
	{
		$credentials = $request->only(['email', 'password']);

		//ユーザー情報が見つかったらログイン
		if (Auth::guard('admins')->attempt($credentials)) {
			//ログイン後に表示するページにリダイレクト
			return redirect()->route('admin.attendance.index');
		}

		//ログインできなかったときに元のページに戻る
		return back()
		->withInput($request->except('password'))
		->withErrors(['email' => 'ログイン情報が登録されていません']);
	}

	public function logout(Request $request)
	{
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		// ログアウトしたらログインフォームにリダイレクト
		return redirect('/admin/login');
	}
}
