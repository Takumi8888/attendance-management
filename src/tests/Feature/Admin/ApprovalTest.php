<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\WorkTime;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.全ユーザーの未承認の修正申請が表示される
	public function test_staff_list_15_1()
	{
		$admin = Admin::find(1);

		$pendingApprovals = CorrectionRequest::where('status', 1)->get();
		$count = count($pendingApprovals);
		if (0 < $count && $count <= 15) {
			$page = 1;
			$i = 0;
		} elseif (15 < $count && $count <= 30) {
			$page = 2;
			$i = 15;
		} elseif (30 < $count && $count <= 45) {
			$page = 3;
			$i = 30;
		}

		$response = $this->actingAs($admin, 'admins')->get(route('admin.correctionRequest.pendingApproval'),[
			'page' => $page,
		]);
		$response->assertStatus(200);

		foreach ($pendingApprovals as $pendingApproval) {
			if ($page == 1 && $i <= 15) {
				$i += 1;
				$user = User::find($pendingApproval->user_id)->first();

				$attendance = Attendance::where('id', $pendingApproval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($pendingApproval->application_date, 0, 4);
				$month = substr($pendingApproval->application_date, 5, 2);
				$date = substr($pendingApproval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$pendingApproval->note,
				]);
			} elseif ($page == 2 && 15 < $i && $i <= 30) {
				$i += 1;
				$user = User::find($pendingApproval->user_id)->first();

				$attendance = Attendance::where('id', $pendingApproval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($pendingApproval->application_date, 0, 4);
				$month = substr($pendingApproval->application_date, 5, 2);
				$date = substr($pendingApproval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$pendingApproval->note,
				]);
			} elseif ($page == 3 && 30 < $i && $i <= 45) {
				$i += 1;
				$user = User::find($pendingApproval->user_id)->first();

				$attendance = Attendance::where('id', $pendingApproval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($pendingApproval->application_date, 0, 4);
				$month = substr($pendingApproval->application_date, 5, 2);
				$date = substr($pendingApproval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$pendingApproval->note,
				]);
			}
		}
	}

	// 2.全ユーザーの承認済みの修正申請が表示される
	public function test_staff_list_15_2()
	{
		$admin = Admin::find(1);

		$approvals = CorrectionRequest::where('status', 2)->get();
		$count = count($approvals);
		if (0 < $count && $count <= 15) {
			$page = 1;
			$i = 0;
		} elseif (15 < $count && $count <= 30) {
			$page = 2;
			$i = 15;
		} elseif (30 < $count && $count <= 45) {
			$page = 3;
			$i = 30;
		}

		$response = $this->actingAs($admin, 'admins')->post(route('admin.correctionRequest.approval'), [
			'page' => $page,
		]);
		$response->assertStatus(200);

		foreach ($approvals as $approval) {
			if ($page == 1 && $i <= 15) {
				$i += 1;
				$user = User::find($approval->user_id)->first();

				$attendance = Attendance::where('id', $approval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($approval->application_date, 0, 4);
				$month = substr($approval->application_date, 5, 2);
				$date = substr($approval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$approval->note,
				]);
			} elseif ($page == 2 && 15 < $i && $i <= 30) {
				$i += 1;
				$user = User::find($approval->user_id)->first();

				$attendance = Attendance::where('id', $approval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($approval->application_date, 0, 4);
				$month = substr($approval->application_date, 5, 2);
				$date = substr($approval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$approval->note,
				]);
			} elseif ($page == 3 && 30 < $i && $i <= 45) {
				$i += 1;
				$user = User::find($approval->user_id)->first();

				$attendance = Attendance::where('id', $approval->attendance_id)->first();
				$year = substr($attendance->work_day, 0, 4);
				$month = substr($attendance->work_day, 5, 2);
				$date = substr($attendance->work_day, 8, 2);
				$attendance_date = $year . '/' . $month . '/' . $date;

				$year = substr($approval->application_date, 0, 4);
				$month = substr($approval->application_date, 5, 2);
				$date = substr($approval->application_date, 8, 2);
				$application_date = $year . '/' . $month . '/' . $date;

				$response->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$application_date,
					$approval->note,
				]);
			}
		}
	}

	// 3.申請内容が正しく表示されている
	public function test_staff_list_15_3()
	{
		$admin = Admin::find(1);

		$pendingApproval = CorrectionRequest::where('status', 1)->first();
		$user = User::find($pendingApproval->user_id);
		$attendance = Attendance::where('id', $pendingApproval->attendance_id)->first();

		$workTime = WorkTime::where('id', $attendance->work_time_id)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = substr($workTime->clock_in_time, 0, 4);
		$month = substr($workTime->clock_in_time, 5, 2);
		$date = substr($workTime->clock_in_time, 8, 2);

		$response = $this->actingAs($admin, 'admins')->get('admin/stamp_correction_request/approve/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date(intval($year) . '年'),
			date(intval($month) . '月' . intval($date) . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
			$pendingApproval->note,
			'承認',
		]);
	}

	// 4.修正申請が承認され、勤怠情報が更新される
	public function test_staff_list_15_4()
	{
		$admin = Admin::find(1);

		$pendingApproval = CorrectionRequest::where('status', 1)->first();
		$user = User::find($pendingApproval->user_id);
		$attendance = Attendance::where('id', $pendingApproval->attendance_id)->first();

		$workTime = WorkTime::where('id', $attendance->work_time_id)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = substr($workTime->clock_in_time, 0, 4);
		$month = substr($workTime->clock_in_time, 5, 2);
		$date = substr($workTime->clock_in_time, 8, 2);

		$response = $this->actingAs($admin, 'admins')->get('admin/stamp_correction_request/approve/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date(intval($year) . '年'),
			date(intval($month) . '月' . intval($date) . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
			$pendingApproval->note,
			'承認',
		]);

		$response = $this->actingAs($admin, 'admins')->put('admin/stamp_correction_request/approve/1');
		$response->assertStatus(302);
		$response->assertRedirect('admin/stamp_correction_request/list');

		$this->assertDatabaseHas('correction_requests', [
			'attendance_id'    => $pendingApproval->id,
			'user_id'          => $user->id,
			'admin_id'         => $admin->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 2,
			'note'             => $pendingApproval->note,
		]);
	}
}
