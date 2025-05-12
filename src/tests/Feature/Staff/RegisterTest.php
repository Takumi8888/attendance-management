<?php

namespace Tests\Feature\Staff;

use App\Mail\TestMailable;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed(DatabaseSeeder::class);
	}

	// 1.「お名前を入力してください」というバリデーションメッセージが表示される
	public function test_register_1_1()
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

	// 2.「メールアドレスを入力してください」というバリデーションメッセージが表示される
	public function test_register_1_2()
	{
		$response = $this->post('/register', [
			'name'                  => 'test_user',
			'email'                 => null,
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
		$response->assertStatus(302);
	}

	// 3.「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
	public function test_register_1_3()
	{
		$response = $this->post('/register', [
			'name'                  => 'test_user',
			'email'                 => 'staff@example.com',
			'password'              => 'pass',
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
		$response->assertStatus(302);
	}

	// 4.「パスワードと一致しません」というバリデーションメッセージが表示される
	public function test_register_1_4()
	{
		$response = $this->post('/register', [
			'name'                  => 'test_user',
			'email'                 => 'staff@example.com',
			'password'              => 'password',
			'password_confirmation' => 'passwordUnmatch',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
		$response->assertStatus(302);
	}

	// 5.「パスワードを入力してください」というバリデーションメッセージが表示される
	public function test_register_1_5()
	{
		$response = $this->post('/register', [
			'name'                  => 'test_user',
			'email'                 => 'staff@example.com',
			'password'              => null,
			'password_confirmation' => 'password',
		]);

		$response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
		$response->assertStatus(302);
	}

	// 6.データベースに登録したユーザー情報が保存される
	public function test_register_1_6()
	{
		$response = $this->post('/register', [
			'name'                  => 'test_user',
			'email'                 => 'staff@example.com',
			'password'              => 'password',
			'password_confirmation' => 'password',
		]);

		$response->assertStatus(302);
		$response->assertRedirect('/email/verify');
		$this->assertDatabaseHas('users', [
			'name'  => 'test_user',
			'email' => 'staff@example.com',
		]);
	}

	// 7.登録したメールアドレス宛に認証メールが送信されている
	public function test_register_16_1()
	{
		$name	  = 'test_user';
		$email	  = 'staff@example.com';
		$password = 'password';

		$response1 = $this->post('/register', [
			'name'                  => $name,
			'email'                 => $email,
			'password'              => $password,
			'password_confirmation' => $password,
		]);
		$response1->assertStatus(302);
		$response1->assertRedirect('/email/verify');

		Mail::fake();
		$data = [
			'title'   => 'テストメール',
			'message' => 'テストメッセージ',
		];

		Mail::to($email)->send(new TestMailable($data));
		Mail::assertSent(TestMailable::class, function ($mail) {
			return $mail->hasTo('staff@example.com');
		});

		$new_user = User::find(7);
		$new_user->update([
			'name'              => $name,
			'email'             => $email,
			'email_verified_at'	=> Carbon::now()->format('Y-m-d H:i:s'),
		]);
	}
}
