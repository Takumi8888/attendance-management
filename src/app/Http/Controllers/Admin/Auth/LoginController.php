<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
	public function create()
	{
		return view('Admin.Auth.login');
	}

	// 参照：https://labo.kon-ruri.co.jp/laravel9-multi-authentication/
	// 参照：https://www.larajapan.com/2020/10/03/withinput-witherrors-with/
	public function store(LoginRequest $request)
	{
		$credentials = $request->only(['email', 'password']);

		// 管理者の未認証確認
		// dd(Auth::guard('admins')->check());

		//ユーザー情報が見つかったらログイン
		if (Auth::guard('admins')->attempt($credentials)) {
			// 管理者の認証確認
			// dd(Auth::guard('admins')->check());

			//ログイン後に表示するページにリダイレクト
			return redirect()->route('admin.attendance.index');
		}

		//ログインできなかったときに元のページに戻る
		return back()
		->withInput($request->except('password'))
		->withErrors(['email' => 'ログイン情報が登録されていません']);
	}

	public function destroy(Request $request)
	{
		// 管理者の認証確認
		// dd(Auth::guard('admins')->check());

		Auth::guard('admins')->logout();

		// 管理者の未認証確認
		// dd(Auth::guard('admins')->check());

		$request->session()->invalidate();
		$request->session()->regenerateToken();

		// ログアウトしたらログインフォームにリダイレクト
		return redirect('/login');
	}
}