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

class AuthTest extends TestCase
{
	use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
	protected function setUp(): void
    {
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
    }

	// 会員登録
	public function test_register_user() {
		$response = $this->post('/register',[
			'name'                  => 'テストユーザ',
			'email'                 => 'test@example.com',
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertRedirect('/email/verify');
		$this->assertDatabaseHas(User::class, [
			'name'  => 'テストユーザ',
			'email' => 'test@example.com',
		]);
	}

	// ログイン（スタッフ）
	public function test_login_user() {
		$user = User::find(1);

		$response = $this->post('/login',[
			'email'    => 'reina.n@coachtech.com',
			'password' => 'password',
		]);

		$response->assertRedirect('/attendance');
		$this->assertAuthenticatedAs($user);
	}

	// ログアウト（スタッフ）
	public function test_logout_user() {
		$user = User::find(1);
		$response = $this->actingAs($user)->post('/logout');

		$response->assertRedirect('/login');
		$this->assertGuest();
	}

	// ログイン（管理者）
	public function test_login_admin() {
		$response = $this->post('/admin/login', [
			'email'    => 'admin@coachtech.com',
			'password' => 'password',
		]);

		$response->assertRedirect('/admin/attendance/list');
	}

	// ログアウト（管理者）
	public function test_logout_admin() {
		$admin = Admin::find(1);
		$response = $this->actingAs($admin)->post(route('admin.login.logout'));

		$response->assertRedirect('/login');
	}
}