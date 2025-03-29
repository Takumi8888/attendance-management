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

class AttendanceTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	//勤怠一覧画面表示
	// public function test_get_attendance_list()
	// {
	// 	$user = User::find(1);
	// 	$response = $this->actingAs($user)->get('/attendance/list');

	// 	$workTimes = WorkTime::where('user_id', $user)->whereDate('clock_in_time', 'like', Carbon::now()->format('Y-m') . '%')->get();
	// 	$work_month = Carbon::now()->format('Y/m');;
	// 	$attendances = Attendance::all();

	// 	$response->assertStatus(200);
	// 	$response->assertSee($work_month);

	// 	foreach ($workTimes as $workTime) {
	// 		$workTimeId = $workTime->id;
	// 		$clockIn = substr($workTime->clock_in_time, 11, 5);
	// 		$clockOut = substr($workTime->clock_out_time, 11, 5);
	// 		$array = ["日", "月", "火", "水", "木", "金", "土"];

	// 		foreach ($attendances as $attendance) {
	// 			$attendance_workTimeId = $attendance->work_time_id;
	// 			if ($attendance_workTimeId == $workTimeId) {
	// 				$attendanceDate = strtotime($attendance->work_day);
	// 				$date = date('m / d', $attendanceDate) . " (" . $array[date("w", $attendanceDate)] . ")";
	// 				$total_break_time = substr($attendance->total_break_time, 0, 5);
	// 				$actual_working_hours = substr($attendance->actual_working_hours, 0, 5);
	// 				break;
	// 			}
	// 		}
	// 		$response->assertSeeInOrder([
	// 			$date,
	// 			$clockIn,
	// 			$clockOut,
	// 			$total_break_time,
	// 			$actual_working_hours,
	// 		]);
	// 	}
	// }

	//勤怠一覧画面表示
	// public function test_get_attendance_list_pagination_prevMonth()
	// {
	// 	$user = User::find(1);
	// 	$response = $this->actingAs($user)->post('/attendance/list',[
	// 		'work_month' => '2025-02-01'
	// 	]);

	// 	$year = 2025;
	// 	$month = 3;
	// 	$date = 1;

	// 	// 前月ボタン押下
	// 	$month -= 1;
	// 	$work_month = Carbon::createFromDate($year, $month, $date)->format('Y/m');

	// 	$workTimes = WorkTime::where('user_id', $user)->whereDate('clock_in_time', 'like', $work_month->format('Y-m') . '%')->get();
	// 	$attendances = Attendance::all();

	// 	$response->assertRedirect('/attendance/list');
	// 	$response->assertSee('work_month');

	// }
}

