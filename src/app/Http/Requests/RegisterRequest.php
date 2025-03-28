<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
			'name'                  => ['required', 'string', 'max:50'],
			'email'                 => ['required', 'string', 'max:100', 'email'],
			'password'              => ['required', 'min:8', 'max:255', 'confirmed'],
			'password_confirmation' => ['required', 'min:8', 'max:255'],
		];
	}

	public function attributes(): array
	{
		return [
			'name'                  => 'お名前',
			'email'                 => 'メールアドレス',
			'password'              => 'パスワード',
			'password_confirmation' => '確認用パスワード',
		];
	}

	public function messages(): array
	{
		return [
			'name.required'                  => ':attributeを入力してください',
			'name.string'                    => ':attributeには文字を指定してください',
			'name.max'                       => ':attributeは:max文字以下で入力してください',
			'email.required'                 => ':attributeを入力してください',
			'email.string'                   => ':attributeには文字を指定してください',
			'email.max'                      => ':attributeは:max文字以下で入力してください',
			'email.email'                    => ':attributeは「ユーザー名@ドメイン」形式で入力してください',
			'password.required'              => ':attributeを入力してください',
			'password.min'                   => ':attributeは:min文字以上で入力してください',
			'password.max'                   => ':attributeは:max文字以下で入力してください',
			'password.confirmed'             => 'パスワードと一致しません',
			'password_confirmation.required' => ':attributeを入力してください',
			'password_confirmation.min'      => ':attributeは:min文字以上で入力してください',
			'password_confirmation.max'      => ':attributeは:max文字以下で入力してください',
		];
	}
}