<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AttendanceRegisterTest extends TestCase
{

	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// // 勤怠登録画面表示（ステータス：勤務外）
	// public function test_attendance_Register_time()
	// {
	// 	$user = User::find(1);
	// 	$time = Carbon::now()->format('H:i');
	// 	// dd($time);

	// 	$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

	// 	$response->assertStatus(200);
	// 	$response->assertSee(strval($time));
	// }

	// 勤怠登録画面表示（ステータス：勤務外）
	public function test_attendance_Register_status_0()
	{
		$user = User::find(1);

		// status=0の状況を成立させるため、対象データを削除
		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

		$response->assertStatus(200);
		$response->assertViewHas('status', 0);
		$response->assertSee('勤務外');
	}

	// 勤怠登録画面表示（ステータス：出勤中）
	public function test_attendance_Register_status_1()
	{
		$user = User::find(1);

		// status=1の状況を成立させるため、対象データを削除、更新
		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		$workTime->update([
			'clock_out_time' => null,
			'work_time'      => null,
		]);

		$attendance->update([
			'total_break_time'     => null,
			'actual_working_hours' => null,
		]);

		// dd($workTime, $attendance);

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

		$response->assertStatus(200);
		$response->assertViewHas('status', 1);
		$response->assertSee('出勤中');
	}

	// 勤怠登録画面表示（ステータス：休憩中）
	public function test_attendance_Register_status_2()
	{
		$user = User::find(1);

		// status=2の状況を成立させるため、対象データを更新
		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		$workTime->update([
			'clock_out_time' => null,
			'work_time'      => null,
		]);

		$breakTime->update([
			'end_time'   => null,
			'break_time' => null,
		]);

		$attendance->update([
			'total_break_time'     => null,
			'actual_working_hours' => null,
		]);

		// dd($workTime, $breakTime, $attendance);

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

		$response->assertStatus(200);
		$response->assertViewHas('status', 2);
		$response->assertSee('休憩中');
	}

	// 勤怠登録画面表示（ステータス：退勤済）
	public function test_attendance_Register_status_3()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

		$response->assertStatus(200);
		$response->assertViewHas('status', 3);
		$response->assertSee('退勤済');
	}

	// 勤怠登録画面表示（ステータス：勤務外）
	public function test_attendance_Register_status_0_clockIn()
	{
		$user = User::find(1);

		// status=0の状況を成立させるため、対象データを削除
		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		// ステータスが勤務外のユーザーにログインする
		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 0);
		// 画面に「出勤」ボタンが表示されていることを確認する
		$response->assertSee('出勤');

		// 出勤の処理を行う
		$response = $this->actingAs($user)->post(route('attendanceRegister.clockIn'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 1);
		$response->assertSee('出勤中');
	}

	// 勤怠登録画面表示（ステータス：勤務外）
	public function test_attendance_Register_status_3_button()
	{
		$user = User::find(1);

		// 退勤済のユーザーがログインする
		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 3);
		// 出勤ボタンが表示されないことを確認する
		$response->assertDontSee('出勤');
	}

	// 勤怠登録画面表示（ステータス：勤務外）
	public function test_attendance_Register_status_0_list()
	{
		$user = User::find(1);

		// status=0の状況を成立させるため、対象データを削除
		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$work_day = strtotime($today);
		$array = ["日", "月", "火", "水", "木", "金", "土"];
		$workDate = date('m / d', $work_day) . " (" . $array[date("w", $work_day)] . ")";

		// 勤務外ユーザーがログイン
		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 0);

		// 出勤処理
		$response = $this->actingAs($user)->post(route('attendanceRegister.clockIn'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 1);

		// 管理画面から出勤の日付を確認する
		$response = $this->actingAs($user)->get(route('attendance.index'));
		$response->assertSee($workDate);
	}

	// // 勤怠登録画面表示（ステータス：出勤中）
	// public function test_attendance_Register_status_1()
	// {
	// 	$user = User::find(1);

	// 	// status=1の状況を成立させるため、対象データを削除、更新
	// 	$today = Carbon::now()->format('Y-m-d');
	// 	$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', $today)->first();
	// 	BreakTime::where('work_time_id', $workTime->id)->first()->delete();
	// 	$attendance = Attendance::where('work_time_id', $workTime->id)->first();

	// 	$workTime->update([
	// 		'clock_out_time' => null,
	// 		'work_time'      => null,
	// 	]);

	// 	$attendance->update([
	// 		'total_break_time'     => null,
	// 		'actual_working_hours' => null,
	// 	]);

	// 	// 出勤中のユーザーがログインする
	// 	$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
	// 	$response->assertStatus(200);
	// 	$response->assertViewHas('status', 1);
	// 	$response->assertSee('休憩入');

	// 	// 出勤処理
	// 	// $this->post(route('attendanceRegister.breakTimeStart'));
	// 	// $response->assertStatus(200);
	// 	// $response->assertViewHas('status', 2);

	// 	$this->assertDatabaseHas('break_times', [
	// 		'work_time_id' => $workTime->id,
	// 		'start_time'   => Carbon::now(),
	// 		'end_time'     => null,
	// 		'break_time'   => null,
	// 		'created_at'   => Carbon::now(),
	// 		'updated_at'   => Carbon::now(),
	// 	]);
	// }

}
