<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
		$work_time_id = 1;
		for ($month = 1; $month <= 5; $month++) {
			for ($day = 1; $day <= 31; $day++) {
				for ($user = 1; $user <= 6; $user++) {
					if (($month == 1 || $month == 3 || $month == 5 && $day <= 31) || ($month == 4 && $day <= 30) || ($month == 2 && $day <= 28)) {
						$start_time  = '2025/' . $month . '/' . $day . ' 12:00:00';
						$end_time = '2025/' . $month . '/' . $day . ' 13:00:00';
						$break_time = ((strtotime($end_time) - strtotime($start_time)) / 3600) . ':00:00';

						DB::table('break_times')->insert([
							'work_time_id' => $work_time_id,
							'start_time'   => $start_time,
							'end_time' 	   => $end_time,
							'break_time'   => $break_time,
							'created_at'   => Carbon::now(),
							'updated_at'   => Carbon::now(),
						]);

						$work_time_id += 1;
					}
				}
			}
		}
    }
}
