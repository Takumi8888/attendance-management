<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
		$this->call(UsersTableSeeder::class);
		$this->call(AdminsTableSeeder::class);
		$this->call(WorkTimesTableSeeder::class);
		$this->call(BreakTimesTableSeeder::class);
		$this->call(AttendancesTableSeeder::class);
		$this->call(CorrectionRequestsTableSeeder::class);
    }
}