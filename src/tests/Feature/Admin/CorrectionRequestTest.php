<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\BreakTime;
use App\Models\User;
use App\Models\WorkTime;
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

	// 1.詳細画面の内容が選択した情報と一致する
	public function test_request_details_13_1()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
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

	// 2.「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
	public function test_request_details_13_2()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date($year . '年'),
			date($month . '月' . $date . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);

		$response = $this->actingAs($admin, 'admins')->put('/admin/attendance/1', [
			'clock_in_time'  => '21:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['13:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です']);
	}

	// 3.「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
	public function test_request_details_13_3()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date($year . '年'),
			date($month . '月' . $date . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);

		$response = $this->actingAs($admin, 'admins')->put('/admin/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['08:00'],
			'end_time'       => ['13:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['start_time.0' => '休憩時間が勤務時間外です']);
	}

	// 4.「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
	public function test_request_details_13_4()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date($year . '年'),
			date($month . '月' . $date . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);

		$response = $this->actingAs($admin, 'admins')->put('/admin/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['23:00'],
			'note'           => '電車遅延のため',
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['end_time.0' => '休憩時間が勤務時間外です']);
	}

	// 5.「備考を記入してください」というバリデーションメッセージが表示される
	public function test_request_details_13_5()
	{
		$admin = Admin::find(1);
		$user = User::find(1);

		$workTime = WorkTime::where('id', 1)->first();
		$clock_in_time = substr($workTime->clock_in_time, 11, 5);
		$clock_out_time = substr($workTime->clock_out_time, 11, 5);

		$breakTime = BreakTime::where('work_time_id', $workTime->id)->first();
		$start_time = substr($breakTime->start_time, 11, 5);
		$end_time = substr($breakTime->end_time, 11, 5);

		$year = intval(substr($workTime->clock_in_time, 0, 4));
		$month = intval(substr($workTime->clock_in_time, 5, 2));
		$date = intval(substr($workTime->clock_in_time, 8, 2));

		$response = $this->actingAs($admin, 'admins')->get('/admin/attendance/1');
		$response->assertStatus(200);
		$response->assertSeeInOrder([
			'勤怠詳細',
			$user->name,
			date($year . '年'),
			date($month . '月' . $date . '日'),
			$clock_in_time,
			$clock_out_time,
			$start_time,
			$end_time,
		]);

		$response = $this->actingAs($admin, 'admins')->put('/admin/attendance/1', [
			'clock_in_time'  => '09:00',
			'clock_out_time' => '18:00',
			'start_time'     => ['12:00'],
			'end_time'       => ['13:00'],
		]);
		$response->assertStatus(302);
		$response->assertSessionHasErrors(['note' => '備考を記入してください']);
	}

}