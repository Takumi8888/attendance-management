<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
			'clock_in_time'  => ['required', 'before_or_equal:clock_out_time'],
			'clock_out_time' => ['required'],
			'start_time'     => ['required', 'array'],
			'start_time.*'   => ['after_or_equal:clock_in_time', 'before_or_equal:end_time.*'],
			'add_start_time' => ['after_or_equal:clock_in_time', 'before_or_equal:add_end_time', 'nullable'],
			'end_time'       => ['required', 'array'],
			'end_time.*'     => ['before_or_equal:clock_out_time'],
			'add_end_time'   => ['before_or_equal:clock_out_time', 'nullable'],
			'note'           => ['required', 'max:255'],
		];
	}

	public function attributes(): array
	{
		return [
			'clock_in_time'  => '出勤時間',
			'clock_out_time' => '退勤時間',
			'start_time'     => '休憩開始時間',
			'start_time.*'   => '休憩開始時間',
			'add_start_time' => '休憩開始時間',
			'end_time'       => '休憩終了時間',
			'end_time.*'     => '休憩終了時間',
			'add_end_time'   => '休憩終了時間',
			'note'           => '備考',
		];
	}

	public function messages(): array
	{
		return [
			'clock_in_time.required'         => ':attributeを入力してください',
			'clock_in_time.before_or_equal'  => '出勤時間もしくは退勤時間が不適切な値です',

			'clock_out_time.required'        => ':attributeを入力してください',

			'start_time.required'            => ':attributeを入力してください',
			'start_time.*.after_or_equal'    => '休憩時間が勤務時間外です',
			'start_time.*.before_or_equal'   => '休憩開始時間もしくは休憩終了時間が不適切な値です',
			'add_start_time.after_or_equal'  => '休憩時間が勤務時間外です',
			'add_start_time.before_or_equal' => '休憩開始時間もしくは休憩終了時間が不適切な値です',

			'end_time.required'              => ':attributeを入力してください',
			'end_time.*.before_or_equal'     => '休憩時間が勤務時間外です',
			'add_end_time.before_or_equal'   => '休憩時間が勤務時間外です',

			'note.required'                  => ':attributeを記入してください',
			'note.max'                       => ':attributeは:max文字以下で入力してください',
		];
	}
}
