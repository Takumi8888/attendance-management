<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
// use Illuminate\Support\Facades\Auth;

class LoginUserTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	public function test_login_user_validation_email_required()
	{
		User::create([
			'name'				=> 'テストユーザ',
			'email'				=> 'staff@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/login', [
			'email'    => null,
			'password' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
		$response->assertStatus(302);
	}

	public function test_login_user_validation_password_required()
	{
		User::create([
			'name'				=> 'テストユーザ',
			'email'				=> 'staff@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/login', [
			'email'    => 'staff@example.com',
			'password' => null,
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
		$response->assertStatus(302);
	}

	public function test_login_user_validation_password_failed()
	{
		User::create([
			'name'				=> 'テストユーザ',
			'email'				=> 'staff@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/login', [
			'email'    => 'admin@example.com',
			'password' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
		$response->assertStatus(302);
	}

	public function test_login_user()
	{
		$user = User::create([
			'name'				=> 'テストユーザ',
			'email'				=> 'staff@example.com',
			'email_verified_at'	=> '2025-04-01 12:00:00',
			'password'			=> 'password',
			'remember_token' 	=> 'ZEy6zPMVx1',
		]);

		$response = $this->post('/login', [
			'email'    => 'staff@example.com',
			'password' => 'password',
		]);

		$response->assertStatus(302);
		$response->assertRedirect('/attendance');
		$this->assertAuthenticatedAs($user);
	}

	public function test_logout_user() {
		$user = User::find(1);
		$response = $this->actingAs($user)->post('/logout');

		$response->assertStatus(302);
		$response->assertRedirect('/login');
		$this->assertGuest();
	}
}
