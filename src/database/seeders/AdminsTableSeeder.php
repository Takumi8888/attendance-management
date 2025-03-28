<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		DB::table('admins')->insert([
			'name'              => 'coachtech',
			'email'             => 'admin@coachtech.com',
			'email_verified_at' => Carbon::now(),
			'password'          => Hash::make('password'),
			'remember_token'    => Str::random(10),
		]);
    }
}