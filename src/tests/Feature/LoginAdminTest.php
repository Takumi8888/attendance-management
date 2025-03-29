<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
// use Illuminate\Support\Facades\Auth;


class LoginAdminTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
    {
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
    }


	public function test_login_admin_validation_email_required()
	{
		Admin::create([
			'name'				=> '管理者',
			'email'				=> 'admin@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/admin/login', [
			'email'    => null,
			'password' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
		$response->assertStatus(302);
	}


	public function test_login_admin_validation_password_required()
	{
		Admin::create([
			'name'				=> '管理者',
			'email'				=> 'admin@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/admin/login', [
			'email'    => 'admin@example.com',
			'password' => null,
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
		$response->assertStatus(302);
	}


	public function test_login_admin_validation_password_failed()
	{
		Admin::create([
			'name'				=> '管理者',
			'email'				=> 'admin@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/admin/login', [
			'email'    => 'staff@example.com',
			'password' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
		$response->assertStatus(302);
	}


	public function test_login_admin()
	{
		$admin = Admin::create([
			'name'				=> '管理者',
			'email'				=> 'admin@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post(route('admin.login.store'), [
			'email'    => 'admin@example.com',
			'password' => 'password',
		]);

		// dd(Auth::guard('admins')->check(), Auth::guard('web')->check());

		$response->assertStatus(302);
		$response->assertRedirect('/admin/attendance/list');
		$this->assertAuthenticatedAs($admin, 'admins');
	}


	public function test_logout_admin()
	{
		$admin = Admin::find(1);
		$response = $this->actingAs($admin, 'admins')->post(route('admin.login.destroy'));

		// 認証確認
		// dd(Auth::guard('admins')->check(), Auth::guard('web')->check());

		$response->assertStatus(302);
		$response->assertRedirect('/login');
		$this->assertGuest();
	}
}