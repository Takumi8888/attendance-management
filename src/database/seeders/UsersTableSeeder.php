<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		$name = [
			'西 伶奈',
			'山田 太郎',
			'増田 一世',
			'山本 敬吉',
			'秋田 朋美',
			'中西 教夫',
		];

		$email = [
			'reina.n@coachtech.com',
			'taro.y@coachtech.com',
			'issei.m@coachtech.com',
			'keikichi.y@coachtech.com',
			'tomomi.a@coachtech.com',
			'norio.n@coachtech.com',
		];

		for ($i = 0; $i < 6; $i++) {
			DB::table('users')->insert([
				'name'              => $name[$i],
				'email'             => $email[$i],
				'email_verified_at' => Carbon::now(),
				'password'          => Hash::make('password'),
				'remember_token'    => Str::random(10),
			]);
		}
    }
}
