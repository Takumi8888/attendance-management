<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegisterTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	public function test_register_validation_name_required()
	{
		$response = $this->post('/register', [
			'name'                  => null,
			'email'                 => 'staff@example.com',
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
		$response->assertStatus(302);
	}

	public function test_register_validation_email_required()
	{
		$response = $this->post('/register', [
			'name'                  => 'テストユーザ',
			'email'                 => null,
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
		$response->assertStatus(302);
	}

	public function test_register_validation_password_required()
	{
		$response = $this->post('/register', [
			'name'                  => 'テストユーザ',
			'email'                 => 'staff@example.com',
			'password'              => null,
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
		$response->assertStatus(302);
	}

	public function test_register_validation_password_min()
	{
		$response = $this->post('/register', [
			'name'                  => 'テストユーザ',
			'email'                 => 'staff@example.com',
			'password'              => 'pass',
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
		$response->assertStatus(302);
	}

	public function test_register_validation_password_confirmed()
	{
		$response = $this->post('/register', [
			'name'                  => 'テストユーザ',
			'email'                 => 'staff@example.com',
			'password'              => 'password',
			'password_confirmation' => 'pass',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
		$response->assertStatus(302);
	}

	public function test_register()
	{
		$response = $this->post('/register', [
			'name'                  => 'テストユーザ',
			'email'                 => 'staff@example.com',
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertRedirect('/email/verify');
		$this->assertDatabaseHas(User::class, [
			'name'  => 'テストユーザ',
			'email' => 'staff@example.com',
		]);
	}
}
