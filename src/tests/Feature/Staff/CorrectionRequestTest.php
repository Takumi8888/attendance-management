<?php

namespace Tests\Feature\Staff;

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

class CorrectionRequestTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.名前がログインユーザーの名前になっている
	public function test_request_details_10_1()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->get('/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
		]);
	}

	// 2.日付が選択した日付になっている
	public function test_request_details_10_2()
	{
		$user = User::find(1);
		$workTime = WorkTime::where('id', 1)->first();
		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($user)->get('/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			date($year . '年'),
			date($month . '月' . $date . '日'),
		]);
	}

	// 3.「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
	public function test_request_details_10_3()
	{
		$user = User::find(1);
		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$response = $this->actingAs($user)->get('/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$clock_in_time,
			$clock_out_time,
		]);
	}

	// 4.「休憩」にて記されている時間がログインユーザーの打刻と一致している
	public function test_request_details_10_4()
	{
		$user = User::find(1);
		$workTime = WorkTime::where('id', 1)->first();
		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$response = $this->actingAs($user)->get('/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$start_time,
			$end_time,
		]);
	}

	// 5.「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
	public function test_request_details_11_1()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => '21:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['13:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です']);
	}

	// 6.「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
	public function test_request_details_11_2()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['08:00'],
			'end_time'       => ['13:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['start_time.0' => '休憩時間が勤務時間外です']);
	}

	// 7.「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
	public function test_request_details_11_3()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['23:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['end_time.0' => '休憩時間が勤務時間外です']);
	}

	// 8.「備考を記入してください」というバリデーションメッセージが表示される
	public function test_request_details_11_4()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['13:00'],
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['note' => '備考を記入してください']);
	}

	// 9.修正申請が実行され、管理者の承認画面と申請一覧画面に表示される
	public function test_request_details_11_5()
	{
		$user = User::find(1);
		$admin = Admin::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = substr($workTime->clock_in_time, 0, 4);
		$month = substr($workTime->clock_in_time, 5, 2);
		$date = substr($workTime->clock_in_time, 8, 2);
		$request_year = date(intval($year) . '年');
		$request_date = date(intval($month) . '月' . intval($date) . '日');

		$attendance = Attendance::where('work_time_id', $workTime->id)->first();
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

		$clock_in_time   = '09:00';
		$clock_out_time  = '18:00';
		$start_time      = '12:00';
		$end_time        = '13:00';
		$work_time       = '09:00:00';
		$break_time      = '01:00:00';
		$actual_workTime = '08:00:00';
		$note            = '電車遅延のため';

		$response1 = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'start_time'     => [$start_time],
			'end_time'       => [$end_time],
			'note'           => $note,
		]);
		$response1->assertStatus(302);
		$response1->assertRedirect('/stamp_correction_request/list');

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => $attendance->work_day . " " . $clock_in_time,
			'clock_out_time' => $attendance->work_day . " " . $clock_out_time,
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => $attendance->work_day . " " . $start_time,
			'end_time'     => $attendance->work_day . " " . $end_time,
			'break_time'   => $break_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $workTime->id,
			'work_day'             => $attendance->work_day,
			'total_break_time'     => $break_time,
			'actual_working_hours' => $actual_workTime,
		]);

		$this->assertDatabaseHas('correction_requests',[
			'attendance_id'    => 1,
			'user_id'          => $user->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 1,
			'note'             => $note,
		]);

		$response2 = $this->actingAs($admin, 'admins')->get('/admin/stamp_correction_request/approve/1');
		$response2->assertStatus(200);
		$response2->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			$request_year,
			$request_date,
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
			$note,
			'承認',
		]);

		$response3 = $this->actingAs($admin, 'admins')->get(route('admin.correctionRequest.pendingApproval'),[
			'page' => $page,
		]);
		$response3->assertStatus(200);

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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
				]);
			}
		}
	}

	// 10.申請一覧に自分の申請が全て表示されている
	public function test_request_details_11_6()
	{
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$attendance = Attendance::where('work_time_id', $workTime->id)->first();
		$pendingApprovals = CorrectionRequest::where('user_id', $user->id)->where('status', 1)->get();
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

		$clock_in_time   = '09:00';
		$clock_out_time  = '18:00';
		$start_time      = '12:00';
		$end_time        = '13:00';
		$work_time       = '09:00:00';
		$break_time      = '01:00:00';
		$actual_workTime = '08:00:00';
		$note            = '電車遅延のため';

		$response1 = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'start_time'     => [$start_time],
			'end_time'       => [$end_time],
			'note'           => $note,
		]);
		$response1->assertStatus(302);
		$response1->assertRedirect('/stamp_correction_request/list');

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => $attendance->work_day . " " . $clock_in_time,
			'clock_out_time' => $attendance->work_day . " " . $clock_out_time,
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => $attendance->work_day . " " . $start_time,
			'end_time'     => $attendance->work_day . " " . $end_time,
			'break_time'   => $break_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $workTime->id,
			'work_day'             => $attendance->work_day,
			'total_break_time'     => $break_time,
			'actual_working_hours' => $actual_workTime,
		]);

		$this->assertDatabaseHas('correction_requests', [
			'attendance_id'    => 1,
			'user_id'          => $user->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 1,
			'note'             => $note,
		]);

		$response2 = $this->actingAs($user)->get(route('correctionRequest.pendingApproval'),[
			'page' => $page,
		]);
		$response2->assertStatus(200);

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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
				]);
			}
		}
	}

	// 11.承認済みに管理者が承認した申請が全て表示されている
	public function test_request_details_11_7()
	{
		$user = User::find(1);
		$admin = Admin::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$attendance = Attendance::where('work_time_id', $workTime->id)->first();
		$approvals = CorrectionRequest::where('user_id', $user->id)->where('status', 2)->get();
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

		$clock_in_time   = '09:00';
		$clock_out_time  = '18:00';
		$start_time      = '12:00';
		$end_time        = '13:00';
		$work_time       = '09:00:00';
		$break_time      = '01:00:00';
		$actual_workTime = '08:00:00';
		$note            = '電車遅延のため';

		$response1 = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'start_time'     => [$start_time],
			'end_time'       => [$end_time],
			'note'           => $note,
		]);
		$response1->assertStatus(302);
		$response1->assertRedirect('/stamp_correction_request/list');

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => $attendance->work_day . " " . $clock_in_time,
			'clock_out_time' => $attendance->work_day . " " . $clock_out_time,
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => $attendance->work_day . " " . $start_time,
			'end_time'     => $attendance->work_day . " " . $end_time,
			'break_time'   => $break_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $workTime->id,
			'work_day'             => $attendance->work_day,
			'total_break_time'     => $break_time,
			'actual_working_hours' => $actual_workTime,
		]);

		$this->assertDatabaseHas('correction_requests', [
			'attendance_id'    => 1,
			'user_id'          => $user->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 1,
			'note'             => $note,
		]);

		$response2 = $this->actingAs($admin, 'admins')->put('/admin/stamp_correction_request/approve/1');
		$response2->assertStatus(302);
		$response2->assertRedirect('/admin/stamp_correction_request/list');

		$this->assertDatabaseHas('correction_requests', [
			'attendance_id'    => 1,
			'user_id'          => $user->id,
			'admin_id'         => $admin->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 2,
			'note'             => $note,
		]);

		$response3 = $this->actingAs($user)->post(route('correctionRequest.approval'));
		$response3->assertStatus(200);

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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認済み',
					$user->name,
					$attendance_date,
					$approval->note,
					$application_date,
					'詳細'
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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認済み',
					$user->name,
					$attendance_date,
					$approval->note,
					$application_date,
					'詳細'
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

				$response3->assertSeeInOrder([
					'申請一覧',
					'承認済み',
					$user->name,
					$attendance_date,
					$approval->note,
					$application_date,
					'詳細'
				]);
			}
		}

	}

	// 12.申請詳細画面に遷移する
	public function test_request_details_11_8()
	{
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = substr($workTime->clock_in_time, 0, 4);
		$month = substr($workTime->clock_in_time, 5, 2);
		$date = substr($workTime->clock_in_time, 8, 2);
		$request_year = date(intval($year) . '年');
		$request_date = date(intval($month) . '月' . intval($date) . '日');

		$attendance = Attendance::where('work_time_id', $workTime->id)->first();
		$pendingApprovals = CorrectionRequest::where('user_id', $user->id)->where('status', 1)->get();
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

		$clock_in_time   = '09:00';
		$clock_out_time  = '18:00';
		$start_time      = '12:00';
		$end_time        = '13:00';
		$work_time       = '09:00:00';
		$break_time      = '01:00:00';
		$actual_workTime = '08:00:00';
		$note            = '電車遅延のため';

		$response1 = $this->actingAs($user)->post('/attendance/1', [
			'clock_in_time'  => $clock_in_time,
			'clock_out_time' => $clock_out_time,
			'start_time'     => [$start_time],
			'end_time'       => [$end_time],
			'note'           => $note,
		]);
		$response1->assertStatus(302);
		$response1->assertRedirect('/stamp_correction_request/list');

		$this->assertDatabaseHas('work_times', [
			'user_id'        => $user->id,
			'clock_in_time'  => $attendance->work_day . " " . $clock_in_time,
			'clock_out_time' => $attendance->work_day . " " . $clock_out_time,
			'work_time'      => $work_time,
		]);

		$this->assertDatabaseHas('break_times', [
			'work_time_id' => $workTime->id,
			'start_time'   => $attendance->work_day . " " . $start_time,
			'end_time'     => $attendance->work_day . " " . $end_time,
			'break_time'   => $break_time,
		]);

		$this->assertDatabaseHas('attendances', [
			'work_time_id'         => $workTime->id,
			'work_day'             => $attendance->work_day,
			'total_break_time'     => $break_time,
			'actual_working_hours' => $actual_workTime,
		]);

		$this->assertDatabaseHas('correction_requests', [
			'attendance_id'    => 1,
			'user_id'          => $user->id,
			'application_date' => Carbon::now()->format('Y-m-d'),
			'status'           => 1,
			'note'             => $note,
		]);

		$response2 = $this->actingAs($user)->get(route('correctionRequest.pendingApproval'), [
			'page' => $page,
		]);
		$response2->assertStatus(200);

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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
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

				$response2->assertSeeInOrder([
					'申請一覧',
					'承認待ち',
					$user->name,
					$attendance_date,
					$pendingApproval->note,
					$application_date,
					'詳細'
				]);
			}
		}

		$response3 = $this->actingAs($user)->get('/attendance/1');
		$response3->assertStatus(200);
		$response3->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			$request_year,
			$request_date,
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
			$note,
			'承認待ちのため修正はできません。',
		]);
	}
}