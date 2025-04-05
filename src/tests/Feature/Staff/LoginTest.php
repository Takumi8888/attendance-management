<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class LoginTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.「メールアドレスを入力してください」というバリデーションメッセージが表示される
	public function test_login_2_1()
	{
		User::create([
			'name'				=> 'test_user',
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

	// 2.「パスワードを入力してください」というバリデーションメッセージが表示される
	public function test_login_2_2()
	{
		User::create([
			'name'				=> 'test_user',
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

	// 3.「ログイン情報が登録されていません」というバリデーションメッセージが表示される
	public function test_login_2_3()
	{
		User::create([
			'name'				=> 'test_user',
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

	// 4.ログイン処理が実行される
	public function test_login_add1_login()
	{
		$user = User::create([
			'name'				=> 'test_user',
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

	// 5.ログアウト処理が実行される
	public function test_login_add2_logout()
	{
		$user = User::find(1);

		$response = $this->actingAs($user)->post('/logout');
		$response->assertStatus(302);
		$response->assertRedirect('/login');

		$this->assertGuest();
	}
}
