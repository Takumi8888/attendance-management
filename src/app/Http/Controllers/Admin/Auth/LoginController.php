<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
	public function create()
	{
		return view('Admin.Auth.login');
	}

	public function store(LoginRequest $request)
	{
		$credentials = $request->only(['email', 'password']);

		if (Auth::guard('admins')->attempt($credentials)) {
			return redirect()->route('admin.attendance.index');
		}

		return back()
		->withInput($request->except('password'))
		->withErrors(['email' => 'ログイン情報が登録されていません']);
	}

	public function destroy(Request $request)
	{
		Auth::guard('admins')->logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect('/login');
	}
}