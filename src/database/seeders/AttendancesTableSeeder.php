<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use App\Models\WorkTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		for ($i = 1; $i <= 906; $i++) {
			$work_time = WorkTime::find($i)->work_time;
			$date = WorkTime::find($i)->clock_in_time;
			$total_break_time = BreakTime::find($i)->break_time;
			$actual_working_hours = ((strtotime($work_time) - strtotime($total_break_time)) / 3600) . ':00:00';

			DB::table('attendances')->insert([
				'work_time_id'         => $i,
				'work_day'             => $date,
				'total_break_time' 	   => $total_break_time,
				'actual_working_hours' => $actual_working_hours,
				'created_at'           => Carbon::now(),
				'updated_at'           => Carbon::now(),
			]);
		}
    }
}
