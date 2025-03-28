<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array
	{
		return [
			'email'    => ['required', 'email'],
			'password' => ['required', 'min:8'],
		];
	}

	public function attributes(): array
	{
		return [
			'email'    => 'メールアドレス',
			'password' => 'パスワード',
		];
	}

	public function messages(): array
	{
		return [
			'email.required'    => ':attributeを入力してください',
			'email.email'       => ':attributeは「ユーザー名@ドメイン」形式で入力してください',
			'password.required' => ':attributeを入力してください',
			'password.min' 		=> ':attributeは:min文字以上で入力してください',
		];
	}
}
