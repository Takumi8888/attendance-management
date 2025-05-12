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

class AttendanceTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.自分の勤怠情報が全て表示されている
	public function test_attendance_list_9_1()
	{
		$user = User::find(1);

		$workTimes = WorkTime::where('user_id', $user->id)->whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m') . '%')->get();
		$work_month = Carbon::now()->format('Y/m');
		$attendances = Attendance::all();

		$response = $this->actingAs($user)->get(route('attendance.index'));
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠一覧',
			$work_month,
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

	// 2.現在の月が表示されている
	public function test_attendance_list_9_2()
	{
		$user = User::find(1);
		$work_month = Carbon::now()->format('Y/m');

		$response = $this->actingAs($user)->get(route('attendance.index'));
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠一覧',
			$work_month,
		]);
	}

	// 3.前月の情報が表示されている
	public function test_attendance_list_9_3()
	{
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

		$response1 = $this->actingAs($user)->get(route('attendance.index'));
		$response1->assertStatus(200);
		$response1->assertSeeInOrder([
			'勤怠一覧',
			$work_month,
		]);

		$response2 = $this->actingAs($user)->post(route('attendance.paginationMonth'), $request);
		$response2->assertStatus(200);
		$response2->assertSeeInOrder([
			'勤怠一覧',
			$prevMonth,
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
			$response2->assertSeeInOrder([
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
	public function test_attendance_list_9_4()
	{
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

		$response1 = $this->actingAs($user)->get(route('attendance.index'));
		$response1->assertStatus(200);
		$response1->assertSeeInOrder([
			'勤怠一覧',
			$work_month,
		]);

		$response2 = $this->actingAs($user)->post(route('attendance.paginationMonth'), $request);
		$response2->assertStatus(200);
		$response2->assertSeeInOrder([
			'勤怠一覧',
			$nextMonth,
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
			$response2->assertSeeInOrder([
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
	public function test_attendance_list_9_5()
	{
		$user = User::find(1);

		$work_month = Carbon::now()->format('Y/m');
		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response1 = $this->actingAs($user)->get(route('attendance.index'));
		$response1->assertStatus(200);
		$response1->assertSeeInOrder([
			'勤怠一覧',
			$work_month,
		]);

		$response2 = $this->actingAs($user)->get('/attendance/1');
		$response2->assertStatus(200);
		$response2->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date($year . '年'),
			date($month . '月' . $date . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);
	}
}

