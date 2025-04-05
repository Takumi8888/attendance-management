<?php

namespace Tests\Feature\Staff;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceRegisterTest extends TestCase
{

	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.画面上に表示されている日時が現在の日時と一致する
	public function test_attendance_register_4_1()
	{
		$user = User::find(1);

		$work_day = Carbon::now()->format('Y-m-d');
		$year = intval(substr($work_day, 0, 4));
		$month = intval(substr($work_day, 5, 2));
		$date = intval(substr($work_day, 8, 2));
		$work_week = strtotime($work_day);
		$array = ["日", "月", "火", "水", "木", "金", "土"];
		$date = date($year . '年' . $month . '月' . $date) . "日(" . $array[date("w", $work_week)] . ")";

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'date',
		]);
	}

	// 2.画面上に表示されているステータスが「勤務外」となる
	public function test_attendance_register_5_1()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 0);
		$response->assertSeeInOrder([
			'勤務外',
			'出勤',
		]);
	}

	// 3.画面上に表示されているステータスが「勤務中」となる
	public function test_attendance_register_5_2()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
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

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 1);
		$response->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);
	}

	// 4.画面上に表示されているステータスが「休憩中」となる
	public function test_attendance_register_5_3()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
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

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 2);
		$response->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);
	}

	// 5.画面上に表示されているステータスが「退勤済」となる
	public function test_attendance_register_5_4()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 3);
		$response->assertSeeInOrder([
			'退勤済',
			'お疲れ様でした。',
		]);
	}

	// 6.画面上に「出勤」ボタンが表示され、処理後に画面上に表示されるステータスが「勤務中」になる
	public function test_attendance_register_6_1()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 0);
		$response1->assertSeeInOrder([
			'勤務外',
			'出勤',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.clockIn'));
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 1);
		$response2->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('work_times', [
			'user_id'       => $user->id,
			'clock_in_time' => Carbon::now()->format('Y-m-d H:i:s'),
		]);
	}

	// 7.画面上に「出勤」ボタンが表示されない
	public function test_attendance_register_6_2()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response->assertStatus(200);
		$response->assertViewHas('status', 3);
		$response->assertDontSee('出勤');
		$response->assertSeeInOrder([
			'退勤済',
			'お疲れ様でした。',
		]);
	}

	// 8.管理画面に出勤時刻が正確に記録されている
	public function test_attendance_register_6_3()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$work_day = strtotime($workTime->clock_in_time);
		$array = ["日", "月", "火", "水", "木", "金", "土"];
		$workDate = date('m / d', $work_day) . " (" . $array[date("w", $work_day)] . ")";

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 0);
		$response1->assertSeeInOrder([
			'勤務外',
			'出勤',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.clockIn'));
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 1);
		$response2->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('work_times', [
			'user_id'       => $user->id,
			'clock_in_time' => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->get(route('attendance.index'));
		$response3->assertSeeInOrder([
			'勤怠一覧',
			$workDate,
			Carbon::now()->format('H:i'),
			'詳細',
		]);
	}

	// 9.画面上に「休憩入」ボタンが表示され、処理後に画面上に表示されるステータスが「休憩中」になる
	public function test_attendance_register_7_1()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
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

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'),[
			$workTime,
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 2);
		$response2->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);
	}

	// 10.画面上に「休憩入」ボタンが表示される
	public function test_attendance_register_7_2()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$new_breakTime_id = count(BreakTime::all()) + 1;
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

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'),[
			$workTime,
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 2);
		$response2->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->put(route('attendanceRegister.breakTimeEnd'), [
			$workTime,
			'id' => $new_breakTime_id,
		]);
		$response3->assertStatus(200);
		$response3->assertViewHas('status', 1);
		$response3->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
			'end_time'     => Carbon::now()->format('Y-m-d H:i:s'),
			'break_time'   => '00:00:00',
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'     => $workTime->id,
			'work_day'         => $attendance->work_day,
			'total_break_time' => '00:00:00',
		]);
	}

	// 11.休憩戻ボタンが表示され、処理後にステータスが「出勤中」に変更される
	public function test_attendance_register_7_3()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$new_breakTime_id = count(BreakTime::all()) + 1;
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

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'), [
			$workTime,
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 2);
		$response2->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->put(route('attendanceRegister.breakTimeEnd'), [
			$workTime,
			'id' => $new_breakTime_id,
		]);
		$response3->assertStatus(200);
		$response3->assertViewHas('status', 1);
		$response3->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
			'end_time'     => Carbon::now()->format('Y-m-d H:i:s'),
			'break_time'   => '00:00:00',
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'     => $workTime->id,
			'work_day'         => $attendance->work_day,
			'total_break_time' => '00:00:00',
		]);
	}

	// 12.画面上に「休憩戻」ボタンが表示される
	public function test_attendance_register_7_4()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$new_breakTime_id = count(BreakTime::all()) + 1;
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

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'), [
			$workTime,
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 2);
		$response2->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->put(route('attendanceRegister.breakTimeEnd'), [
			$workTime,
			'id' => $new_breakTime_id,
		]);
		$response3->assertStatus(200);
		$response3->assertViewHas('status', 1);
		$response3->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
			'end_time'     => Carbon::now()->format('Y-m-d H:i:s'),
			'break_time'   => '00:00:00',
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'     => $workTime->id,
			'work_day'         => $attendance->work_day,
			'total_break_time' => '00:00:00',
		]);

		$response4 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'), [
			$workTime,
			'id' => $workTime->id,
		]);
		$response4->assertStatus(200);
		$response4->assertViewHas('status', 2);
		$response4->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);
	}

	// 13.管理画面に休憩時刻が正確に記録されている
	public function test_attendance_register_7_5()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$new_breakTime_id = count(BreakTime::all()) + 1;
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		$work_day = strtotime($workTime->clock_in_time);
		$array = ["日", "月", "火", "水", "木", "金", "土"];
		$workDate = date('m / d', $work_day) . " (" . $array[date("w", $work_day)] . ")";

		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$workTime->update([
			'clock_out_time' => null,
			'work_time'      => null,
		]);

		$attendance->update([
			'total_break_time'     => null,
			'actual_working_hours' => null,
		]);

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.breakTimeStart'), [
			$workTime,
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 2);
		$response2->assertSeeInOrder([
			'休憩中',
			'休憩戻',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->put(route('attendanceRegister.breakTimeEnd'), [
			$workTime,
			'id' => $new_breakTime_id,
		]);
		$response3->assertStatus(200);
		$response3->assertViewHas('status', 1);
		$response3->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => Carbon::now()->format('Y-m-d H:i:s'),
			'end_time'     => Carbon::now()->format('Y-m-d H:i:s'),
			'break_time'   => '00:00:00',
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'     => $workTime->id,
			'work_day'         => $attendance->work_day,
			'total_break_time' => '00:00:00',
		]);

		$response4 = $this->actingAs($user)->get(route('attendance.index'));
		$response4->assertStatus(200);
		$response4->assertSeeInOrder([
			'勤怠一覧',
			$workDate,
			$clock_in_time,
			'00:00',
			'詳細',
		]);
	}

	// 14.画面上に「退勤」ボタンが表示され、処理後に画面上に表示されるステータスが「退勤済」になる
	public function test_attendance_register_8_1()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$attendance = Attendance::where('work_time_id', $workTime->id)->first();

		$workTime->update([
			'clock_out_time' => null,
			'work_time'      => null,
		]);

		$attendance->update([
			'actual_working_hours' => null,
		]);

		$clock_in_time = $workTime->clock_in_time;
		$clock_out_time = Carbon::now()->format('Y-m-d H:i:s');
		$work_time_difference = strtotime($clock_out_time) - strtotime($clock_in_time);
		$work_hours = floor($work_time_difference / 3600);
		$work_minutes = floor(($work_time_difference % 3600) / 60);
		$work_seconds = $work_time_difference % 60;
		$work_time = date(sprintf('%02d', $work_hours) . ':' . sprintf('%02d', $work_minutes) . ':' . sprintf('%02d', $work_seconds));

		$break_time_difference = strtotime($breakTime->end_time) - strtotime($breakTime->start_time);

		$actual_time_difference = $work_time_difference - $break_time_difference;
		$actual_hours = floor($actual_time_difference / 3600);
		$actual_minutes = floor(($actual_time_difference % 3600) / 60);
		$actual_seconds = $actual_time_difference % 60;
		$actual_workTime = date(sprintf('%02d', $actual_hours) . ':' . sprintf('%02d', $actual_minutes) . ':' . sprintf('%02d', $actual_seconds));

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 1);
		$response1->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$response2 = $this->actingAs($user)->put(route('attendanceRegister.clockOut'), [
			'id' => $workTime->id,
		]);
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 3);
		$response2->assertSeeInOrder([
			'退勤済',
			'お疲れ様でした。',
		]);

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $workTime->id,
			'work_day'             => $attendance->work_day,
			'total_break_time'     => $breakTime->break_time,
			'actual_working_hours' => $actual_workTime,
		]);
	}

	// 15.管理画面に退勤時刻が正確に記録されている
	public function test_attendance_register_8_2()
	{
		$user = User::find(1);

		$today = Carbon::now()->format('Y-m-d');
		$workTime = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $today . '%')->first();
		$new_workTime_id = count(WorkTime::all()) + 1;
		BreakTime::where('work_time_id', $workTime->id)->first()->delete();
		Attendance::where('work_time_id', $workTime->id)->first()->delete();
		$workTime->delete();

		$clock_in_time = Carbon::now()->format('Y-m-d H:i:s');
		$clock_out_time = Carbon::now()->format('Y-m-d H:i:s');
		$work_time_difference = strtotime($clock_out_time) - strtotime($clock_in_time);
		$work_hours = floor($work_time_difference / 3600);
		$work_minutes = floor(($work_time_difference % 3600) / 60);
		$work_seconds = $work_time_difference % 60;
		$work_time = date(sprintf('%02d', $work_hours) . ':' . sprintf('%02d', $work_minutes) . ':' . sprintf('%02d', $work_seconds));

		$work_day = strtotime($clock_in_time);
		$array = ["日", "月", "火", "水", "木", "金", "土"];
		$workDate = date('m / d', $work_day) . " (" . $array[date("w", $work_day)] . ")";

		$response1 = $this->actingAs($user)->get(route('attendanceRegister.index'));
		$response1->assertStatus(200);
		$response1->assertViewHas('status', 0);
		$response1->assertSeeInOrder([
			'勤務外',
			'出勤',
		]);

		$response2 = $this->actingAs($user)->post(route('attendanceRegister.clockIn'));
		$response2->assertStatus(200);
		$response2->assertViewHas('status', 1);
		$response2->assertSeeInOrder([
			'出勤中',
			'退勤',
			'休憩入',
		]);

		$this->assertDatabaseHas('work_times', [
			'user_id'       => $user->id,
			'clock_in_time' => Carbon::now()->format('Y-m-d H:i:s'),
		]);

		$response3 = $this->actingAs($user)->put(route('attendanceRegister.clockOut'), [
			'id' => $new_workTime_id,
		]);
		$response3->assertStatus(200);
		$response3->assertViewHas('status', 3);
		$response3->assertSeeInOrder([
			'退勤済',
			'お疲れ様でした。',
		]);

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => Carbon::now()->format('Y-m-d H:i:s'),
			'clock_out_time' => Carbon::now()->format('Y-m-d H:i:s'),
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $new_workTime_id,
			'work_day'             => $today,
			'total_break_time'     => "00:00:00",
			'actual_working_hours' => $work_time,
		]);

		$response4 = $this->actingAs($user)->get(route('attendance.index'));
		$response4->assertStatus(200);
		$response4->assertSeeInOrder([
			'勤怠一覧',
			$workDate,
			Carbon::now()->format('H:i'),
			Carbon::now()->format('H:i'),
			"00:00",
			substr($work_time, 0, 5),
			'詳細',
		]);
	}

}
