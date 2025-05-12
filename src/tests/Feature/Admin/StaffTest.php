<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StaffTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
	public function test_staff_list_14_1()
	{
		$admin = Admin::find(1);
		$users = User::all();

		$response = $this->actingAs($admin, 'admins')->get(route('admin.attendance.staff'));
		$response->assertStatus(200);
		$response->assertSee('スタッフ一覧');
		foreach($users as $user) {
			$response->assertSeeInOrder([
				$user->name,
				$user->email,
				'詳細',
			]);
		}
	}

	// 2.勤怠情報が正確に表示される
	public function test_staff_list_14_2()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$work_month = Carbon::now()->format('Y/m');
		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $work_month . '%')->get();
		$attendances = Attendance::all();

		$response = $this->actingAs($admin, 'admins')->get('admin/attendance/staff/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$work_month,
			'CSV出力',
		]);

		foreach ($workTimes as $workTime) {
			$workTimeId = $workTime->id;
			$clock_in_time = substr($workTime->clock_in_time, 11, 5);
			$clock_out_time = substr($workTime->clock_out_time, 11, 5);
			$array = ["日", "月", "火", "水", "木", "金", "土"];

			foreach ($attendances as $attendance) {
				$attendance_workTimeId = $attendance->work_time_id;
				if ($attendance_workTimeId == $workTimeId) {
					$attendanceDate = strtotime($attendance->work_day);
					$date = date('m / d', $attendanceDate) . " (" . $array[date("w", $attendanceDate)] . ")";
					$total_break_time = substr($attendance->total_break_time, 0, 5);
					$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
					break;
				}
			}
			$response->assertSeeInOrder([
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}

	// 3.前月の情報が表示されている
	public function test_staff_list_14_3()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$work_month = Carbon::now()->format('Y/m');
		$year = substr($work_month, 0, 4);
		$month = intval(substr($work_month, 5, 2));
		$prevMonth = date($year . '/' . sprintf('%02d', $month - 1));

		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $prevMonth . '%')->get();
		$attendances = Attendance::all();

		$request = [
			'work_month' => $work_month,
			'prevMonth'  => null,
		];

		$response = $this->actingAs($admin, 'admins')->get('admin/attendance/staff/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$work_month,
			'CSV出力',
		]);

		$response = $this->actingAs($admin, 'admins')->post('admin/attendance/staff/1', $request);
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$prevMonth,
			'CSV出力',
		]);

		foreach ($workTimes as $workTime) {
			$workTimeId = $workTime->id;
			$clock_in_time = substr($workTime->clock_in_time, 11, 5);
			$clock_out_time = substr($workTime->clock_out_time, 11, 5);
			$array = ["日", "月", "火", "水", "木", "金", "土"];

			foreach ($attendances as $attendance) {
				$attendance_workTimeId = $attendance->work_time_id;
				if ($attendance_workTimeId == $workTimeId) {
					$attendanceDate = strtotime($attendance->work_day);
					$date = date('m / d', $attendanceDate) . " (" . $array[date("w", $attendanceDate)] . ")";
					$total_break_time = substr($attendance->total_break_time, 0, 5);
					$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
					break;
				}
			}
			$response->assertSeeInOrder([
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}

	// 4.翌月の情報が表示されている
	public function test_staff_list_14_4()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$work_month = Carbon::now()->format('Y/m');
		$year = substr($work_month, 0, 4);
		$month = intval(substr($work_month, 5, 2));
		$nextMonth = date($year . '/' . sprintf('%02d', $month + 1));

		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', $nextMonth . '%')->get();
		$attendances = Attendance::all();

		$request = [
			'work_month' => $work_month,
			'nextMonth'  => null,
		];

		$response = $this->actingAs($admin, 'admins')->get('admin/attendance/staff/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$work_month,
			'CSV出力',
		]);

		$response = $this->actingAs($admin, 'admins')->post('admin/attendance/staff/1', $request);
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$nextMonth,
			'CSV出力',
		]);

		foreach ($workTimes as $workTime) {
			$workTimeId = $workTime->id;
			$clock_in_time = substr($workTime->clock_in_time, 11, 5);
			$clock_out_time = substr($workTime->clock_out_time, 11, 5);
			$array = ["日", "月", "火", "水", "木", "金", "土"];

			foreach ($attendances as $attendance) {
				$attendance_workTimeId = $attendance->work_time_id;
				if ($attendance_workTimeId == $workTimeId) {
					$attendanceDate = strtotime($attendance->work_day);
					$date = date('m / d', $attendanceDate) . " (" . $array[date("w", $attendanceDate)] . ")";
					$total_break_time = substr($attendance->total_break_time, 0, 5);
					$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
					break;
				}
			}
			$response->assertSeeInOrder([
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}

	// 5.その日の勤怠詳細画面に遷移する
	public function test_staff_list_14_5()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m') . '%')->get();
		$work_month = Carbon::now()->format('Y/m');
		$attendances = Attendance::all();

		$response = $this->actingAs($admin, 'admins')->get('admin/attendance/staff/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			$user->name . 'さんの勤怠',
			$work_month,
			'CSV出力',
		]);

		foreach ($workTimes as $workTime) {
			$workTimeId = $workTime->id;
			$clock_in_time = substr($workTime->clock_in_time, 11, 5);
			$clock_out_time = substr($workTime->clock_out_time, 11, 5);
			$array = ["日", "月", "火", "水", "木", "金", "土"];

			foreach ($attendances as $attendance) {
				$attendance_workTimeId = $attendance->work_time_id;
				if ($attendance_workTimeId == $workTimeId) {
					$attendanceDate = strtotime($attendance->work_day);
					$date = date('m / d', $attendanceDate) . " (" . $array[date("w", $attendanceDate)] . ")";
					$total_break_time = substr($attendance->total_break_time, 0, 5);
					$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
					break;
				}
			}
			$response->assertSeeInOrder([
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
			]);
		}

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = substr($workTime->clock_in_time, 0, 4);
		$month = substr($workTime->clock_in_time, 5, 2);
		$date = substr($workTime->clock_in_time, 8, 2);

		$response2 = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response2->assertStatus(200);
		$response2->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date(intval($year) . '年'),
			date(intval($month) . '月' . intval($date) . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);
	}
}
