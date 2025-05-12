<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.その日の全ユーザーの勤怠情報が正確な値になっている
	public function test_attendance_list_12_1()
	{
		$admin = Admin::find(1);

		$work_day = Carbon::now()->format('Y/m/d');
		$workTimes = WorkTime::whereDate('clock_in_time', 'like', $work_day . '%')->get();
		$attendances = Attendance::all();

		$work_year = substr($work_day, 0, 4);
		$work_month = intval(substr($work_day, 5, 2));
		$work_date = intval(substr($work_day, 8, 2));
		$work_day_title = $work_year . "年" . $work_month . "月" . $work_date . "日の勤怠";

		$response = $this->actingAs($admin, 'admins')->get(route('admin.attendance.index'));
		$response->assertStatus(200);
		$response->assertSeeText($work_day_title);
		$response->assertSee($work_day);

		foreach ($workTimes as $workTime) {
			$user = User::find($workTime->id)->first();

			$workTimeId = $workTime->id;
			$clock_in_time = substr($workTime->clock_in_time, 11, 5);
			$clock_out_time = substr($workTime->clock_out_time, 11, 5);

			foreach ($attendances as $attendance) {
				$attendance_workTimeId = $attendance->work_time_id;
				if ($attendance_workTimeId == $workTimeId) {
					$total_break_time = substr($attendance->total_break_time, 0, 5);
					$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
					break;
				}
			}
			$response->assertSeeInOrder([
				$user->name,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}

	// 2.勤怠一覧画面にその日の日付が表示されている
	public function test_attendance_list_12_2()
	{
		$admin = Admin::find(1);
		$work_day = Carbon::now()->format('Y/m/d');

		$work_year = substr($work_day, 0, 4);
		$work_month = intval(substr($work_day, 5, 2));
		$work_date = intval(substr($work_day, 8, 2));
		$work_day_title = $work_year . "年" . $work_month . "月" . $work_date . "日の勤怠";

		$response = $this->actingAs($admin, 'admins')->get(route('admin.attendance.index'));
		$response->assertStatus(200);
		$response->assertSeeText($work_day_title);
		$response->assertSee($work_day);
	}

	// 3.前日の日付の勤怠情報が表示される
	public function test_attendance_list_12_3()
	{
		$admin = Admin::find(1);

		$work_day = Carbon::now()->format('Y/m/d');
		$year = substr($work_day, 0, 4);
		$month = intval(substr($work_day, 5, 2));
		$date = intval(substr($work_day, 8, 2));

		$work_day_title = $year . "年" . $month . "月" . $date . "日の勤怠";
		$prev_date_title = $year . "年" . $month . "月" . $date - 1 . "日の勤怠";
		$prevDate = date($year . '/' . sprintf('%02d', $month) . '/' . sprintf('%02d', $date - 1));
		$workTimes = WorkTime::whereDate('clock_in_time', 'like', $prevDate . '%')->get();
		$attendances = Attendance::all();

		$request = [
			'work_day' => $work_day,
			'prevDate'  => null,
		];

		$response1 = $this->actingAs($admin, 'admins')->get(route('admin.attendance.index'));
		$response1->assertStatus(200);
		$response1->assertSeeText($work_day_title);
		$response1->assertSee($work_day);

		$response2 = $this->actingAs($admin, 'admins')->post(route('admin.attendance.paginationDate'), $request);
		$response2->assertStatus(200);
		$response2->assertSeeText($prev_date_title);
		$response2->assertSee($prevDate);

		foreach ($workTimes as $workTime) {
			$user = User::find($workTime->id)->first();

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
			$response2->assertSeeInOrder([
				$user->name,
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}

	// 4.翌日の日付の勤怠情報が表示される
	public function test_attendance_list_12_4()
	{
		$admin = Admin::find(1);

		$work_day = Carbon::now()->format('Y/m/d');
		$year = substr($work_day, 0, 4);
		$month = intval(substr($work_day, 5, 2));
		$date = intval(substr($work_day, 8, 2));

		$work_day_title = $year . "年" . $month . "月" . $date . "日の勤怠";
		$next_date_title = $year . "年" . $month . "月" . $date + 1 . "日の勤怠";
		$nextDate = date($year . '/' . sprintf('%02d', $month) . '/' . sprintf('%02d', $date + 1));
		$workTimes = WorkTime::whereDate('clock_in_time', 'like', $nextDate . '%')->get();
		$attendances = Attendance::all();

		$request = [
			'work_day' => $work_day,
			'nextDate'  => null,
		];

		$response1 = $this->actingAs($admin, 'admins')->get(route('admin.attendance.index'));
		$response1->assertStatus(200);
		$response1->assertSeeText($work_day_title);
		$response1->assertSee($work_day);

		$response2 = $this->actingAs($admin, 'admins')->post(route('admin.attendance.paginationDate'), $request);
		$response2->assertStatus(200);
		$response2->assertSeeText($next_date_title);
		$response2->assertSee($nextDate);

		foreach ($workTimes as $workTime) {
			$user = User::find($workTime->id)->first();

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
			$response2->assertSeeInOrder([
				$user->name,
				$date,
				$clock_in_time,
				$clock_out_time,
				$total_break_time,
				$actual_working_hours,
				'詳細',
			]);
		}
	}
}
