<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\WorkTime;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceRegisterTest extends TestCase
{

	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	//勤怠登録画面表示
	public function test_get_attendance_0()
	{
		// テスト用にユーザーを作成
		$user = User::create([
			'name'					=> 'テストユーザ',
			'email'					=> 'test@example.com',
			'email_verified_at'		=> '2025-03-26 15:25:22',
			'password'				=> 'password',
			'remember_token' 		=> 'ZEy6zPMVx1',
		]);

		// テスト対象のURLにアクセスさせる
		$response = $this->actingAs($user)->get(route('attendanceRegister.index'));

		$response = $this->actingAs($user)->get('/attendance',[
			'status' => '0'
		]);

		$response->assertStatus(200);
		$response->assertViewHas('status', 0);
	}

	//勤怠登録画面表示
	// public function test_get_attendance_1()
	// {
	// 	$user = User::find(1);
	// 	$response = $this->actingAs($user)->get('/attendance', [
	// 		'status' => '1'
	// 	]);

	// 	$response->assertStatus(200);
	// 	$response->assertViewHas('status', 3);
	// }

	// //勤怠登録画面表示
	// public function test_get_attendance_2()
	// {
	// 	$user = User::find(1);
	// 	$response = $this->actingAs($user)->get('/attendance', [
	// 		'status' => '2'
	// 	]);

	// 	$response->assertStatus(200);
	// 	$response->assertViewHas('status', 3);
	// }

	// //勤怠登録画面表示
	// public function test_get_attendance_3()
	// {
	// 	$user = User::find(1);
	// 	$response = $this->actingAs($user)->get('/attendance', [
	// 		'status' => '3'
	// 	]);

	// 	$response->assertStatus(200);
	// 	$response->assertViewHas('status', 3);
	// }
}
